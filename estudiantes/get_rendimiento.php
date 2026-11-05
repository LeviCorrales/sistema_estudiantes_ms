<?php
header('Content-Type: application/json');
// Incluye aquí tu archivo de conexión: include 'conexion.php';
// $conn = new mysqli("localhost", "usuario", "password", "base_datos");

$id_estudiante = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_estudiante <= 0) {
    echo json_encode(['error' => 'ID no válido']);
    exit;
}

// 1. Obtener la carrera del estudiante y el total de materias de esa carrera
$sql_carrera = "SELECT g.id_carrera, 
                (SELECT COUNT(*) FROM materias_carrera mc WHERE mc.id_carrera = g.id_carrera) as total_materias
                FROM grupo_estudiantes ge
                JOIN grupos g ON ge.id_grupo = g.id_grupo
                WHERE ge.id_estudiante = ? LIMIT 1";

$stmt = $conn->prepare($sql_carrera);
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$res_carrera = $stmt->get_result()->fetch_assoc();

if (!$res_carrera) {
    echo json_encode(['porcentaje' => 0, 'promedio' => 0, 'texto_progreso' => 'Sin carrera']);
    exit;
}

$id_carrera = $res_carrera['id_carrera'];
$total_materias = $res_carrera['total_materias'];

// 2. Contar materias aprobadas y calcular promedio real
// Según tus triggers, el estado es 'Aprobada' si nota >= 12
$sql_notas = "SELECT 
                COUNT(*) as aprobadas, 
                AVG(nota) as promedio 
              FROM historial_grupo_materia 
              WHERE id_estudiante = ? AND id_carrera = ? AND estado = 'Aprobada'";

$stmt_notas = $conn->prepare($sql_notas);
$stmt_notas->bind_param("ii", $id_estudiante, $id_carrera);
$stmt_notas->execute();
$res_notas = $stmt_notas->get_result()->fetch_assoc();

$aprobadas = $res_notas['aprobadas'] ?? 0;
$promedio = $res_notas['promedio'] ?? 0;

// 3. Calcular porcentaje (Evitar división por cero)
$porcentaje = ($total_materias > 0) ? ($aprobadas / $total_materias) * 100 : 0;

echo json_encode([
    "porcentaje" => round($porcentaje, 1),
    "promedio" => round($promedio, 1),
    "texto_progreso" => "$aprobadas de $total_materias materias"
]);
?>