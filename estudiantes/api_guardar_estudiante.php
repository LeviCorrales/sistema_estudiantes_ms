<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/conexion.php';

mysqli_report(MYSQLI_REPORT_OFF);

/**
 * Responder siempre con JSON y finalizar.
 */
function responder($success, $message, $status = 200) {
    http_response_code($status);

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'msg' => $message,
        'errors' => $success ? [] : [$message]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método de solicitud no permitido.', 405);
}

// Validar ID
$id = intval($_POST['id'] ?? $_POST['id_estudiante'] ?? 0);

if ($id <= 0) {
    responder(false, 'ID de estudiante inválido.', 400);
}

// Obtener datos personales
$cedula = trim($_POST['cedula'] ?? '');
$primer_nombre = trim($_POST['primer_nombre'] ?? '');
$segundo_nombre = trim($_POST['segundo_nombre'] ?? '');
$primer_apellido = trim($_POST['primer_apellido'] ?? '');
$segundo_apellido = trim($_POST['segundo_apellido'] ?? '');
$sexo = trim($_POST['sexo'] ?? '');
$estado_civil = trim($_POST['estado_civil'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$telefono_celular = trim($_POST['telefono_celular'] ?? '');
$correo_electronico = trim($_POST['correo_electronico'] ?? '');
$etnia_aborigen = trim($_POST['etnia_aborigen'] ?? '');

// Dirección
$id_estado = intval($_POST['id_estado'] ?? 0);
$id_municipio = intval($_POST['id_municipio'] ?? 0);
$id_parroquia = intval($_POST['id_parroquia'] ?? 0);
$avenida = trim($_POST['avenida'] ?? '');
$calle_sector = trim($_POST['calle_sector'] ?? '');
$casa_apartamento = trim($_POST['casa_apartamento'] ?? '');
$punto_referencia = trim($_POST['punto_referencia'] ?? '');

// Datos socioeconómicos
$grupo_familiar = intval($_POST['grupo_familiar'] ?? 0);
$personas_a_cargo = intval($_POST['personas_a_cargo'] ?? 0);
$fuente_ingresos = trim($_POST['fuente_ingresos'] ?? '');
$ingreso_mensual = floatval($_POST['ingreso_mensual'] ?? 0);
$tipo_vivienda = trim($_POST['tipo_vivienda'] ?? '');
$tenencia_vivienda = trim($_POST['tenencia_vivienda'] ?? '');

// Validaciones básicas
if (!preg_match('/^\d{7,8}$/', $cedula)) {
    responder(false, 'La cédula debe tener 7 u 8 dígitos.', 422);
}

if ($primer_nombre === '' || $primer_apellido === '') {
    responder(false, 'El primer nombre y el primer apellido son obligatorios.', 422);
}

if (!in_array($sexo, ['M', 'F'], true)) {
    responder(false, 'Seleccione un sexo válido.', 422);
}

if (!in_array($estado_civil, ['Soltero', 'Casado', 'Divorciado', 'Viudo'], true)) {
    responder(false, 'Seleccione un estado civil válido.', 422);
}

if ($fecha_nacimiento === '') {
    responder(false, 'La fecha de nacimiento es obligatoria.', 422);
}

if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
    responder(false, 'El correo electrónico no es válido.', 422);
}

if (!preg_match('/^\d{11}$/', $telefono_celular)) {
    responder(false, 'El teléfono celular debe tener 11 dígitos.', 422);
}

// Confirmar que el estudiante exista
$stmt = $conn->prepare(
    'SELECT id FROM datos_personales WHERE id = ? LIMIT 1'
);

if (!$stmt) {
    responder(false, 'No se pudo verificar el estudiante.', 500);
}

$stmt->bind_param('i', $id);
$stmt->execute();
$resultado = $stmt->get_result();
$existeEstudiante = $resultado->num_rows > 0;
$stmt->close();

if (!$existeEstudiante) {
    responder(false, 'El estudiante no existe.', 404);
}

// Verificar cédula duplicada, excluyendo al estudiante actual
$stmt = $conn->prepare(
    'SELECT id
     FROM datos_personales
     WHERE cedula = ? AND id <> ?
     LIMIT 1'
);

if (!$stmt) {
    responder(false, 'No se pudo verificar la cédula.', 500);
}

$stmt->bind_param('si', $cedula, $id);
$stmt->execute();
$resultado = $stmt->get_result();
$cedulaDuplicada = $resultado->num_rows > 0;
$stmt->close();

if ($cedulaDuplicada) {
    responder(
        false,
        'La cédula ingresada ya pertenece a otro estudiante.',
        422
    );
}

// Iniciar transacción para guardar todo o no guardar nada
if (!$conn->begin_transaction()) {
    responder(false, 'No se pudo iniciar el guardado de los datos.', 500);
}

try {
    /*
     * 1. Actualizar datos personales.
     * La foto no se modifica.
     */
    $sql = '
        UPDATE datos_personales SET
            cedula = ?,
            primer_nombre = ?,
            segundo_nombre = ?,
            primer_apellido = ?,
            segundo_apellido = ?,
            sexo = ?,
            estado_civil = ?,
            fecha_nacimiento = ?,
            telefono_celular = ?,
            correo_electronico = ?,
            etnia_aborigen = ?
        WHERE id = ?
    ';

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('No se pudo preparar la actualización personal.');
    }

    $stmt->bind_param(
        'sssssssssssi',
        $cedula,
        $primer_nombre,
        $segundo_nombre,
        $primer_apellido,
        $segundo_apellido,
        $sexo,
        $estado_civil,
        $fecha_nacimiento,
        $telefono_celular,
        $correo_electronico,
        $etnia_aborigen,
        $id
    );

    if (!$stmt->execute()) {
        $codigoError = $stmt->errno;
        $stmt->close();

        if ($codigoError === 1062) {
            throw new Exception(
                'La cédula ingresada ya pertenece a otro estudiante.',
                1062
            );
        }

        throw new Exception('Error al actualizar los datos personales.');
    }

    $stmt->close();

    /*
     * 2. Actualizar o insertar dirección.
     */
    $stmt = $conn->prepare(
        'SELECT id FROM direccion_residencia
         WHERE id_persona = ? LIMIT 1'
    );

    if (!$stmt) {
        throw new Exception('No se pudo verificar la dirección.');
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $existeDireccion = $resultado->num_rows > 0;
    $stmt->close();

    if ($existeDireccion) {
        $sqlDir = '
            UPDATE direccion_residencia
            SET id_estado = ?, id_municipio = ?, id_parroquia = ?,
                avenida = ?, calle_sector = ?, casa_apartamento = ?,
                punto_referencia = ?
            WHERE id_persona = ?
        ';
    } else {
        $sqlDir = '
            INSERT INTO direccion_residencia
                (id_estado, id_municipio, id_parroquia, avenida,
                 calle_sector, casa_apartamento, punto_referencia, id_persona)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ';
    }

    $stmt = $conn->prepare($sqlDir);

    if (!$stmt) {
        throw new Exception('No se pudo preparar el guardado de la dirección.');
    }

    $stmt->bind_param(
        'iiissssi',
        $id_estado,
        $id_municipio,
        $id_parroquia,
        $avenida,
        $calle_sector,
        $casa_apartamento,
        $punto_referencia,
        $id
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Error al guardar la dirección.');
    }

    $stmt->close();

    /*
     * 3. Actualizar o insertar datos socioeconómicos.
     */
    $stmt = $conn->prepare(
        'SELECT id FROM datos_socioeconomicos
         WHERE id_persona = ? LIMIT 1'
    );

    if (!$stmt) {
        throw new Exception('No se pudieron verificar los datos socioeconómicos.');
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $existenDatosSoc = $resultado->num_rows > 0;
    $stmt->close();

    if ($existenDatosSoc) {
        $sqlSoc = '
            UPDATE datos_socioeconomicos
            SET grupo_familiar = ?, personas_a_cargo = ?,
                fuente_ingresos = ?, ingreso_mensual = ?,
                tipo_vivienda = ?, tenencia_vivienda = ?
            WHERE id_persona = ?
        ';
    } else {
        $sqlSoc = '
            INSERT INTO datos_socioeconomicos
                (grupo_familiar, personas_a_cargo, fuente_ingresos,
                 ingreso_mensual, tipo_vivienda, tenencia_vivienda, id_persona)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ';
    }

    $stmt = $conn->prepare($sqlSoc);

    if (!$stmt) {
        throw new Exception('No se pudo preparar el guardado socioeconómico.');
    }

    $stmt->bind_param(
        'iisdssi',
        $grupo_familiar,
        $personas_a_cargo,
        $fuente_ingresos,
        $ingreso_mensual,
        $tipo_vivienda,
        $tenencia_vivienda,
        $id
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Error al guardar los datos socioeconómicos.');
    }

    $stmt->close();

    // Confirmar todos los cambios
    if (!$conn->commit()) {
        throw new Exception('No se pudieron confirmar los cambios.');
    }

    responder(true, 'Datos del estudiante actualizados correctamente.');

} catch (Throwable $e) {
    $conn->rollback();

    if ((int)$e->getCode() === 1062) {
        responder(
            false,
            'La cédula ingresada ya pertenece a otro estudiante.',
            422
        );
    }

    // Registrar el detalle para revisión sin mostrarlo al usuario.
    error_log('api_guardar_estudiante.php: ' . $e->getMessage());

    responder(
        false,
        $e->getMessage() === 'La cédula ingresada ya pertenece a otro estudiante.'
            ? $e->getMessage()
            : 'No se pudieron guardar los datos. Verifique la información e inténtelo nuevamente.',
        500
    );
}
?>
