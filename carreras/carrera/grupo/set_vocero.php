<?php
require_once '../../../config/conexion.php';

$id_grupo = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
$id_vocero = isset($_POST['id_vocero']) ? (int)$_POST['id_vocero'] : 0;

if($id_grupo <= 0 || $id_vocero <= 0){
    echo json_encode(['success'=>false,'error'=>'Datos inválidos']);
    exit;
}

// Verificar que el estudiante pertenezca al grupo
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM grupo_estudiantes WHERE id_grupo = ? AND id_estudiante = ?");
$stmt->bind_param("ii",$id_grupo,$id_vocero);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

if($total == 0){
    echo json_encode(['success'=>false,'error'=>'El estudiante no pertenece a este grupo']);
    exit;
}

// Actualizar vocero en la tabla grupos
$stmt = $conn->prepare("UPDATE grupos SET id_vocero = ? WHERE id_grupo = ?");
$stmt->bind_param("ii",$id_vocero,$id_grupo);
if($stmt->execute()){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'error'=>$conn->error]);
}
$stmt->close();
