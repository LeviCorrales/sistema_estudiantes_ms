<?php
// IMPORTANTE: Debes incluir la conexión, de lo contrario $conn no existe
require_once '../../../config/conexion.php'; 

header('Content-Type: application/json');

// Desactivar visualización de errores de PHP para que no rompan el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['id_grupo'])) {
        throw new Exception('Falta el ID del grupo');
    }

    $id_grupo = (int)$data['id_grupo'];

    $conn->begin_transaction();

    // 1. Actualizar estudiantes
    $stmt_estudiantes = $conn->prepare("
        UPDATE datos_personales dp
        INNER JOIN grupo_estudiantes ge ON dp.id = ge.id_estudiante
        SET dp.estado = 'egresado'
        WHERE ge.id_grupo = ?
    ");
    $stmt_estudiantes->bind_param("i", $id_grupo);
    $stmt_estudiantes->execute();
    $stmt_estudiantes->close();

    // 2. Actualizar el grupo
    $stmt_grupo = $conn->prepare("UPDATE grupos SET estado = 'egresado' WHERE id_grupo = ?");
    $stmt_grupo->bind_param("i", $id_grupo);
    $stmt_grupo->execute();
    $stmt_grupo->close();

    $conn->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>