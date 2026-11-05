<?php
// Evitar que cualquier error previo ensucie el archivo SQL
ob_start();

$host = "localhost";
$nombre_db = "estudiantes"; 
$usuario = "root";
$password = "";
$dumpPath = 'C:\sistema_estudiantes_ms\xampp\mysql\bin\mysqldump.exe';

$fecha = date("d-m-Y_H-i-s");
$nombre_archivo = "Respaldo_{$nombre_db}_{$fecha}.sql";

// Construir comando
$auth = "-u $usuario" . (!empty($password) ? " -p$password" : "");
$comando = "\"$dumpPath\" --opt -h $host $auth $nombre_db";

// Limpiar cualquier salida previa (espacios, warnings)
ob_clean();

// Cabeceras
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Ejecutar
passthru($comando);

// Terminar el script inmediatamente
exit;