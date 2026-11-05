
<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    echo json_encode([
        'success' => false,
        'error' => 'Los datos enviados no son válidos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener y validar datos
$id_carrera      = (int)($data['id_carrera'] ?? 0);
$id_trayecto     = (int)($data['id_trayecto'] ?? 0);
$id_periodo      = (int)($data['id_periodo'] ?? 0);
$nombre_materia  = trim($data['nombre_materia'] ?? '');
$codigo_materia  = trim($data['codigo_materia'] ?? '');
$creditos        = (int)($data['creditos'] ?? 0);
$horas_semanales = (int)($data['horas_semanales'] ?? 0);

// Validaciones básicas
if (
    $id_carrera <= 0 ||
    $id_trayecto <= 0 ||
    $id_periodo <= 0 ||
    $nombre_materia === ''
) {
    echo json_encode([
        'success' => false,
        'error' => 'Datos inválidos o incompletos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Evitar códigos duplicados entre materias activas
    // La comprobación se realiza globalmente, en todas las carreras.
    if ($codigo_materia !== '') {
        $stmt = $conn->prepare("
            SELECT id_materia
            FROM materias_carrera
            WHERE codigo_materia = ?
              AND estado = 'activa'
            LIMIT 1
        ");

        if (!$stmt) {
            throw new Exception('No se pudo validar el código de la materia.');
        }

        $stmt->bind_param("s", $codigo_materia);
        $stmt->execute();
        $stmt->store_result();

        $codigoExiste = $stmt->num_rows > 0;
        $stmt->close();

        if ($codigoExiste) {
            echo json_encode([
                'success' => false,
                'error' => 'Este código ya está registrado en otra materia activa. Introduce un código diferente.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Insertar materia si pasa las validaciones
    $stmt = $conn->prepare("
        INSERT INTO materias_carrera (
            id_carrera,
            id_trayecto,
            id_periodo,
            nombre_materia,
            codigo_materia,
            creditos,
            horas_semanales
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception('No se pudo preparar el registro de la materia.');
    }

    $stmt->bind_param(
        "iiissii",
        $id_carrera,
        $id_trayecto,
        $id_periodo,
        $nombre_materia,
        $codigo_materia,
        $creditos,
        $horas_semanales
    );

    if (!$stmt->execute()) {
        throw new Exception('No se pudo guardar la materia.');
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Materia guardada correctamente.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('Error al guardar materia: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} finally {
    $conn->close();
}
?>
