<?php
require_once '../../config/conexion.php';
header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';
$id     = intval($_POST['id'] ?? 0);

// Validación inicial de ID
if (!$id) {
  echo json_encode(["success" => false, "error" => "ID no válido"]);
  exit;
}

$stmt = null;

switch ($accion) {
  case 'editar':
    $nombre = trim($_POST['nombre'] ?? '');
    
    if (empty($nombre)) {
      echo json_encode(["success" => false, "error" => "El nombre no puede estar vacío"]);
      exit;
    }

    $stmt = $conn->prepare("UPDATE carreras SET nombre_carrera = ? WHERE id_carrera = ?");
    $stmt->bind_param("si", $nombre, $id);
    break;

  case 'estado':
    // Toggle entre activa e inhabilitada de forma atómica en la base de datos
    $stmt = $conn->prepare("UPDATE carreras SET estado = IF(estado='activa', 'inhabilitada', 'activa') WHERE id_carrera = ?");
    $stmt->bind_param("i", $id);
    break;

  case 'eliminar':
    $stmt = $conn->prepare("DELETE FROM carreras WHERE id_carrera = ?");
    $stmt->bind_param("i", $id);
    break;

  default:
    echo json_encode(["success" => false, "error" => "Acción no reconocida"]);
    exit;
}

// Ejecución y respuesta
if ($stmt && $stmt->execute()) {
  $nuevo_estado = '';

  // Si la acción fue cambiar estado, obtenemos el valor actualizado para refrescar la UI
  if ($accion === 'estado') {
    $res = $conn->query("SELECT estado FROM carreras WHERE id_carrera = $id");
    if ($fila = $res->fetch_assoc()) {
      $nuevo_estado = $fila['estado'];
    }
  }

  echo json_encode([
    "success"      => true, 
    "message"      => "¡Operación exitosa!", 
    "nuevo_estado" => $nuevo_estado
  ]);
} else {
  echo json_encode([
    "success" => false, 
    "error"   => "Error al procesar la solicitud: " . ($conn->error ?? 'Error de sentencia')
  ]);
}

if ($stmt) {
  $stmt->close();
}
$conn->close();