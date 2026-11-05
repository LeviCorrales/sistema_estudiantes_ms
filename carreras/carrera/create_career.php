<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php';

// --- CAPTURA DE DATOS ---
$nombre = trim($_POST['nombre_carrera'] ?? '');

// --- VALIDACIONES ---
if ($nombre === '') {
    echo json_encode([
        "success" => false, 
        "error" => "El nombre es obligatorio."
    ]);
    exit;
}

if (mb_strlen($nombre) > 200) {
    echo json_encode([
        'success' => false, 
        'error' => 'El nombre es muy largo.'
    ]);
    exit;
}

// --- PREPARACIÓN DE VALORES ---
$estado = 'activa';
$fecha  = date('Y-m-d H:i:s');

// --- EJECUCIÓN EN BASE DE DATOS ---
$stmt = $conn->prepare("INSERT INTO carreras (nombre_carrera, estado, fecha_creacion) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $estado, $fecha);

if ($stmt->execute()) {
    $id = $stmt->insert_id;
    $stmt->close();

    echo json_encode([
        'success' => true,
        'carrera' => [
            'id_carrera'     => $id,
            'nombre_carrera' => $nombre,
            'estado'         => $estado,
            'fecha_creacion' => $fecha
        ]
    ]);
    exit;
} else {
    echo json_encode([
        'success' => false, 
        'error'   => 'Error al guardar en la base de datos.'
    ]);
    exit;
}