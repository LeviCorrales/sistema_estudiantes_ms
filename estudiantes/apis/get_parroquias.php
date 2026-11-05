<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../config/conexion.php';

if (!$conn) {
    echo json_encode(["error" => "Error en la conexión a la base de datos"]);
    exit;
}

if (isset($_GET['id_municipio'])) {
    $id_municipio = intval($_GET['id_municipio']);

    $stmt = $conn->prepare("SELECT id, nombre FROM parroquias WHERE id_municipio = ? ORDER BY nombre ASC");
    if (!$stmt) {
        echo json_encode(["error" => "Error en la preparación de la consulta"]);
        exit;
    }

    $stmt->bind_param("i", $id_municipio);
    $stmt->execute();

    $result = $stmt->get_result();

    $parroquias = [];
    while ($row = $result->fetch_assoc()) {
        $parroquias[] = $row;
    }

    echo json_encode($parroquias);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([]);
    exit;
}
