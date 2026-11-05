<?php
include '../config/conexion.php';

$q        = isset($_GET['q']) ? trim($_GET['q']) : '';
$id_grupo = $_GET['id_grupo'] ?? 0;

// Si no hay búsqueda mínima, devolvemos vacío
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// 1. Tokenización: Dividimos la búsqueda en palabras
$palabras = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);

$condiciones = [];
$parametros  = [];
$tipos       = '';

// 2. Definimos el campo de búsqueda concatenado
$camposUnidos = "CONCAT_WS(' ', cedula, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido)";

// 3. Construimos las condiciones dinámicas para cada palabra
foreach ($palabras as $palabra) {
    $condiciones[] = "$camposUnidos LIKE ?";
    $parametros[]  = "%" . $palabra . "%";
    $tipos .= 's';
}

// 4. SQL con exclusiones y búsqueda inteligente
$sql = "
    SELECT 
        id, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, cedula
    FROM datos_personales
    WHERE estado = 'activo'
    -- Excluir si ya está en trayecto inicial
    AND id NOT IN (
        SELECT id_estudiante FROM trayecto_inicial_estudiantes
    ) 
    -- Excluir si ya está en otros grupos generales
    AND id NOT IN (
        SELECT id_estudiante FROM grupo_estudiantes
    )
    -- Aplicar búsqueda inteligente (todas las palabras deben coincidir)
    AND (" . implode(' AND ', $condiciones) . ")
    LIMIT 10
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error en SQL: ' . $conn->error]);
    exit;
}

// 5. Vinculación dinámica de parámetros
$stmt->bind_param($tipos, ...$parametros);
$stmt->execute();

$res         = $stmt->get_result();
$estudiantes = [];

while ($row = $res->fetch_assoc()) {
    $row['nombre_completo'] = trim(
        $row['primer_nombre']   . ' ' . 
        $row['segundo_nombre']  . ' ' . 
        $row['primer_apellido'] . ' ' . 
        $row['segundo_apellido']
    );
    $estudiantes[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($estudiantes);
?>