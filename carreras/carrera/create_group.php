<?php
// api_guardar_grupo.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php';

// Leer JSON raw input
$data = json_decode(file_get_contents('php://input'), true);

$carrera_id   = isset($data['carrera_id']) ? (int)$data['carrera_id'] : 0;
$nombre_grupo = trim($data['nombre_grupo'] ?? '');
$id_trayecto  = isset($data['id_trayecto']) ? (int)$data['id_trayecto'] : 0;
$id_periodo   = isset($data['id_periodo']) ? (int)$data['id_periodo'] : 0;

// --- VALIDACIONES INICIALES ---
if ($carrera_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de carrera inválido.']);
    exit;
}

if ($nombre_grupo === '') {
    echo json_encode(['success' => false, 'error' => 'El nombre del grupo es obligatorio.']);
    exit;
}

// --- ASIGNACIÓN AUTOMÁTICA DE TRAYECTO/PERIODO ---
// Si trayecto o periodo no son válidos, obtener el primero disponible para la carrera
if ($id_trayecto <= 0 || $id_periodo <= 0) {
    $stmt = $conn->prepare("
        SELECT t.id_trayecto, p.id_periodo 
        FROM trayectos t
        INNER JOIN periodos p ON p.id_trayecto = t.id_trayecto
        WHERE t.id_carrera = ?
        ORDER BY t.numero_trayecto, p.numero_periodo
        LIMIT 1
    ");
    $stmt->bind_param("i", $carrera_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $fila = $res->fetch_assoc();
    $stmt->close();

    if ($fila) {
        $id_trayecto = (int)$fila['id_trayecto'];
        $id_periodo  = (int)$fila['id_periodo'];
    } else {
        echo json_encode(['success' => false, 'error' => 'No hay trayectos o periodos definidos para esta carrera.']);
        exit;
    }
}

// Validar nuevamente después de la búsqueda automática
if ($id_trayecto <= 0 || $id_periodo <= 0) {
    echo json_encode(['success' => false, 'error' => 'Debe seleccionar un trayecto y periodo válidos.']);
    exit;
}

// --- VERIFICACIÓN DE INTEGRIDAD (Relaciones) ---

// 1. Verificar que el trayecto pertenece a la carrera
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM trayectos WHERE id_trayecto = ? AND id_carrera = ?");
$stmt->bind_param("ii", $id_trayecto, $carrera_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if ($row['cnt'] == 0) {
    echo json_encode(['success' => false, 'error' => 'El trayecto no pertenece a la carrera especificada.']);
    exit;
}

// 2. Verificar que el periodo pertenece al trayecto
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM periodos WHERE id_periodo = ? AND id_trayecto = ?");
$stmt->bind_param("ii", $id_periodo, $id_trayecto);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if ($row['cnt'] == 0) {
    echo json_encode(['success' => false, 'error' => 'El periodo no pertenece al trayecto especificado.']);
    exit;
}

// --- INSERCIÓN FINAL ---
$stmt = $conn->prepare("INSERT INTO grupos (id_carrera, nombre_grupo, id_trayecto, id_periodo) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isii", $carrera_id, $nombre_grupo, $id_trayecto, $id_periodo);

if ($stmt->execute()) {
    $id_grupo = $stmt->insert_id;
    $stmt->close();

    echo json_encode([
        'success' => true, 
        'grupo'   => [
            'id_grupo'     => $id_grupo,
            'nombre_grupo' => $nombre_grupo,
            'id_trayecto'  => $id_trayecto,
            'id_periodo'   => $id_periodo
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar el grupo en la base de datos.']);
}