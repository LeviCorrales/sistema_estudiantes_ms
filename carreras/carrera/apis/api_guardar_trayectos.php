<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/conexion.php';

// Leer JSON
$data = json_decode(file_get_contents('php://input'), true);

$carrera_id = isset($data['carrera_id']) ? (int)$data['carrera_id'] : 0;
$trayectos  = $data['trayectos'] ?? [];

if ($carrera_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'ID de carrera inválido.'
    ]);
    exit;
}

if (empty($trayectos) || !is_array($trayectos)) {
    echo json_encode([
        'success' => false,
        'error' => 'No se recibieron trayectos.'
    ]);
    exit;
}

/**
 * 🔒 VALIDACIÓN CLAVE
 * Si ya existen trayectos → NO se puede volver a definir
 */
$check = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM trayectos
    WHERE id_carrera = ?
");
$check->bind_param("i", $carrera_id);
$check->execute();
$result = $check->get_result()->fetch_assoc();
$check->close();

if ((int)$result['total'] > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Los trayectos y periodos ya fueron definidos.'
    ]);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    $stmtTray = $conn->prepare("
        INSERT INTO trayectos (id_carrera, numero_trayecto)
        VALUES (?, ?)
    ");

    $stmtPer = $conn->prepare("
        INSERT INTO periodos (id_trayecto, numero_periodo)
        VALUES (?, ?)
    ");

    foreach ($trayectos as $tray) {
        $numero_trayecto = (int)($tray['numero'] ?? 0);
        $num_periodos    = (int)($tray['periodos'] ?? 0);

        if ($numero_trayecto <= 0 || $num_periodos <= 0) {
            throw new Exception('Datos inválidos de trayectos.');
        }

        // Insertar trayecto
        $stmtTray->bind_param("ii", $carrera_id, $numero_trayecto);
        $stmtTray->execute();
        $id_trayecto = $stmtTray->insert_id;

        // Insertar periodos
        for ($i = 1; $i <= $num_periodos; $i++) {
            $stmtPer->bind_param("ii", $id_trayecto, $i);
            $stmtPer->execute();
        }
    }

    $stmtTray->close();
    $stmtPer->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Trayectos y periodos guardados correctamente.'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
