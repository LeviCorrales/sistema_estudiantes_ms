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

    // 🔹 Buscar siguiente periodo
    $stmt = $conn->prepare("
        SELECT id_periodo, numero_periodo, id_trayecto 
        FROM periodos 
        WHERE id_trayecto = ? AND numero_periodo > (SELECT numero_periodo FROM periodos WHERE id_periodo = ?) 
        ORDER BY numero_periodo ASC LIMIT 1
    ");
    $stmt->bind_param("ii", $id_trayecto, $id_periodo);
    $stmt->execute();
    $next = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($next){
        $next_periodo = (int)$next['id_periodo'];
        $next_trayecto = (int)$next['id_trayecto'];
    } else {
        // Si no hay más periodos en el trayecto actual, avanzar al siguiente trayecto
        $stmt = $conn->prepare("
            SELECT t.id_trayecto, MIN(p.id_periodo) as primer_periodo
            FROM trayectos t 
            JOIN periodos p ON p.id_trayecto = t.id_trayecto
            WHERE t.id_carrera = ? AND t.numero_trayecto > (SELECT numero_trayecto FROM trayectos WHERE id_trayecto = ?)
            GROUP BY t.id_trayecto
            ORDER BY t.numero_trayecto ASC
            LIMIT 1
        ");
        $stmt->bind_param("ii", $id_carrera, $id_trayecto);
        $stmt->execute();
        $next = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if(!$next) throw new Exception("No hay siguiente periodo ni trayecto disponible");
        $next_trayecto = (int)$next['id_trayecto'];
        $next_periodo  = (int)$next['primer_periodo'];
    }

    // 🔹 Actualizar grupo
    $stmt = $conn->prepare("UPDATE grupos SET id_periodo = ?, id_trayecto = ? WHERE id_grupo = ?");
    $stmt->bind_param("iii", $next_periodo, $next_trayecto, $id_grupo);
    if($stmt->execute()){
        echo json_encode(['success'=>true, 'next_periodo'=>$next_periodo, 'next_trayecto'=>$next_trayecto]);
    } else {
        echo json_encode(['success'=>false, 'error'=>$stmt->error]);
    }
    $stmt->close();

} catch(Exception $e){
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
?>
