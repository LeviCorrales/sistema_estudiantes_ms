<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
include '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'errors' => ['Método no permitido']]);
    exit;
}

// Datos personales
$cedula = trim($_POST['cedula'] ?? '');
$primer_nombre = trim($_POST['primer_nombre'] ?? '');
$segundo_nombre = trim($_POST['segundo_nombre'] ?? '');
$primer_apellido = trim($_POST['primer_apellido'] ?? '');
$segundo_apellido = trim($_POST['segundo_apellido'] ?? '');
$sexo = trim($_POST['sexo'] ?? '');
$estado_civil = trim($_POST['estado_civil'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$telefono_celular = trim($_POST['telefono_celular'] ?? '');
$correo_electronico = trim($_POST['correo_electronico'] ?? ''); // 👈 nuevo campo
$etnia_aborigen = trim($_POST['etnia_aborigen'] ?? 'No');

// Dirección
$id_estado = isset($_POST['id_estado']) && is_numeric($_POST['id_estado']) ? intval($_POST['id_estado']) : 0;
$id_municipio = isset($_POST['id_municipio']) && is_numeric($_POST['id_municipio']) ? intval($_POST['id_municipio']) : 0;
$id_parroquia = isset($_POST['id_parroquia']) && is_numeric($_POST['id_parroquia']) ? intval($_POST['id_parroquia']) : 0;
$avenida = trim($_POST['avenida'] ?? '');
$calle_sector = trim($_POST['calle_sector'] ?? '');
$casa_apartamento = trim($_POST['casa_apartamento'] ?? '');
$punto_referencia = trim($_POST['punto_referencia'] ?? '');

// Socioeconómicos
$grupo_familiar = $_POST['grupo_familiar'] ?? null;
$personas_a_cargo = $_POST['personas_a_cargo'] ?? null;
$fuente_ingresos = trim($_POST['fuente_ingresos'] ?? '');
$ingreso_mensual = $_POST['ingreso_mensual'] ?? null;
$tipo_vivienda = trim($_POST['tipo_vivienda'] ?? '');
$tenencia_vivienda = trim($_POST['tenencia_vivienda'] ?? '');

// Validaciones
$errores = [];

if (!$cedula || !preg_match('/^\d+$/', $cedula)) $errores[] = "Cédula inválida o vacía.";
if (!$primer_nombre) $errores[] = "El primer nombre es obligatorio.";
if (!$primer_apellido) $errores[] = "El primer apellido es obligatorio.";
if (!$sexo || !in_array($sexo, ['M', 'F'])) $errores[] = "Seleccione un sexo válido.";
if (!$estado_civil || !in_array($estado_civil, ['Soltero', 'Casado', 'Divorciado', 'Viudo'])) $errores[] = "Seleccione un estado civil válido.";
if (!$fecha_nacimiento || !DateTime::createFromFormat('Y-m-d', $fecha_nacimiento)) $errores[] = "Fecha de nacimiento inválida o vacía.";
if (!$telefono_celular || !preg_match('/^\d{11}$/', $telefono_celular)) $errores[] = "Teléfono celular inválido, debe tener 11 dígitos.";
if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo electrónico inválido."; // 👈 validación nueva

if ($id_estado <= 0) $errores[] = "Debe seleccionar un estado válido.";
if ($id_municipio <= 0) $errores[] = "Debe seleccionar un municipio válido.";
if ($id_parroquia <= 0) $errores[] = "Debe seleccionar una parroquia válida.";

if (count($errores) > 0) {
    echo json_encode(['success' => false, 'errors' => $errores]);
    exit;
}

// Validar cédula duplicada
$checkStmt = $conn->prepare("SELECT id FROM datos_personales WHERE cedula = ?");
$checkStmt->bind_param("s", $cedula);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'errors' => ["La cédula ya está registrada."]]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insert datos personales
$stmt = $conn->prepare("INSERT INTO datos_personales (
    cedula, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
    sexo, estado_civil, fecha_nacimiento, telefono_celular, etnia_aborigen, correo_electronico
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    throw new Exception("Error al preparar insert de datos personales."); 
}

$stmt->bind_param(
    "sssssssssss",
    $cedula,
    $primer_nombre,
    $segundo_nombre,
    $primer_apellido,
    $segundo_apellido,
    $sexo,
    $estado_civil,
    $fecha_nacimiento,
    $telefono_celular,
    $etnia_aborigen,
    $correo_electronico
);

if (!$stmt->execute()) {
    throw new Exception("Error al guardar datos personales.");
}

$id_persona = $stmt->insert_id;
$stmt->close();


    // Insert residencia
    $stmt2 = $conn->prepare("INSERT INTO direccion_residencia (
        id_persona, id_estado, id_municipio, id_parroquia,
        avenida, calle_sector, casa_apartamento, punto_referencia
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("iiisssss", $id_persona, $id_estado, $id_municipio, $id_parroquia, $avenida, $calle_sector, $casa_apartamento, $punto_referencia);
    $stmt2->execute();
    $stmt2->close();

    // Insert socioeconómicos
    $stmt3 = $conn->prepare("INSERT INTO datos_socioeconomicos (
        id_persona, grupo_familiar, personas_a_cargo, fuente_ingresos, ingreso_mensual, tipo_vivienda, tenencia_vivienda
    ) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt3->bind_param("iiisdss", $id_persona, $grupo_familiar, $personas_a_cargo, $fuente_ingresos, $ingreso_mensual, $tipo_vivienda, $tenencia_vivienda);
    $stmt3->execute();
    $stmt3->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Datos guardados correctamente.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'errors' => ['Error al guardar datos: ' . $e->getMessage()]]);
}

$conn->close();
?>
