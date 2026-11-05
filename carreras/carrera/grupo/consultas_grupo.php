<?php
// logica_grupo.php

// LO SIENTO 😅
require_once '../../../config/conexion.php';

$id_grupo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_grupo <= 0) {
    die("Grupo no válido.");
}

// -----------------------------
// 1) Obtener datos base del grupo
// -----------------------------
$sql = "
    SELECT 
    g.id_grupo,
    g.nombre_grupo,
    g.id_carrera,
    c.nombre_carrera,
    g.id_trayecto,
    t.numero_trayecto,
    g.id_periodo,
    p.numero_periodo,
    gpf.fecha_inicio,  -- NUEVO: Leemos de la tabla intermedia
    gpf.fecha_fin,     -- NUEVO: Leemos de la tabla intermedia
    g.id_vocero,
    dp.cedula AS vocero_ci,
    dp.primer_nombre AS vocero_nombre,
    dp.primer_apellido AS vocero_apellido
FROM grupos g
INNER JOIN carreras c ON g.id_carrera = c.id_carrera
LEFT JOIN trayectos t ON g.id_trayecto = t.id_trayecto
LEFT JOIN periodos p ON g.id_periodo = p.id_periodo
LEFT JOIN datos_personales dp ON g.id_vocero = dp.id
-- NUEVO: Relacionamos las fechas específicas de ESTE grupo en ESTE periodo
LEFT JOIN grupo_periodo_fechas gpf ON g.id_grupo = gpf.id_grupo AND g.id_periodo = gpf.id_periodo
WHERE g.id_grupo = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare (grupo): " . $conn->error);
}
$stmt->bind_param("i", $id_grupo);
$stmt->execute();
$res = $stmt->get_result();
$grupo = $res->fetch_assoc();
$stmt->close();

if (!$grupo) {
    die("Grupo no encontrado.");
}

// Normalizar tipos
$grupo['id_trayecto'] = isset($grupo['id_trayecto']) ? (int)$grupo['id_trayecto'] : 0;
$grupo['id_periodo']  = isset($grupo['id_periodo'])  ? (int)$grupo['id_periodo']  : 0;
$grupo['numero_trayecto'] = $grupo['numero_trayecto'] ?? '';
$grupo['numero_periodo']  = $grupo['numero_periodo'] ?? '';
$grupo['fecha_inicio'] = $grupo['fecha_inicio'] ?? '';
$grupo['fecha_fin']    = $grupo['fecha_fin'] ?? '';

// -----------------------------
// 2) Actualizar fechas del grupo (POST)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin    = $_POST['fecha_fin'] ?? null;

    // Validar formato YYYY-MM-DD
    $inicioObj = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
    $finObj    = DateTime::createFromFormat('Y-m-d', $fecha_fin);

    if (!$inicioObj || !$finObj) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id_grupo&error=formato");
        exit;
    }

    // ✅ Única restricción lógica: Inicio debe ser menor que fin
    if ($inicioObj >= $finObj) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id_grupo&error=logica");
        exit;
    }

    // ✅ Actualizar en la BD (Tabla Intermedia)
    $stmt = $conn->prepare("
        INSERT INTO grupo_periodo_fechas (id_grupo, id_periodo, fecha_inicio, fecha_fin)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        fecha_inicio = VALUES(fecha_inicio), 
        fecha_fin = VALUES(fecha_fin)
    ");

    if ($stmt) {
        // Orden: id_grupo (int), id_periodo (int), fecha_inicio (string), fecha_fin (string)
        $stmt->bind_param("iiss", $id_grupo, $grupo['id_periodo'], $fecha_inicio, $fecha_fin);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id_grupo&success=1");
            exit;
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$id_grupo&error=db");
            exit;
        }
    }
}

// -----------------------------
// 3) Contar estudiantes
// -----------------------------
$total_estudiantes = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM grupo_estudiantes WHERE id_grupo = ?");
if ($stmt) {
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $total_estudiantes = isset($row['total']) ? (int)$row['total'] : 0;
    $stmt->close();
}

