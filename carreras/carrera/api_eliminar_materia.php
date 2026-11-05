<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php';

// Obtención de datos mediante el cuerpo de la petición (JSON)
$data = json_decode(file_get_contents('php://input'), true);
$id_materia = isset($data['id_materia']) ? (int)$data['id_materia'] : 0;

if ($id_materia <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de materia inválido.']);
  exit;
}

try {
  // BAJA LÓGICA: No usamos DELETE, usamos UPDATE para preservar historial
  $stmt = $conn->prepare("UPDATE materias_carrera SET estado = 'eliminada' WHERE id_materia = ?");
  
  if (!$stmt) {
    throw new Exception("Error en la preparación de la consulta: " . $conn->error);
  }

  $stmt->bind_param("i", $id_materia);
  
  if ($stmt->execute()) {
    echo json_encode([
      'success' => true, 
      'message' => 'Materia ocultada del pensum con éxito.'
    ]);
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