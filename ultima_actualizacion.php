<?php
require_once 'config/conexion.php';

$query = $conn->query("
    SELECT fecha 
    FROM auditoria_bd 
    ORDER BY fecha DESC 
    LIMIT 1
");

if ($query && $query->num_rows > 0) {
    $ultima = date("d/m/Y H:i", strtotime($query->fetch_assoc()['fecha']));
} else {
    $ultima = "Sin registros";
}

?>

