<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/conexion.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'msg' => 'ID de estudiante no válido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
SELECT
    h.id_historial,
    c.id_carrera,
    c.nombre_carrera,
    h.nota,
    COALESCE(h.estado, 'pendiente') AS estado,
    COALESCE(h.consolidado, 0) AS consolidado,
    m.id_materia,

    CASE
        WHEN h.consolidado = 1
             AND h.nombre_materia_snapshot IS NOT NULL
             AND h.nombre_materia_snapshot != ''
        THEN h.nombre_materia_snapshot
        ELSE m.nombre_materia
    END AS nombre_materia,

    m.horas_semanales,
    t.id_trayecto,
    t.numero_trayecto,
    p.id_periodo,
    p.numero_periodo,
    COALESCE(g.nombre_grupo, 'Sin asignar') AS nombre_grupo,

    (
        SELECT COUNT(*)
        FROM materias_carrera mc
        WHERE mc.id_carrera = c.id_carrera
          AND mc.estado = 'activa'
    ) AS total_materias_carrera

FROM (
    /* Carreras vinculadas a los grupos actuales */
    SELECT DISTINCT
        gr.id_carrera,
        ge.id_estudiante
    FROM grupo_estudiantes ge
    INNER JOIN grupos gr
        ON gr.id_grupo = ge.id_grupo
    WHERE ge.id_estudiante = ?

    UNION

    /* Carreras recuperadas del historial académico */
    SELECT DISTINCT
        gr.id_carrera,
        hgm.id_estudiante
    FROM historial_grupo_materia hgm
    INNER JOIN grupos gr
        ON gr.id_grupo = hgm.id_grupo
    WHERE hgm.id_estudiante = ?
) estudiante_carreras

INNER JOIN carreras c
    ON c.id_carrera = estudiante_carreras.id_carrera

INNER JOIN trayectos t
    ON t.id_carrera = c.id_carrera

INNER JOIN periodos p
    ON p.id_trayecto = t.id_trayecto

INNER JOIN materias_carrera m
    ON m.id_carrera = c.id_carrera
   AND m.id_trayecto = t.id_trayecto
   AND m.id_periodo = p.id_periodo

LEFT JOIN historial_grupo_materia h
    ON h.id_estudiante = estudiante_carreras.id_estudiante
   AND h.id_materia = m.id_materia
   AND h.id_historial = (
        SELECT MAX(h_sub.id_historial)
        FROM historial_grupo_materia h_sub
        WHERE h_sub.id_estudiante = estudiante_carreras.id_estudiante
          AND h_sub.id_materia = m.id_materia
   )

LEFT JOIN grupos g
    ON h.id_grupo = g.id_grupo

WHERE c.estado = 'activa'
  AND (
        m.estado = 'activa'
        OR (
            m.estado <> 'activa'
            AND h.id_historial IS NOT NULL
            AND h.consolidado = 1
        )
  )

ORDER BY
    c.nombre_carrera,
    t.numero_trayecto,
    p.numero_periodo,
    nombre_materia
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Error preparando historial: ' . $conn->error);

    echo json_encode([
        'success' => false,
        'msg' => 'Error al preparar la consulta del historial académico.'
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();
    exit;
}

/*
 * Se utiliza el mismo ID dos veces:
 * 1. Para buscar las carreras de los grupos actuales.
 * 2. Para recuperar las carreras desde el historial.
 */
$stmt->bind_param('ii', $id, $id);

if (!$stmt->execute()) {
    error_log('Error consultando historial: ' . $stmt->error);

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => false,
        'msg' => 'Error al consultar el historial académico.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$result = $stmt->get_result();
$historial = [];

while ($row = $result->fetch_assoc()) {
    $historial[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'historial' => $historial
], JSON_UNESCAPED_UNICODE);
?>
