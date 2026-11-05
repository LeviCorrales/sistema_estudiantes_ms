<?php
header('Content-Type: application/json');
include '../config/conexion.php';

$id_estudiante = $_POST['id_estudiante'] ?? null;
$nuevo_estado = $_POST['nuevo_estado'] ?? null;

if (!$id_estudiante || !$nuevo_estado) {
    echo json_encode(['success' => false, 'msg' => 'Faltan parámetros']);
    exit;
}

// ------------------- ACTUALIZAR ESTADO -------------------
$sql = "UPDATE datos_personales SET estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nuevo_estado, $id_estudiante);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'msg' => $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// ------------------- ELIMINAR DE GRUPOS SI NO ESTÁ ACTIVO -------------------
if ($nuevo_estado === 'congelado' || $nuevo_estado === 'retirado') {
    
    // 🔹 Eliminar de grupos normales
    $sqlDeleteGrupos = "DELETE FROM grupo_estudiantes WHERE id_estudiante = ?";
    $stmtDel = $conn->prepare($sqlDeleteGrupos);
    $stmtDel->bind_param("i", $id_estudiante);
    if (!$stmtDel->execute()) {
        echo json_encode(['success' => false, 'msg' => 'Error eliminando de grupos: ' . $stmtDel->error]);
        $stmtDel->close();
        $conn->close();
        exit;
    }
    $stmtDel->close();

    // 🔹 Eliminar de trayecto inicial (sin borrar historial)
    $sqlDeleteInicial = "DELETE FROM trayecto_inicial_estudiantes WHERE id_estudiante = ?";
    $stmtDel2 = $conn->prepare($sqlDeleteInicial);
    $stmtDel2->bind_param("i", $id_estudiante);
    if (!$stmtDel2->execute()) {
        echo json_encode(['success' => false, 'msg' => 'Error eliminando de trayecto inicial: ' . $stmtDel2->error]);
        $stmtDel2->close();
        $conn->close();
        exit;
    }
    $stmtDel2->close();
}

$conn->close();
echo json_encode(['success' => true, 'msg' => 'Estado actualizado correctamente']);
?>
