<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php';

// Decodificamos el JSON recibido desde el cuerpo de la petición
$data = json_decode(file_get_contents('php://input'), true);

$id_materia   = isset($data['id_materia']) ? (int)$data['id_materia'] : 0;
$nuevo_nombre = trim($data['nuevo_nombre'] ?? '');

// Validación de integridad de datos
if ($id_materia <= 0 || empty($nuevo_nombre)) {
  echo json_encode(['success' => false, 'error' => 'Datos insuficientes.']);
  exit;
}

try {
  // Preparamos la actualización del nombre
  $stmt = $conn->prepare("UPDATE materias_carrera SET nombre_materia = ? WHERE id_materia = ?");
  
  if (!$stmt) {
    throw new Exception("Error al preparar la consulta: " . $conn->error);
  }

  $stmt->bind_param("si", $nuevo_nombre, $id_materia);
  
  if ($stmt->execute()) {
    echo json_encode(['success' => true]);
  } else {
    throw new Exception("Error al ejecutar la actualización: " . $stmt->error);
  }

  $stmt->close();

} catch (Exception $e) {
  echo json_encode([
    'success' => false, 
    'error'   => $e->getMessage()
  ]);
}

$conn->close();