<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config/conexion.php';

/**
 * 📌 1. Total de estudiantes (Cómputo general)
 */
$totalRes = $conn->query("SELECT COUNT(*) AS total FROM datos_personales");
$totalRow = $totalRes->fetch_assoc();
$totalEstudiantes = (int)$totalRow['total'];

/**
 * 📌 2. Total estudiantes recientes (Registrados en los últimos 30 días)
 */
$recientesRes = $conn->query("
    SELECT COUNT(*) AS total 
    FROM datos_personales 
    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
");
$recientesRow = $recientesRes->fetch_assoc();
$totalRecientes = (int)$recientesRow['total'];

/**
 * 📌 3. Listado detallado de estudiantes recientes con JOINs
 * Se utiliza LEFT JOIN para asegurar que se muestre el estudiante 
 * aunque no tenga grupo o carrera asignada.
 */
$sql = "
    SELECT 
        dp.id,
        dp.cedula,
        CONCAT(
            dp.primer_nombre, ' ', 
            IFNULL(dp.segundo_nombre,''), ' ', 
            dp.primer_apellido, ' ', 
            IFNULL(dp.segundo_apellido,'')
        ) AS nombre_completo,
        dp.foto,
        dp.fecha_registro,
        c.nombre_carrera,
        CONCAT(t.numero_trayecto, '-', p.numero_periodo) AS trayecto_periodo,
        g.nombre_grupo
    FROM datos_personales dp
    LEFT JOIN grupo_estudiantes ge ON dp.id = ge.id_estudiante
    LEFT JOIN grupos g ON ge.id_grupo = g.id_grupo
    LEFT JOIN carreras c ON g.id_carrera = c.id_carrera
    LEFT JOIN trayectos t ON g.id_trayecto = t.id_trayecto
    LEFT JOIN periodos p ON g.id_periodo = p.id_periodo
    WHERE dp.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    ORDER BY dp.fecha_registro DESC
    LIMIT 20
";

$res = $conn->query($sql);
$recientes = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $recientes[] = $row;
    }
}

// 📌 4. Respuesta en formato JSON
echo json_encode([
    'success' => true,
    'total_estudiantes' => $totalEstudiantes,
    'total_recientes' => $totalRecientes,
    'recientes' => $recientes
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);