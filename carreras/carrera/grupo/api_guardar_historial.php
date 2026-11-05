<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

$id_grupo    = $data['id_grupo']    ?? null;
$id_trayecto = $data['id_trayecto'] ?? null;
$id_periodo  = $data['id_periodo']  ?? null;
$registros   = $data['registros']   ?? [];

if (!$id_grupo || !$id_trayecto || !$id_periodo || empty($registros)) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
    exit;
}

// 1. Verificar si el periodo ya está bloqueado (consolidado)
$stmtCheck = $conn->prepare("SELECT COUNT(*) as total FROM historial_grupo_materia 
                             WHERE id_grupo = ? AND id_trayecto = ? AND id_periodo = ? AND consolidado = 1");
$stmtCheck->bind_param("iii", $id_grupo, $id_trayecto, $id_periodo);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result()->fetch_assoc();

if ($resCheck['total'] > 0) {
    echo json_encode(['success' => false, 'error' => 'Este periodo ya está consolidado. No se permiten cambios.']);
    exit;
}

try {
    // 2. Obtener carrera del grupo
    $stmt = $conn->prepare("SELECT id_carrera FROM grupos WHERE id_grupo = ?");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $id_carrera = $result['id_carrera'] ?? null;
    if (!$id_carrera) throw new Exception("No se encontró la carrera del grupo.");

    $conn->begin_transaction(); // Seguridad de datos

    // 3. Preparar el statement (9 columnas en total)
    // 6 enteros (i), 1 string (s), 1 double (d), 1 string (s) = iiiiiisds
    $stmt = $conn->prepare("
        INSERT INTO historial_grupo_materia 
        (id_grupo, id_estudiante, id_materia, id_carrera, id_trayecto, id_periodo, profesor, nota, estado, fecha_actualizacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            profesor = VALUES(profesor),
            nota = VALUES(nota),
            estado = VALUES(estado),
            fecha_actualizacion = NOW()
    ");

    foreach ($registros as $r) {
        if (!isset($r['id_estudiante'], $r['id_materia'])) continue;

        $id_estudiante = intval($r['id_estudiante']);
        $id_materia    = intval($r['id_materia']);
        $profesor      = trim($r['profesor'] ?? '');
        $nota          = is_numeric($r['nota']) ? round(floatval($r['nota'])) : null;
        
        // Estado automático basado en nota o manual
        $estado = ($nota !== null && $nota >= 10) ? 'aprobada' : ($r['estado'] ?? 'pendiente');

        $stmt->bind_param("iiiiiisds", 
            $id_grupo, 
            $id_estudiante, 
            $id_materia, 
            $id_carrera, 
            $id_trayecto, 
            $id_periodo, 
            $profesor, 
            $nota, 
            $estado
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al guardar registro: " . $stmt->error);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Historial guardado correctamente.']);

} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}