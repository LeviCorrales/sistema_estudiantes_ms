<?php
// acciones_grupo.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php'; 

$accion   = $_POST['accion'] ?? '';
$id_grupo = intval($_POST['id'] ?? 0);

if ($id_grupo <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de grupo inválido.']);
    exit;
}

// Función auxiliar para realizar la actualización lógica (Grupo + Estudiantes)
function cambiarEstado($conn, $id_grupo, $estado_grupo, $estado_estudiantes) {
    $conn->begin_transaction();
    try {
        // 1. Actualizar el grupo
        $stmt1 = $conn->prepare("UPDATE grupos SET estado = ? WHERE id_grupo = ?");
        $stmt1->bind_param("si", $estado_grupo, $id_grupo);
        $stmt1->execute();
        $stmt1->close();

        // 2. Actualizar a todos los estudiantes vinculados al grupo
        $stmt2 = $conn->prepare("
            UPDATE datos_personales dp
            INNER JOIN grupo_estudiantes ge ON dp.id = ge.id_estudiante
            SET dp.estado = ?
            WHERE ge.id_grupo = ?
        ");
        $stmt2->bind_param("si", $estado_estudiantes, $id_grupo);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()];
    }
}

switch ($accion) {
    case 'congelar':
        $resultado = cambiarEstado($conn, $id_grupo, 'congelado', 'congelado');
        echo json_encode($resultado);
        break;

    case 'activar':
        $resultado = cambiarEstado($conn, $id_grupo, 'activo', 'activo');
        echo json_encode($resultado);
        break;
        
    case 'editar':
        $nuevo_nombre = trim($_POST['nombre'] ?? '');
        if (empty($nuevo_nombre)) {
            echo json_encode(['success' => false, 'error' => 'El nombre no puede estar vacío.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE grupos SET nombre_grupo = ? WHERE id_grupo = ?");
        $stmt->bind_param("si", $nuevo_nombre, $id_grupo);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'eliminar':
        // CAMBIO CRÍTICO: Ahora hace un borrado lógico (Soft Delete)
        try {
            $stmt = $conn->prepare("UPDATE grupos SET estado = 'eliminado' WHERE id_grupo = ?");
            $stmt->bind_param("i", $id_grupo);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al eliminar lógicamente el grupo.']);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => "Acción '{$accion}' no reconocida."]);
        break;
}

$conn->close();
?>