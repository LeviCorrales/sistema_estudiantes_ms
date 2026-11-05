<?php
header('Content-Type: application/json');
include '../config/conexion.php'; // tu archivo de conexión

// ------------------- RECIBIR DATOS -------------------
$grupos = $_POST['grupos'] ?? [];

if (empty($grupos)) {
    echo json_encode(['success' => false, 'msg' => 'No se enviaron grupos.']);
    exit;
}

// ------------------- INSERTAR GRUPOS -------------------
$sql  = "INSERT INTO trayecto_inicial (nombre_grupo_inicial) VALUES (?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'msg'     => 'Error preparando la consulta: ' . $conn->error
    ]);
    exit;
}

// Procesamiento del array de grupos
foreach ($grupos as $nombre) {
    $nombre = trim($nombre);
    
    if ($nombre !== '') {
        $stmt->bind_param("s", $nombre);
        
        if (!$stmt->execute()) {
            echo json_encode([
                'success' => false, 
                'msg'     => 'Error insertando grupo: ' . $stmt->error
            ]);
            
            $stmt->close();
            $conn->close();
            exit;
        }
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true, 
    'msg'     => 'Grupos iniciales creados correctamente.'
]);
?>