// -----------------------------
// 4) Obtener listado de estudiantes
// -----------------------------
$estudiantes = [];
$stmt = $conn->prepare("
    SELECT dp.id, dp.cedula, dp.primer_nombre, dp.primer_apellido
    FROM grupo_estudiantes ge
    INNER JOIN datos_personales dp ON ge.id_estudiante = dp.id
    WHERE ge.id_grupo = ?
    ORDER BY dp.primer_apellido, dp.primer_nombre
");
if ($stmt) {
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $estudiantes[] = $row;
    }
    $stmt->close();
}

// -----------------------------
// 5) Calcular siguiente periodo/trayecto (si aplica)
// -----------------------------
$next_periodo = null;
$grupo['next_trayecto'] = $grupo['id_trayecto'];

if (!empty($grupo['id_trayecto']) && $grupo['numero_periodo'] !== '') {
    $stmt = $conn->prepare("
        SELECT id_periodo, numero_periodo
        FROM periodos
        WHERE id_trayecto = ? AND numero_periodo > ?
        ORDER BY numero_periodo ASC
        LIMIT 1
    ");
    if ($stmt) {
        $numPeriodoActual = is_numeric($grupo['numero_periodo']) ? (int)$grupo['numero_periodo'] : 0;
        $stmt->bind_param("ii", $grupo['id_trayecto'], $numPeriodoActual);
        $stmt->execute();
        $next_periodo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

if (!$next_periodo) {
    // buscar siguiente trayecto dentro de la misma carrera
    if (!empty($grupo['id_carrera']) && $grupo['numero_trayecto'] !== '') {
        $stmt = $conn->prepare("
            SELECT id_trayecto, numero_trayecto
            FROM trayectos
            WHERE id_carrera = ? AND numero_trayecto > ?
            ORDER BY numero_trayecto ASC
            LIMIT 1
        ");
        if ($stmt) {
            $numTrayActual = is_numeric($grupo['numero_trayecto']) ? (int)$grupo['numero_trayecto'] : 0;
            $stmt->bind_param("ii", $grupo['id_carrera'], $numTrayActual);
            $stmt->execute();
            $next_trayecto = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($next_trayecto) {
                $stmt = $conn->prepare("
                    SELECT id_periodo, numero_periodo
                    FROM periodos
                    WHERE id_trayecto = ?
                    ORDER BY numero_periodo ASC
                    LIMIT 1
                ");
                if ($stmt) {
                    $stmt->bind_param("i", $next_trayecto['id_trayecto']);
                    $stmt->execute();
                    $first_period = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if ($first_period) {
                        $next_periodo = $first_period;
                        $grupo['next_trayecto'] = $next_trayecto['id_trayecto'];
                    }
                }
            }
        }
    }
}

// -----------------------------
// 6) Preparar infoGrupo para JS
// -----------------------------
$infoGrupo = [
    'nombre_carrera'   => $grupo['nombre_carrera'],
    'vocero_nombre'    => $grupo['vocero_nombre'],
    'vocero_apellido'  => $grupo['vocero_apellido'],
    'vocero_ci'        => $grupo['vocero_ci'],

    // Datos estáticos
    'estado'           => 'YARACUY',
    'municipio'        => 'NIRGUA',
    'aldea'            => '"SIMON BOLIVAR"',
    'seccion'          => 'FIN DE SEMANA',

    // Datos dinámicos de la BD
    'numero_trayecto'  => $grupo['numero_trayecto'] ?? 'I',
    'numero_periodo'   => $grupo['numero_periodo'] ?? 'N/A'
];

if ($next_periodo) {
    $infoGrupo['next_periodo'] = [
        'id_periodo'     => (int)$next_periodo['id_periodo'],
        'numero_periodo' => $next_periodo['numero_periodo']
    ];
}
?>