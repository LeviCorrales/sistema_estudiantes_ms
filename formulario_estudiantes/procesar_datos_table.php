<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'errors' => ['Método no permitido']]);
    exit;
}

$errores = [];
$exito = true;

$estudiantes = $_POST['estudiantes'] ?? [];

if (empty($estudiantes)) {
    echo json_encode(['success' => false, 'errors' => ['No se enviaron estudiantes']]);
    exit;
}

$conn->begin_transaction();

try {

    foreach ($estudiantes as $index => $est) {

        /* =========================
           DATOS PERSONALES
        ==========================*/
        $cedula            = trim($est['cedula'] ?? '');
        $primer_nombre     = trim($est['primer_nombre'] ?? '');
        $segundo_nombre    = trim($est['segundo_nombre'] ?? '');
        $primer_apellido   = trim($est['primer_apellido'] ?? '');
        $segundo_apellido  = trim($est['segundo_apellido'] ?? '');
        $sexo              = trim($est['sexo'] ?? '');
        $estado_civil      = trim($est['estado_civil'] ?? '');
        $fecha_nacimiento  = trim($est['fecha_nacimiento'] ?? '');
        $telefono_celular  = trim($est['telefono_celular'] ?? '');
        $correo_electronico = trim($est['correo_electronico'] ?? '');
        $etnia_aborigen    = trim($est['etnia_aborigen'] ?? 'No');

        /* =========================
           DIRECCIÓN
        ==========================*/
        $id_estado     = is_numeric($est['id_estado'] ?? null) ? (int)$est['id_estado'] : 0;
        $id_municipio  = is_numeric($est['id_municipio'] ?? null) ? (int)$est['id_municipio'] : 0;
        $id_parroquia  = is_numeric($est['id_parroquia'] ?? null) ? (int)$est['id_parroquia'] : 0;

        $avenida           = trim($est['avenida'] ?? '');
        $calle_sector      = trim($est['calle_sector'] ?? '');
        $casa_apartamento  = trim($est['casa_apartamento'] ?? '');
        $punto_referencia  = trim($est['punto_referencia'] ?? '');

        /* =========================
           SOCIOECONÓMICOS
        ==========================*/
        $grupo_familiar    = is_numeric($est['grupo_familiar'] ?? null) ? (int)$est['grupo_familiar'] : 0;
        $personas_a_cargo  = is_numeric($est['personas_a_cargo'] ?? null) ? (int)$est['personas_a_cargo'] : 0;
        $fuente_ingresos   = trim($est['fuente_ingresos'] ?? '');
        $ingreso_mensual   = is_numeric($est['ingreso_mensual'] ?? null) ? (float)$est['ingreso_mensual'] : 0;
        $tipo_vivienda     = trim($est['tipo_vivienda'] ?? '');
        $tenencia_vivienda = trim($est['tenencia_vivienda'] ?? '');

        /* =========================
           VALIDACIONES
        ==========================*/
        $filaErrores = [];

        if (!$cedula || !preg_match('/^\d+$/', $cedula))
            $filaErrores[] = "Fila ".($index+1).": Cédula inválida";

        if (!$primer_nombre)
            $filaErrores[] = "Fila ".($index+1).": Primer nombre obligatorio";

        if (!$primer_apellido)
            $filaErrores[] = "Fila ".($index+1).": Primer apellido obligatorio";

        if (!in_array($sexo, ['M','F']))
            $filaErrores[] = "Fila ".($index+1).": Sexo inválido";

        if (!in_array($estado_civil, ['Soltero','Casado','Divorciado','Viudo']))
            $filaErrores[] = "Fila ".($index+1).": Estado civil inválido";

        if (!$fecha_nacimiento || !DateTime::createFromFormat('Y-m-d', $fecha_nacimiento))
            $filaErrores[] = "Fila ".($index+1).": Fecha de nacimiento inválida";

        if (!preg_match('/^\d{11}$/', $telefono_celular))
            $filaErrores[] = "Fila ".($index+1).": Teléfono celular inválido";

        if ($correo_electronico && !filter_var($correo_electronico, FILTER_VALIDATE_EMAIL))
            $filaErrores[] = "Fila ".($index+1).": Correo electrónico inválido";

        if ($id_estado <= 0 || $id_municipio <= 0 || $id_parroquia <= 0)
            $filaErrores[] = "Fila ".($index+1).": Dirección incompleta";

        /* =========================
           CÉDULA DUPLICADA
        ==========================*/
        $check = $conn->prepare("SELECT id FROM datos_personales WHERE cedula = ?");
        $check->bind_param("s", $cedula);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0)
            $filaErrores[] = "Fila ".($index+1).": Cédula ya registrada";
        $check->close();

        if ($filaErrores) {
            $errores = array_merge($errores, $filaErrores);
            $exito = false;
            continue;
        }

        /* =========================
           INSERT DATOS PERSONALES
        ==========================*/
        $stmt = $conn->prepare("
            INSERT INTO datos_personales (
                cedula, primer_nombre, segundo_nombre,
                primer_apellido, segundo_apellido,
                sexo, estado_civil, fecha_nacimiento,
                telefono_celular, correo_electronico,
                etnia_aborigen
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssssssss",
            $cedula, $primer_nombre, $segundo_nombre,
            $primer_apellido, $segundo_apellido,
            $sexo, $estado_civil, $fecha_nacimiento,
            $telefono_celular, $correo_electronico,
            $etnia_aborigen
        );

        if (!$stmt->execute()) {
            $errores[] = "Fila ".($index+1).": Error al insertar datos personales";
            $exito = false;
            $stmt->close();
            continue;
        }

        $id_persona = $stmt->insert_id;
        $stmt->close();

        /* =========================
           INSERT DIRECCIÓN
        ==========================*/
        $stmt2 = $conn->prepare("
            INSERT INTO direccion_residencia
            (id_persona, id_estado, id_municipio, id_parroquia, avenida, calle_sector, casa_apartamento, punto_referencia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt2->bind_param(
            "iiisssss",
            $id_persona, $id_estado, $id_municipio, $id_parroquia,
            $avenida, $calle_sector, $casa_apartamento, $punto_referencia
        );
        $stmt2->execute();
        $stmt2->close();

        /* =========================
           INSERT SOCIOECONÓMICOS
        ==========================*/
        $stmt3 = $conn->prepare("
            INSERT INTO datos_socioeconomicos
            (id_persona, grupo_familiar, personas_a_cargo, fuente_ingresos, ingreso_mensual, tipo_vivienda, tenencia_vivienda)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt3->bind_param(
            "iiisdss",
            $id_persona, $grupo_familiar, $personas_a_cargo,
            $fuente_ingresos, $ingreso_mensual,
            $tipo_vivienda, $tenencia_vivienda
        );
        $stmt3->execute();
        $stmt3->close();
    }

    $conn->commit();
    echo json_encode(['success' => $exito, 'errors' => $errores]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
}

$conn->close();
