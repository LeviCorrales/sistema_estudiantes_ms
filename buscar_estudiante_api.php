<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/sistema_gestion_aldea/config/conexion.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$estudiantes = [];
$totalEstudiantes = 0;

// 1. Contar total de estudiantes activos (Opcional, para estadísticas)
$resTotal = $conn->query("SELECT COUNT(*) AS total FROM datos_personales WHERE estado = 'activo'");
if ($resTotal) {
    $totalEstudiantes = $resTotal->fetch_assoc()['total'];
}

if ($q === '') {
    echo json_encode([
        'success' => true,
        'estudiantes' => [],
        'total' => $totalEstudiantes
    ]);
    exit;
}

// --- INICIO DE LÓGICA INTELIGENTE ---

// 2. Dividimos la cadena de búsqueda por espacios (ej: "Elvis Corrales" -> ["Elvis", "Corrales"])
$words = explode(' ', $q);
$words = array_filter($words); // Eliminamos espacios extra

$sql = "SELECT id, cedula, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido 
        FROM datos_personales 
        WHERE estado = 'activo'";

$params = [];
$types = "";

// 3. Por cada palabra, creamos un grupo de condiciones
foreach ($words as $word) {
    $sql .= " AND (
        cedula LIKE ? 
        OR primer_nombre LIKE ? 
        OR segundo_nombre LIKE ? 
        OR primer_apellido LIKE ? 
        OR segundo_apellido LIKE ?
    )";
    
    $likeWord = "%$word%";
    // Añadimos la palabra 5 veces (una por cada columna del OR)
    for ($i = 0; $i < 5; $i++) {
        $params[] = $likeWord;
        $types .= "s";
    }
}

$sql .= " LIMIT 50";

$stmt = $conn->prepare($sql);

// 4. Vinculamos los parámetros dinámicamente
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $estudiantes[] = $row;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'estudiantes' => $estudiantes,
    'total' => $totalEstudiantes
]);
?>