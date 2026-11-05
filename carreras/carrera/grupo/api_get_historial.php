<?php
// Desactivar la visualización de errores en el output para no romper el JSON
error_reporting(0); 
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/conexion.php';

// Limpiar cualquier salida accidental previa (espacios en blanco, avisos)
ob_start();

$id_grupo    = intval($_GET['id_grupo']    ?? 0);
$id_trayecto = intval($_GET['id_trayecto'] ?? 0);
$id_periodo  = intval($_GET['id_periodo']  ?? 0);

try {
    if ($id_grupo <= 0) throw new Exception("ID de grupo no válido.");

    // 1. Obtener id_carrera (Validando que el grupo exista)
    $stmt = $conn->prepare("SELECT id_carrera FROM grupos WHERE id_grupo = ?");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    
    if (!$res) throw new Exception("El grupo no existe en la base de datos.");
    $id_carrera = intval($res['id_carrera']);

    // 2. Obtener trayectos
    $stmt = $conn->prepare("SELECT id_trayecto, numero_trayecto FROM trayectos WHERE id_carrera = ? ORDER BY numero_trayecto");
    $stmt->bind_param("i", $id_carrera);
    $stmt->execute();
    $trayectos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // 3. Obtener periodos (MODIFICADO PARA LEER DE grupo_periodo_fechas)
    $periodos = [];
    $datosPeriodo = null;
    if ($id_trayecto > 0) {
        $stmt = $conn->prepare("
            SELECT p.id_periodo, p.numero_periodo, gpf.fecha_inicio, gpf.fecha_fin 
            FROM periodos p
            LEFT JOIN grupo_periodo_fechas gpf ON p.id_periodo = gpf.id_periodo AND gpf.id_grupo = ?
            WHERE p.id_trayecto = ? 
            ORDER BY p.numero_periodo
        ");
        // Ahora pasamos el id_grupo y el id_trayecto ("ii")
        $stmt->bind_param("ii", $id_grupo, $id_trayecto);
        $stmt->execute();
        $periodos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($periodos as $p) {
            if (intval($p['id_periodo']) === $id_periodo) {
                $datosPeriodo = $p;
                break;
            }
        }
    }

    // 4. Obtener materias (Pensum actual + Historial)
    $materias = [];
    if ($id_trayecto > 0 && $id_periodo > 0) {
        $stmt = $conn->prepare("
            /* PARTE A: Materias del pensum que NO han sido eliminadas */
            SELECT DISTINCT m.id_materia, m.nombre_materia
            FROM materias_carrera m
            WHERE m.id_carrera = ? 
              AND m.id_trayecto = ? 
              AND m.id_periodo = ?
              AND m.estado = 'activa'  /* <--- ESTO EVITA QUE SE VEA ANTES DE CONSOLIDAR */
            
            UNION
            
            /* PARTE B: Materias que ya fueron consolidadas en el pasado */
            SELECT DISTINCT h.id_materia, h.nombre_materia_snapshot AS nombre_materia
            FROM historial_grupo_materia h
            WHERE h.id_grupo = ? 
              AND h.id_trayecto = ? 
              AND h.id_periodo = ? 
              AND h.nombre_materia_snapshot IS NOT NULL
        ");
        $stmt->bind_param("iiiiii", $id_carrera, $id_trayecto, $id_periodo, $id_grupo, $id_trayecto, $id_periodo);
        $stmt->execute();
        $materias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 5. Historial
    $historial = [];
    if ($id_periodo > 0) {
        $stmt = $conn->prepare("
            SELECT h.id_historial, h.id_estudiante, h.id_materia, h.profesor, h.nota, h.consolidado,
                   COALESCE(h.nombre_materia_snapshot, m.nombre_materia, 'Materia no existente') AS nombre_materia_display
            FROM historial_grupo_materia h
            LEFT JOIN materias_carrera m ON h.id_materia = m.id_materia
            WHERE h.id_grupo = ? AND h.id_trayecto = ? AND h.id_periodo = ?
        ");
        $stmt->bind_param("iii", $id_grupo, $id_trayecto, $id_periodo);
        $stmt->execute();
        $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Limpiar el búfer y enviar solo el JSON
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'trayectos' => $trayectos,
        'periodos' => $periodos,
        'materias' => $materias,
        'historial' => $historial,
        'datosPeriodo' => $datosPeriodo
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}