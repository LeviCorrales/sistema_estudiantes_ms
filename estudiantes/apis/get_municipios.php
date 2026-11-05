<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../config/conexion.php';

// Verificar que la conexión exista
if (!$conn) {
    echo json_encode(["error" => "Error en la conexión a la base de datos"]);
    exit;
}

// Verificar si se recibió id_estado
if (isset($_GET['id_estado'])) {
    $id_estado = intval($_GET['id_estado']);

    $stmt = $conn->prepare("SELECT id, nombre FROM municipios WHERE id_estado = ? ORDER BY nombre ASC");
    if (!$stmt) {
        echo json_encode(["error" => "Error en la preparación de la consulta"]);
        exit;
    }

    $stmt->bind_param("i", $id_estado);
    $stmt->execute();

    $result = $stmt->get_result();

    $municipios = [];
    while ($row = $result->fetch_assoc()) {
        $municipios[] = $row;
    }

    echo json_encode($municipios);

    $stmt->close();
    $conn->close();
} else {
    // Enviar array vacío si no llega id_estado
    echo json_encode([]);
    exit;
}
