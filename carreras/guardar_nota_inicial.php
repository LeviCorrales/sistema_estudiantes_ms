<?php
include '../config/conexion.php';

// Limpiar cualquier salida previa para evitar errores de JSON
ob_clean(); 
header('Content-Type: application/json');

// ------------------- RECIBIR PARÁMETROS -------------------
$idTrayecto = $_POST['id_trayecto_inicial'] ?? null;
$idGrupoEstudiante = $_POST['id_grupo_estudiante'] ?? null;
$idEstudiante = $_POST['id_estudiante'] ?? null;
$idMateria = $_POST['id_materia'] ?? null;
$notaRaw = $_POST['nota'] ?? null;

// Normalizar id_grupo_estudiante: Si es vacío, '0', o 'null' string, convertir a NULL real
if (empty($idGrupoEstudiante) || $idGrupoEstudiante === '0' || $idGrupoEstudiante === 'null') {
    $idGrupoEstudiante = null;
}

// ------------------- VALIDACIÓN CRÍTICA -------------------
// Verificamos que los datos esenciales de identificación existan (quitamos la nota de aquí)
if (!$idTrayecto || !$idEstudiante || !$idMateria) {
    echo json_encode(['success' => false, 'msg' => 'Datos incompletos para procesar el registro']);
    exit;
}

// ------------------- NORMALIZACIÓN DE LA NOTA -------------------
// Si la nota viene vacía o no fue enviada, asignamos un NULL real de PHP
if ($notaRaw === null || $notaRaw === '') {
    $nota = null;
} else {
    // Si contiene valor, lo redondeamos a entero (Sistema Misión Sucre 1-20)
    $nota = round(floatval($notaRaw));

    if ($nota < 0 || $nota > 20) {
        echo json_encode(['success' => false, 'msg' => 'La nota debe estar entre 0 y 20']);
        exit;
    }
}

// ------------------- PROCESAR CON UPSERT (INSERT O UPDATE) -------------------
/**
 * Usamos ON DUPLICATE KEY UPDATE basado en el UNIQUE KEY (id_estudiante, id_materia_inicial).
 * Al pasar la variable $nota (que puede ser un entero o null), mysqli se encarga
 * de enviar el marcador NULL nativo de forma segura a MySQL.
 */
$sql = "INSERT INTO historial_academico_inicial 
            (id_trayecto_inicial, id_grupo_estudiante, id_estudiante, id_materia_inicial, nota)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            nota = VALUES(nota),
            id_grupo_estudiante = VALUES(id_grupo_estudiante),
            id_trayecto_inicial = VALUES(id_trayecto_inicial)";

try {
    $stmt = $conn->prepare($sql);
    
    // Vinculamos los parámetros. 
    // Aunque se defina 'i' (entero), si la variable de PHP es null, el driver de mysqli guarda un NULL real.
    $stmt->bind_param("iiiii", $idTrayecto, $idGrupoEstudiante, $idEstudiante, $idMateria, $nota);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'nota_guardada' => $nota]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Error de ejecución: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => 'Error de servidor: ' . $e->getMessage()]);
}

$conn->close();