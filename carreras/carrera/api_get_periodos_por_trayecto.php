<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php';

$id_trayecto = isset($_GET['id_trayecto']) ? (int)$_GET['id_trayecto'] : 0;

if ($id_trayecto <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de trayecto inválido.']);
  exit;
}

// Consulta para obtener periodos asociados a un trayecto específico
$stmt = $conn->prepare("
  SELECT id_periodo, numero_periodo
  FROM periodos
  WHERE id_trayecto = ? AND numero_periodo > 0
  ORDER BY numero_periodo ASC
");

$stmt->bind_param("i", $id_trayecto);
$stmt->execute();
$result = $stmt->get_result();

$periodos = [];
while ($row = $result->fetch_assoc()) {
  $periodos[] = [
    'id_periodo'     => (int)$row['id_periodo'],
    'numero_periodo' => (int)$row['numero_periodo']
  ];
}

$stmt->close();
$conn->close();

echo json_encode([
  'success'  => true, 
  'periodos' => $periodos
]);