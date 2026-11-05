<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php';

$where = [];
$params = [];
$types  = "";

// 1. Recibir parámetros de paginación
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Recibir filtros
$q = $_GET['q'] ?? '';
$carrera = $_GET['carrera'] ?? '';
$trayecto_periodo = $_GET['trayecto_periodo'] ?? '';
$grupo = $_GET['grupo'] ?? '';
$estado = $_GET['estado'] ?? 'activo';

// Buscar por cédula o nombre
if ($q) {
    $qParam = "%$q%";
    $where[] = "(dp.cedula LIKE ? OR dp.primer_nombre LIKE ? OR dp.primer_apellido LIKE ?)";
    $params = array_merge($params, [$qParam, $qParam, $qParam]);
    $types .= "sss";
}

// Filtrar por estado
if ($estado) {
    $where[] = "dp.estado = ?";
    $params[] = $estado;
    $types .= "s";
}

// Filtrar por carrera
if ($carrera) {
    if ($carrera === 'trayecto_inicial') {
        $where[] = "ti_est.id_estudiante IS NOT NULL";
    } else {
        $where[] = "c.id_carrera = ?";
        $params[] = intval($carrera);
        $types .= "i";
    }
}

// Filtrar por trayecto + periodo
if ($trayecto_periodo) {
    if ($trayecto_periodo === 'Trayecto Inicial') {
        $where[] = "ti_est.id_estudiante IS NOT NULL";
    } else {
        $tp = explode("-", $trayecto_periodo);
        if (count($tp) === 2) {
            $trayecto = intval($tp[0]);
            $periodo  = intval($tp[1]);
            $where[] = "t.numero_trayecto = ? AND p.numero_periodo = ?";
            $params[] = $trayecto;
            $params[] = $periodo;
            $types .= "ii";
        }
    }
}

// Filtrar por grupo
if ($grupo) {
    if (strpos($grupo, 'ti_') === 0) {
        $id_ti = intval(str_replace('ti_', '', $grupo));
        $where[] = "ti.id_trayecto_inicial = ?";
        $params[] = $id_ti;
        $types .= "i";
    } else {
        $where[] = "g.id_grupo = ?";
        $params[] = intval($grupo);
        $types .= "i";
    }
}

// Consulta principal
// CORRECCIÓN: 'ti.estado' no existe en tu DDL, se reemplaza por 'activa' de manera fija para no romper el SQL
$sql = "
SELECT 
    dp.id,
    dp.cedula,
    dp.foto,
    CONCAT(dp.primer_nombre, ' ', IFNULL(dp.segundo_nombre,''), ' ', dp.primer_apellido, ' ', IFNULL(dp.segundo_apellido,'')) AS nombre_completo,
    CASE 
        WHEN ti_est.id_estudiante IS NOT NULL THEN 'Trayecto Inicial'
        ELSE c.nombre_carrera
    END AS nombre_carrera,
    CASE 
        WHEN ti_est.id_estudiante IS NOT NULL THEN 'Trayecto Inicial'
        ELSE CONCAT(t.numero_trayecto,'-',p.numero_periodo)
    END AS trayecto_periodo,
    COALESCE(g.nombre_grupo, ti.nombre_grupo_inicial) AS nombre_grupo,
    
    c.estado AS estado_carrera,
    g.estado AS estado_grupo,
    'activa' AS estado_ti 
FROM datos_personales dp
LEFT JOIN grupo_estudiantes ge ON dp.id = ge.id_estudiante
LEFT JOIN grupos g ON ge.id_grupo = g.id_grupo
LEFT JOIN carreras c ON g.id_carrera = c.id_carrera
LEFT JOIN trayectos t ON g.id_trayecto = t.id_trayecto
LEFT JOIN periodos p ON g.id_periodo = p.id_periodo
LEFT JOIN trayecto_inicial_estudiantes ti_est ON dp.id = ti_est.id_estudiante
LEFT JOIN trayecto_inicial ti ON ti_est.id_trayecto_inicial = ti.id_trayecto_inicial
";

// Aplicar filtros
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// 2. Aplicar Paginación Dinámica
$sql .= " ORDER BY dp.primer_apellido ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$estudiantes = [];
while ($row = $result->fetch_assoc()) {
    $estudiantes[] = $row;
}

echo json_encode([
    'success' => true,
    'estudiantes' => $estudiantes,
    'count' => count($estudiantes)
], JSON_UNESCAPED_UNICODE);