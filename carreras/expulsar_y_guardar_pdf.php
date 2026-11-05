<?php
include '../config/conexion.php';
header('Content-Type: application/json');

$idGrupo = $_POST['id_trayecto_inicial'] ?? null;
$nombreArchivo = $_POST['nombre_archivo'] ?? null;
$pdfTmp = $_FILES['pdf']['tmp_name'] ?? null;

if (!$idGrupo || !$pdfTmp) {
    echo json_encode(['success' => false, 'msg' => 'Datos incompletos']);
    exit;
}

$rutaDestino = "../exports/" . basename($nombreArchivo);

try {
    $conn->begin_transaction();

    // 1. Guardar el archivo físico
    if (!move_uploaded_file($pdfTmp, $rutaDestino)) {
        throw new Exception("No se pudo guardar el archivo PDF en el servidor");
    }

    // 2. Desvincular notas del grupo (SET NULL) para preservar historial
    $sqlHistorial = "UPDATE historial_academico_inicial h
                     JOIN trayecto_inicial_estudiantes tie ON h.id_grupo_estudiante = tie.id
                     SET h.id_grupo_estudiante = NULL 
                     WHERE tie.id_trayecto_inicial = ?";
    $stmtH = $conn->prepare($sqlHistorial);
    $stmtH->bind_param("i", $idGrupo);
    $stmtH->execute();

    // 3. Eliminar estudiantes del grupo activo
    $stmtD = $conn->prepare("DELETE FROM trayecto_inicial_estudiantes WHERE id_trayecto_inicial = ?");
    $stmtD->bind_param("i", $idGrupo);
    $stmtD->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    // Eliminar el archivo si algo falló en la DB para no tener basura
    if(file_exists($rutaDestino)) unlink($rutaDestino);
    
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}

$conn->close();