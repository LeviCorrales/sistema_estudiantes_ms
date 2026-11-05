<?php
if (ob_get_length()) ob_clean(); 

header('Content-Type: application/json; charset=utf-8');
include '../config/conexion.php';

$idMateria = isset($_POST['id_materia']) ? intval($_POST['id_materia']) : 0;

if ($idMateria <= 0) {
    echo json_encode(['success' => false, 'msg' => 'ID de materia inválido para la operación.']);
    exit;
}

try {
    /**
     * IMPORTANTE: No usamos DELETE. 
     * Hacemos un borrado lógico (Soft Delete) cambiando activo a 0.
     */
    $sql = "UPDATE materias_pensum_inicial SET activo = 0 WHERE id_materia_inicial = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $idMateria);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'msg' => 'La materia se ha inactivado del pensum correctamente.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception("No se pudo actualizar el registro: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'msg' => 'Error crítico del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>