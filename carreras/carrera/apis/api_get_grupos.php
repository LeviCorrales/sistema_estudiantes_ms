<?php
// api_get_grupos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/conexion.php';

$carrera_id = isset($_GET['carrera_id']) ? (int)$_GET['carrera_id'] : 0;
// Por defecto cargamos 'activo' si no se envía nada
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'activo';

// Validar que el estado sea permitido para evitar inyecciones o errores
$estados_permitidos = ['activo', 'egresado', 'congelado'];
if (!in_array($estado, $estados_permitidos)) {
    $estado = 'activo'; 
}

if ($carrera_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de carrera inválido.']);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        g.id_grupo,
        g.nombre_grupo,
        g.estado,
        t.numero_trayecto,
        p.numero_periodo
    FROM grupos g
    INNER JOIN trayectos t ON g.id_trayecto = t.id_trayecto
    INNER JOIN periodos p ON g.id_periodo = p.id_periodo
    WHERE g.id_carrera = ? AND g.estado = ?
    ORDER BY t.numero_trayecto, p.numero_periodo, g.nombre_grupo
");

$stmt->bind_param("is", $carrera_id, $estado);
$stmt->execute();
$res = $stmt->get_result();

$grupos = [];
while ($row = $res->fetch_assoc()) {
    $grupos[] = [
        'id_grupo' => $row['id_grupo'],
        'nombre_grupo' => $row['nombre_grupo'],
        'estado' => $row['estado'],
        'trayecto' => (int)$row['numero_trayecto'],
        'periodo' => (int)$row['numero_periodo']
    ];
}

$stmt->close();
echo json_encode(['success' => true, 'grupos' => $grupos]);