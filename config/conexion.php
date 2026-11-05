
<?php
// Verificar el acceso antes de conectar con la base de datos.
require_once __DIR__ . '/seguridad.php';

$host = "localhost";
$user = "root";
$pass = "";
$db   = "estudiantes";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log("Error de conexión MySQL: " . $conn->connect_error);
    http_response_code(500);
    exit("No se pudo conectar con la base de datos.");
}

$conn->set_charset("utf8mb4");
?>