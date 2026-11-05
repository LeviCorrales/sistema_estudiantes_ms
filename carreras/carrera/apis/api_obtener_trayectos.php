<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/conexion.php';

$carrera_id = isset($_GET['carrera_id']) ? (int)$_GET['carrera_id'] : 0;

if ($carrera_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'ID de carrera inválido.'
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT t.id_trayecto, t.numero_trayecto, COUNT(p.id_periodo) AS total_periodos
    FROM trayectos t
    LEFT JOIN periodos p ON p.id_trayecto = t.id_trayecto
    WHERE t.id_carrera = ?
    GROUP BY t.id_trayecto, t.numero_trayecto
    ORDER BY t.numero_trayecto ASC
");

$stmt->bind_param("i", $carrera_id);
$stmt->execute();
$result = $stmt->get_result();

$trayectos = [];
while ($row = $result->fetch_assoc()) {
    $trayectos[] = [
        'id_trayecto' => (int)$row['id_trayecto'],
        'numero'      => (int)$row['numero_trayecto'],
        'periodos'    => (int)$row['total_periodos']
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'definidos' => count($trayectos) > 0, // 🔥 CLAVE
    'trayectos' => $trayectos
]);
