<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php';

$filtros = [];

// 1. Carreras normales (Excluyendo las eliminadas)
$sql = "SELECT id_carrera, nombre_carrera FROM carreras WHERE estado != 'eliminada' ORDER BY nombre_carrera ASC";
$res = $conn->query($sql);
$carreras = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Agregar opción de Trayecto Inicial como "carrera"
$carreras[] = ['id_carrera' => 'trayecto_inicial', 'nombre_carrera' => 'Trayecto Inicial'];
$filtros['carreras'] = $carreras;

// 2. Trayecto + Periodo normales (Solo de carreras que NO estén eliminadas)
$sql = "
    SELECT DISTINCT CONCAT(t.numero_trayecto,'-',p.numero_periodo) AS trayecto_periodo
    FROM trayectos t
    INNER JOIN periodos p ON t.id_trayecto = p.id_trayecto
    INNER JOIN carreras c ON t.id_carrera = c.id_carrera
    WHERE c.estado != 'eliminada'
    ORDER BY t.numero_trayecto, p.numero_periodo
";
$res = $conn->query($sql);
$trayecto_periodo = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Agregar opción de Trayecto Inicial
$trayecto_periodo[] = ['trayecto_periodo' => 'Trayecto Inicial'];
$filtros['trayecto_periodo'] = $trayecto_periodo;

// 3. Grupos normales (Excluyendo los eliminados)
$sql = "SELECT id_grupo, nombre_grupo FROM grupos WHERE estado != 'eliminado' ORDER BY nombre_grupo ASC";
$res = $conn->query($sql);
$grupos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// 4. Grupos de trayecto inicial 
// SINCRONIZACIÓN: Usamos CONCAT para ponerle el 'ti_' que espera la otra API
// CORRECCIÓN: Se elimina el WHERE estado porque esta tabla no posee esa columna
$sql = "SELECT CONCAT('ti_', id_trayecto_inicial) AS id_grupo, nombre_grupo_inicial AS nombre_grupo FROM trayecto_inicial ORDER BY nombre_grupo_inicial ASC";
$res = $conn->query($sql);
$grupos_iniciales = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Unir todos los grupos válidos (regulares + iniciales con prefijo)
$filtros['grupos'] = array_merge($grupos, $grupos_iniciales);

// Estados de estudiante
$filtros['estados'] = [
    ['estado' => 'activo', 'nombre_estado' => 'Activo'],
    ['estado' => 'retirado', 'nombre_estado' => 'Retirado'],
    ['estado' => 'egresado', 'nombre_estado' => 'Egresado'],
    ['estado' => 'congelado', 'nombre_estado' => 'Congelado']
];

echo json_encode([
    'success' => true,
    'filtros' => $filtros
], JSON_UNESCAPED_UNICODE);