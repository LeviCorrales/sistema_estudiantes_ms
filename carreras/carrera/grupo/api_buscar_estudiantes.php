<?php
// Aseguramos que la respuesta sea siempre JSON y UTF-8
header('Content-Type: application/json; charset=utf-8');

require_once '../../../config/conexion.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validación mínima: si es menor a 2 caracteres devolvemos array vacío
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// 1. Dividimos la búsqueda en palabras individuales
$palabras = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);

$condiciones = [];
$parametros = [];
$tipos = '';

// 2. Definimos la cadena de búsqueda (Cédula + Nombres + Apellidos)
$camposUnidos = "CONCAT_WS(' ', cedula, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido)";

// 3. Construimos la consulta dinámica según la cantidad de palabras
foreach ($palabras as $palabra) {
    $condiciones[] = "$camposUnidos LIKE ?";
    $parametros[] = "%" . $palabra . "%";
    $tipos .= 's'; 
}

// 4. Armamos el SQL final
// Hemos añadido: AND estado = 'activo'
$sql = "SELECT id, cedula, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido 
        FROM datos_personales
        WHERE (" . implode(' AND ', $condiciones) . ") 
        AND estado = 'activo'
        LIMIT 20";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error en la preparación: ' . $conn->error]);
    exit;
}

// 5. Pasamos los parámetros dinámicamente
$stmt->bind_param($tipos, ...$parametros);

$stmt->execute();
$res = $stmt->get_result();

$estudiantes = [];
while ($row = $res->fetch_assoc()) {
    // Agregamos un campo virtual de "nombre_completo"
    $row['nombre_completo'] = trim(
        $row['primer_nombre'] . ' ' . 
        $row['segundo_nombre'] . ' ' . 
        $row['primer_apellido'] . ' ' . 
        $row['segundo_apellido']
    );
    
    $estudiantes[] = $row;
}

$stmt->close();

// Enviamos el array de resultados
echo json_encode($estudiantes);
?>