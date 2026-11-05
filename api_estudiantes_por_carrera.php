<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config/conexion.php';

$data = [];

/**
 * 📌 Consulta: Estudiantes por Carrera
 * Se realiza un conteo basado en la relación entre carreras, grupos y estudiantes.
 * Se filtran solo carreras y grupos activos para mantener la integridad de la estadística.
 */
$sql = "
    SELECT 
        c.nombre_carrera, 
        COUNT(DISTINCT ge.id_estudiante) AS total_estudiantes
    FROM carreras c
    LEFT JOIN grupos g 
        ON g.id_carrera = c.id_carrera 
        AND g.estado = 'activo'
    LEFT JOIN grupo_estudiantes ge 
        ON ge.id_grupo = g.id_grupo
    WHERE c.estado = 'activa'
    GROUP BY c.id_carrera, c.nombre_carrera
    ORDER BY total_estudiantes DESC
";

$res = $conn->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $data[] = [
            'nombre'   => $row['nombre_carrera'],
            'cantidad' => (int)$row['total_estudiantes']
        ];
    }
}

// 📌 Respuesta JSON para el gráfico del Dashboard
echo json_encode([
    'success' => true,
    'datos'   => $data
], JSON_UNESCAPED_UNICODE);