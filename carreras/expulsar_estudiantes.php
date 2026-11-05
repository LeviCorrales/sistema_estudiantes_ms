<?php
include '../config/conexion.php';

$idGrupo = $_POST['id_trayecto_inicial'] ?? null;

if (!$idGrupo) {
    echo json_encode(['success' => false, 'msg' => 'Grupo no especificado']);
    exit;
}

// 🔹 Traer todos los estudiantes del grupo
$stmtEst = $conn->prepare("SELECT id FROM trayecto_inicial_estudiantes WHERE id_trayecto_inicial = ?");
$stmtEst->bind_param("i", $idGrupo);
$stmtEst->execute();

$resCheck    = $stmtEst->get_result();
$estudiantes = [];

while ($row = $resCheck->fetch_assoc()) {
    $estudiantes[] = $row['id'];
}
$stmtEst->close();

if (!empty($estudiantes)) {
    // 🔹 Actualizar historial académico para mantener notas, solo desconectar del grupo activo
    $ids          = implode(',', $estudiantes);
    $sqlHistorial = "UPDATE historial_academico_inicial 
                     SET id_grupo_estudiante = NULL 
                     WHERE id_grupo_estudiante IN ($ids)";
    
    $conn->query($sqlHistorial);

    // 🔹 Sacar a los estudiantes del grupo
    $stmtDel = $conn->prepare("DELETE FROM trayecto_inicial_estudiantes WHERE id_trayecto_inicial = ?");
    $stmtDel->bind_param("i", $idGrupo);
    $stmtDel->execute();
    $stmtDel->close();
}

$conn->close();

echo json_encode(['success' => true]);
?>