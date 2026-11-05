<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'msg' => 'ID no válido']);
    exit;
}

try {
    // 1. Consulta de Datos Personales, Dirección y Datos Socioeconómicos
    $sql = "
    SELECT 
        dp.id,
        dp.cedula,
        dp.primer_nombre,
        dp.segundo_nombre,
        dp.primer_apellido,
        dp.segundo_apellido,
        dp.sexo,
        dp.estado_civil,
        dp.fecha_nacimiento,
        dp.telefono_celular,
        dp.correo_electronico,
        dp.etnia_aborigen,
        dp.fecha_registro,
        dp.foto,
        dp.estado,

        dr.id_estado,
        dr.id_municipio,
        dr.id_parroquia,
        dr.avenida,
        dr.calle_sector,
        dr.casa_apartamento,
        dr.punto_referencia,

        e.nombre AS estado_nombre,
        m.nombre AS municipio_nombre,
        p.nombre AS parroquia_nombre,

        dse.grupo_familiar,
        dse.personas_a_cargo,
        dse.fuente_ingresos,
        dse.ingreso_mensual,
        dse.tipo_vivienda,
        dse.tenencia_vivienda

    FROM datos_personales dp
    LEFT JOIN direccion_residencia dr ON dp.id = dr.id_persona
    LEFT JOIN datos_socioeconomicos dse ON dp.id = dse.id_persona
    LEFT JOIN estados e ON dr.id_estado = e.id
    LEFT JOIN municipios m ON dr.id_municipio = m.id
    LEFT JOIN parroquias p ON dr.id_parroquia = p.id
    WHERE dp.id = ?
    LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $estudiante = $result->fetch_assoc();
    $stmt->close();

    if (!$estudiante) {
        echo json_encode(['success' => false, 'msg' => 'Estudiante no encontrado']);
        exit;
    }

    // 2. Traer TODAS las carreras y grupos asociados al estudiante (Soporta Múltiples Carreras)
    $sqlGrupos = "
    SELECT 
        c.id_carrera,
        c.nombre_carrera,
        g.id_grupo,
        g.nombre_grupo,
        t.id_trayecto,
        t.numero_trayecto,
        p.id_periodo,
        p.numero_periodo
    FROM grupo_estudiantes ge
    INNER JOIN grupos g ON ge.id_grupo = g.id_grupo
    INNER JOIN periodos p ON g.id_periodo = p.id_periodo
    INNER JOIN trayectos t ON p.id_trayecto = t.id_trayecto
    INNER JOIN carreras c ON t.id_carrera = c.id_carrera
    WHERE ge.id_estudiante = ?
    ORDER BY c.nombre_carrera ASC
    ";

    $stmtG = $conn->prepare($sqlGrupos);
    $stmtG->bind_param("i", $id);
    $stmtG->execute();
    $resG = $stmtG->get_result();

    $grupos = [];
    while ($row = $resG->fetch_assoc()) {
        $grupos[] = $row;
    }
    $stmtG->close();

    $estudiante['grupos'] = $grupos;

    // 3. Calcular Promedio General acumulado (Materia Regular + Trayecto Inicial)
    $sqlPromedio = "
    SELECT ROUND(AVG(nota), 2) AS promedio
    FROM (
        SELECT nota FROM historial_grupo_materia WHERE id_estudiante = ? AND nota IS NOT NULL
        UNION ALL
        SELECT nota FROM historial_academico_inicial WHERE id_estudiante = ? AND nota IS NOT NULL
    ) AS notas_totales
    ";

    $stmtP = $conn->prepare($sqlPromedio);
    $stmtP->bind_param("ii", $id, $id);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    $rowP = $resP->fetch_assoc();
    $stmtP->close();

    $estudiante['promedio'] = $rowP['promedio'] ?? null;

    // Respuesta final
    echo json_encode(['success' => true, 'estudiante' => $estudiante], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>