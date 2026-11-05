<?php
require_once '../../../config/conexion.php';
header('Content-Type: application/json');

// Recibir JSON
$data = json_decode(file_get_contents('php://input'), true);
$id_grupo = isset($data['id_grupo']) ? (int)$data['id_grupo'] : 0;

if($id_grupo <= 0){
    echo json_encode(['success'=>false, 'error'=>'Grupo inválido']);
    exit;
}

try {
    // 🔹 Obtener el trayecto y periodo actual
    $stmt = $conn->prepare("SELECT id_trayecto, id_periodo, id_carrera FROM grupos WHERE id_grupo = ?");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    $grupo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$grupo) throw new Exception("Grupo no encontrado");

    $id_trayecto = (int)$grupo['id_trayecto'];
    $id_periodo  = (int)$grupo['id_periodo'];
    $id_carrera  = (int)$grupo['id_carrera'];

    // 🔹 Buscar periodo anterior
    $stmt = $conn->prepare("
        SELECT id_periodo, numero_periodo, id_trayecto 
        FROM periodos 
        WHERE id_trayecto = ? AND numero_periodo < (SELECT numero_periodo FROM periodos WHERE id_periodo = ?) 
        ORDER BY numero_periodo DESC LIMIT 1
    ");
    $stmt->bind_param("ii", $id_trayecto, $id_periodo);
    $stmt->execute();
    $prev = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($prev){
        $prev_periodo = (int)$prev['id_periodo'];
        $prev_trayecto = (int)$prev['id_trayecto'];
    } else {
        // Si no hay periodos anteriores en el trayecto actual, retroceder al último periodo del trayecto anterior
        $stmt = $conn->prepare("
            SELECT t.id_trayecto, MAX(p.id_periodo) as ultimo_periodo
            FROM trayectos t 
            JOIN periodos p ON p.id_trayecto = t.id_trayecto
            WHERE t.id_carrera = ? AND t.numero_trayecto < (SELECT numero_trayecto FROM trayectos WHERE id_trayecto = ?)
            GROUP BY t.id_trayecto
            ORDER BY t.numero_trayecto DESC
            LIMIT 1
        ");
        $stmt->bind_param("ii", $id_carrera, $id_trayecto);
        $stmt->execute();
        $prev = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if(!$prev) throw new Exception("No hay periodo ni trayecto anterior disponible");
        $prev_trayecto = (int)$prev['id_trayecto'];
        $prev_periodo  = (int)$prev['ultimo_periodo'];
    }

    // 🔹 Actualizar grupo
    $stmt = $conn->prepare("UPDATE grupos SET id_periodo = ?, id_trayecto = ? WHERE id_grupo = ?");
    $stmt->bind_param("iii", $prev_periodo, $prev_trayecto, $id_grupo);
    if($stmt->execute()){
        echo json_encode(['success'=>true, 'prev_periodo'=>$prev_periodo, 'prev_trayecto'=>$prev_trayecto]);
    } else {
        echo json_encode(['success'=>false, 'error'=>$stmt->error]);
    }
    $stmt->close();

} catch(Exception $e){
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
?>
