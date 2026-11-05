<?php
header('Content-Type: application/json');
include '../config/conexion.php';

$response = ['success' => false, 'msg' => ''];

if (!isset($_FILES['imagen']) || !isset($_POST['id'])) {
    $response['msg'] = 'Datos incompletos';
    echo json_encode($response);
    exit;
}

$id = intval($_POST['id']);

// 📌 Ruta a la carpeta uploads dentro de /estudiantes
$carpeta = __DIR__ . '/uploads/';  

// Crear la carpeta si no existe
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// --- Buscar foto anterior ---
$stmtOld = $conn->prepare("SELECT foto FROM datos_personales WHERE id=?");
$stmtOld->bind_param("i", $id);
$stmtOld->execute();
$resOld = $stmtOld->get_result();
$oldFoto = $resOld->fetch_assoc()['foto'] ?? null;
$stmtOld->close();

$archivo = $_FILES['imagen'];
$ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
$nombreNuevo = 'estudiantes_'.$id.'_'.time().'.'.$ext;
$rutaFinal = $carpeta . $nombreNuevo;

// Mover archivo físico
if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
    // Ruta que se guarda en la DB (relativa para usar en <img>)
    $rutaDB = 'uploads/' . $nombreNuevo;  

    $stmt = $conn->prepare("UPDATE datos_personales SET foto=? WHERE id=?");
    $stmt->bind_param('si', $rutaDB, $id);

    if ($stmt->execute()) {
        // ✅ Eliminar la foto anterior si existía y no es nula
        if ($oldFoto && file_exists(__DIR__ . '/' . $oldFoto)) {
            unlink(__DIR__ . '/' . $oldFoto);
        }

        $response['success'] = true;
        $response['ruta'] = $rutaDB;
    } else {
        $response['msg'] = 'Error guardando en la DB';
    }
} else {
    $response['msg'] = 'Error moviendo archivo. Carpeta: ' . $carpeta . ' Ruta final: ' . $rutaFinal;
}

echo json_encode($response);
exit;
