<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php';

$id_estudiante = (int)($_GET['id_estudiante'] ?? 0);
if ($id_estudiante <= 0) { echo json_encode(['trayectos'=>[]]); exit; }

$stmt = $conn->prepare("
    SELECT DISTINCT t.id_trayecto, t.numero_trayecto
    FROM historial_grupo_materia h
    INNER JOIN trayectos t ON h.id_trayecto = t.id_trayecto
    WHERE h.id_estudiante = ?
    ORDER BY t.numero_trayecto
");
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$trayectos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['trayectos'=>$trayectos]);
