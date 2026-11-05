<?php
// Evitar que cualquier advertencia (Warning/Notice) de PHP ensucie la salida JSON
if (ob_get_length()) ob_clean(); 

header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'msg' => 'ID de estudiante no válido']);
    exit;
}

try {
    /**
     * Consulta optimizada: devolvemos tanto 'estado' como 'estatus_calificacion' 
     * para asegurar compatibilidad absoluta con tu archivo JavaScript.
     */
    $sql = "
        SELECT 
            h.id_historial,
            h.nota,
            h.estado AS estado_original,
            m.nombre_materia,
            t.nombre_grupo_inicial,
            -- Regla de negocio automatizada para el Frontend
            CASE 
                WHEN h.nota IS NULL THEN 'Pendiente'
                WHEN h.nota >= 12 THEN 'Aprobada'
                ELSE 'Reprobada'
            END AS estado,
            CASE 
                WHEN h.nota IS NULL THEN 'Pendiente'
                WHEN h.nota >= 10 THEN 'Aprobada'
                ELSE 'Reprobada'
            END AS estatus_calificacion
        FROM historial_academico_inicial h
        INNER JOIN materias_pensum_inicial m ON h.id_materia_inicial = m.id_materia_inicial
        INNER JOIN trayecto_inicial t ON h.id_trayecto_inicial = t.id_trayecto_inicial
        WHERE h.id_estudiante = ?
        ORDER BY t.id_trayecto_inicial ASC, m.nombre_materia ASC
    ";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la estructura de la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $historial = [];
    while ($row = $result->fetch_assoc()) {
        $historial[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'success' => true, 
        'total_materias' => count($historial),
        'historial' => $historial
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'msg' => 'Error de servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>