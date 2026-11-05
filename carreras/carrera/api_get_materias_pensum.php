<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php';

// Obtenemos el ID por parámetro GET (ej: consultar.php?id_carrera=5)
$id_carrera = isset($_GET['id_carrera']) ? (int)$_GET['id_carrera'] : 0;

if ($id_carrera <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de carrera inválido.']);
  exit;
}

// Consulta estructurada con JOINs para obtener trayectos y periodos
$sql = "
  SELECT 
    m.id_materia,
    m.nombre_materia,
    m.codigo_materia,
    m.creditos,
    m.horas_semanales,
    t.numero_trayecto,
    p.numero_periodo
  FROM materias_carrera m
  INNER JOIN trayectos t ON m.id_trayecto = t.id_trayecto
  INNER JOIN periodos p ON m.id_periodo = p.id_periodo
  WHERE m.id_carrera = ? 
    AND m.estado = 'activa'
  ORDER BY t.numero_trayecto, p.numero_periodo, m.nombre_materia
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_carrera);
$stmt->execute();
$result = $stmt->get_result();

$materias = [];
while ($row = $result->fetch_assoc()) {
  $materias[] = $row;
}

// Retornamos el array de objetos listo para ser procesado por JavaScript
echo json_encode([
  'success'  => true, 
  'materias' => $materias
]);

$stmt->close();
$conn->close();