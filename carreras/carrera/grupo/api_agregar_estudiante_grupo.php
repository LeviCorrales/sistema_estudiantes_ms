<?php
require_once '../../../config/conexion.php';

// Aseguramos que la respuesta sea JSON puro
header('Content-Type: application/json; charset=utf-8');

$id_grupo = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
$id_estudiante = isset($_POST['id_estudiante']) ? (int)$_POST['id_estudiante'] : 0;

if ($id_grupo <= 0 || $id_estudiante <= 0) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

// 0️⃣ Chequear si el estudiante está en algún grupo de trayecto inicial
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM trayecto_inicial_estudiantes
    WHERE id_estudiante = ?
");
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$totalTrayecto = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

if ($totalTrayecto > 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'El estudiante ya pertenece a un grupo de trayecto inicial, no se puede agregar.'
    ]);
    exit;
}

// 1️⃣ Obtener la carrera del grupo
$stmt = $conn->prepare("SELECT id_carrera FROM grupos WHERE id_grupo = ?");
$stmt->bind_param("i", $id_grupo);
$stmt->execute();
$res = $stmt->get_result();
$grupo_info = $res->fetch_assoc();
$stmt->close();

if (!$grupo_info) {
    echo json_encode(['success' => false, 'error' => 'Grupo no encontrado.']);
    exit;
}
$id_carrera = $grupo_info['id_carrera'];

// 2️⃣ Verificar si el estudiante ya está en algún grupo de la misma carrera
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM grupo_estudiantes ge
    INNER JOIN grupos g ON ge.id_grupo = g.id_grupo
    WHERE ge.id_estudiante = ? AND g.id_carrera = ?
");
$stmt->bind_param("ii", $id_estudiante, $id_carrera);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

if ($total > 0) {
    echo json_encode(['success' => false, 'error' => 'El estudiante ya está asignado a otro grupo de esta carrera.']);
    exit;
}

// 3️⃣ Insertar el estudiante en el grupo
$stmt = $conn->prepare("INSERT INTO grupo_estudiantes (id_grupo, id_estudiante) VALUES (?, ?)");
$stmt->bind_param("ii", $id_grupo, $id_estudiante);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Estudiante agregado correctamente.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar en la base de datos: ' . $conn->error]);
}