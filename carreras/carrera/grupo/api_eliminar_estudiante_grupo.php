
<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../../../config/conexion.php';

$id_grupo = intval($_POST['id_grupo'] ?? 0);
$id_estudiante = intval($_POST['id_estudiante'] ?? 0);

if ($id_grupo <= 0 || $id_estudiante <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Los datos del grupo o del estudiante no son válidos.'
    ], JSON_UNESCAPED_UNICODE);
    $conn->close();
    exit;
}

try {
    /*
     * Solo elimina la relación del estudiante con ESTE grupo.
     * No elimina al estudiante ni modifica sus calificaciones
     * en historial_grupo_materia.
     */
    $sql = "
        DELETE FROM grupo_estudiantes
        WHERE id_grupo = ?
          AND id_estudiante = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('No se pudo preparar la operación.');
    }

    $stmt->bind_param('ii', $id_grupo, $id_estudiante);

    if (!$stmt->execute()) {
        throw new Exception('No se pudo eliminar al estudiante del grupo.');
    }

    $eliminados = $stmt->affected_rows;
    $stmt->close();

    if ($eliminados === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'El estudiante ya no pertenece a este grupo.'
        ], JSON_UNESCAPED_UNICODE);

        $conn->close();
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'El estudiante fue eliminado del grupo. Su historial académico se conserva.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('Error al eliminar estudiante del grupo: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => 'Ocurrió un error al eliminar al estudiante del grupo.'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
