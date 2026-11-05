<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
$id_grupo    = intval($data['id_grupo']    ?? 0);
$id_trayecto = intval($data['id_trayecto'] ?? 0);
$id_periodo  = intval($data['id_periodo']  ?? 0);

if (!$id_grupo || !$id_trayecto || !$id_periodo) {
    echo json_encode(['success' => false, 'error' => 'Datos insuficientes.']);
    exit;
}

try {
    $conn->begin_transaction();

    // PASO 1: Eliminar registros de notas de materias que el usuario "borró" de la interfaz
    // Esto limpia los "fantasmas" que quedan en el historial_grupo_materia
    $sqlLimpieza = "DELETE h FROM historial_grupo_materia h
                    INNER JOIN materias_carrera m ON h.id_materia = m.id_materia
                    WHERE h.id_grupo = ? AND h.id_trayecto = ? AND h.id_periodo = ?
                    AND m.estado = 'eliminada' AND h.consolidado = 0";
    
    $stmtDel = $conn->prepare($sqlLimpieza);
    $stmtDel->bind_param("iii", $id_grupo, $id_trayecto, $id_periodo);
    $stmtDel->execute();

    // PASO 2: Consolidar solo las que quedaron (las activas)
    $sqlConsolidar = "UPDATE historial_grupo_materia h
                      INNER JOIN materias_carrera m ON h.id_materia = m.id_materia
                      SET h.nombre_materia_snapshot = m.nombre_materia,
                          h.consolidado = 1
                      WHERE h.id_grupo = ? 
                        AND h.id_trayecto = ? 
                        AND h.id_periodo = ?
                        AND m.estado = 'activa'";

    $stmtUpd = $conn->prepare($sqlConsolidar);
    $stmtUpd->bind_param("iii", $id_grupo, $id_trayecto, $id_periodo);
    $stmtUpd->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Consolidación exitosa.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}