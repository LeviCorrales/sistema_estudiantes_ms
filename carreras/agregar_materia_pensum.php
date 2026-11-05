<?php
// Limpiar el buffer para evitar que espacios en blanco rompan el JSON
if (ob_get_length()) ob_clean(); 

header('Content-Type: application/json; charset=utf-8');
include '../config/conexion.php';

// Sanitización rigurosa de entradas
$nombre   = isset($_POST['nombre_materia']) ? trim($_POST['nombre_materia']) : null;
$codigo   = isset($_POST['codigo_materia']) ? trim($_POST['codigo_materia']) : '';
$creditos = isset($_POST['creditos']) ? intval($_POST['creditos']) : 0;
$horas    = isset($_POST['horas_semanales']) ? intval($_POST['horas_semanales']) : 0;

// Validaciones del lado del servidor (Capa secundaria de seguridad)
if (empty($nombre)) {
    echo json_encode(['success' => false, 'msg' => 'El nombre de la materia es totalmente obligatorio.']);
    exit;
}

try {
    /**
     * 1. CONTROL DE DUPLICADOS & BORRADO LÓGICO
     * Verificamos si ya existe una materia con el mismo nombre o el mismo código
     */
    $sqlCheck = "SELECT id_materia_inicial, activo FROM materias_pensum_inicial 
                 WHERE nombre_materia = ? OR (codigo_materia = ? AND codigo_materia != '') 
                 LIMIT 1";
                 
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("ss", $nombre, $codigo);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    $materiaExistente = $resCheck->fetch_assoc();
    $stmtCheck->close();

    if ($materiaExistente) {
        // CASO A: La materia existe y está ACTIVA
        if ($materiaExistente['activo'] == 1) {
            echo json_encode([
                'success' => false, 
                'msg' => 'Restricción: Ya existe una materia activa con ese mismo nombre o código en el pensum.'
            ]);
            exit;
        } 
        
        // CASO B: La materia existe pero estaba INACTIVA (Reactivación inteligente)
        $idExistente = $materiaExistente['id_materia_inicial'];
        
        $sqlReactivar = "UPDATE materias_pensum_inicial 
                         SET activo = 1, codigo_materia = ?, creditos = ?, horas_semanales = ? 
                         WHERE id_materia_inicial = ?";
                         
        $stmtReact = $conn->prepare($sqlReactivar);
        $stmtReact->bind_param("siii", $codigo, $creditos, $horas, $idExistente);
        
        if ($stmtReact->execute()) {
            echo json_encode([
                'success'         => true,
                'id'              => $idExistente,
                'nombre_materia'  => $nombre,
                'codigo_materia'  => $codigo,
                'creditos'        => $creditos,
                'horas_semanales' => $horas,
                'msg'             => 'Materia histórica reactivada con éxito.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("Error al reactivar la materia histórica: " . $stmtReact->error);
        }
        $stmtReact->close();
        exit;
    }

    /**
     * 2. INSERCIÓN NORMAL
     * Si no existe en ningún registro del pasado, procedemos a crearla normalmente
     */
    $sqlInsert = "INSERT INTO materias_pensum_inicial (nombre_materia, codigo_materia, creditos, horas_semanales, activo)
                  VALUES (?, ?, ?, ?, 1)";

    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ssii", $nombre, $codigo, $creditos, $horas);

    if ($stmtInsert->execute()) {
        echo json_encode([
            'success'         => true,
            'id'              => $stmtInsert->insert_id,
            'nombre_materia'  => $nombre,
            'codigo_materia'  => $codigo,
            'creditos'        => $creditos,
            'horas_semanales' => $horas
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception("Error al insertar la nueva materia: " . $stmtInsert->error);
    }
    $stmtInsert->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'msg' => 'Error interno del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>