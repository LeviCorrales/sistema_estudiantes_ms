<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$accion = $_POST['accion'] ?? '';
$tipo   = $_POST['tipo'] ?? ''; // 'carrera' o 'trayecto'
$id     = intval($_POST['id'] ?? 0);

if (!$id || !$accion || !$tipo) {
    echo json_encode(["success" => false, "error" => "Datos insuficientes"]);
    exit;
}

// Determinamos la tabla y el nombre de la columna ID
$tabla     = ($tipo === 'trayecto') ? 'trayecto_inicial' : 'carreras';
$colId     = ($tipo === 'trayecto') ? 'id_trayecto_inicial' : 'id_carrera';
$colNombre = ($tipo === 'trayecto') ? 'nombre_grupo_inicial' : 'nombre_carrera';

switch ($accion) {
    case 'editar':
        $nombre = trim($_POST['nombre']);
        $stmt = $conn->prepare("UPDATE $tabla SET $colNombre = ? WHERE $colId = ?");
        $stmt->bind_param("si", $nombre, $id);
        break;

    case 'eliminar':
        // CAMBIO CRÍTICO: Ya no borra, ahora cambia el estado a 'eliminada'
        $stmt = $conn->prepare("UPDATE $tabla SET estado = 'eliminada' WHERE $colId = ?");
        $stmt->bind_param("i", $id);
        break;

    case 'estado': 
        // Ahora es dinámico para ambos tipos. Alterna entre 'activa' e 'inhabilitada'
        $stmt = $conn->prepare("UPDATE $tabla SET estado = IF(estado='activa', 'inhabilitada', 'activa') WHERE $colId = ?");
        $stmt->bind_param("i", $id);
        break;
        
    default:
        echo json_encode(["success" => false, "error" => "Acción no válida"]);
        exit;
}

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}