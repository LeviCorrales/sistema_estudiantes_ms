<?php
include '../config/conexion.php';

$idGrupo      = $_POST['id_trayecto_inicial'] ?? 0;
$idEstudiante = $_POST['id_estudiante'] ?? 0;

if (!$idGrupo || !$idEstudiante) {
    echo json_encode(['success' => false, 'msg' => 'Faltan datos.']);
    exit;
}

// 🔹 Verificar si el estudiante ya está en cualquier grupo inicial
$sqlCheck  = "SELECT COUNT(*) AS total FROM trayecto_inicial_estudiantes WHERE id_estudiante = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $idEstudiante);
$stmtCheck->execute();

$resCheck = $stmtCheck->get_result();
$already  = $resCheck->fetch_assoc()['total'] ?? 0;
$stmtCheck->close();

if ($already > 0) {
    echo json_encode(['success' => false, 'msg' => 'El estudiante ya pertenece a otro grupo inicial.']);
    exit;
}

// 🔹 Insertar estudiante en el grupo
$sqlInsert  = "INSERT INTO trayecto_inicial_estudiantes (id_trayecto_inicial, id_estudiante) VALUES (?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("ii", $idGrupo, $idEstudiante);

if ($stmtInsert->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Error al agregar el estudiante.']);
}

$stmtInsert->close();
$conn->close();
?>