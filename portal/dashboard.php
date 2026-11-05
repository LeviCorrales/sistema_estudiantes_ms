<?php
session_start();
require_once 'config/db.php';

// Control de acceso: Verificar si el estudiante inició sesión
if (!isset($_SESSION['estudiante_id'])) {
    header('Location: login.php?error=3');
    exit;
}

$id_estudiante = $_SESSION['estudiante_id'];

try {
    // 1. Consulta de Datos Personales y Dirección
    $stmtPersona = $pdo->prepare("
        SELECT dp.*, 
               e.nombre AS estado_nombre, 
               m.nombre AS municipio_nombre, 
               p.nombre AS parroquia_nombre,
               dr.avenida, dr.calle_sector, dr.casa_apartamento, dr.punto_referencia
        FROM datos_personales dp
        LEFT JOIN direccion_residencia dr ON dp.id = dr.id_persona
        LEFT JOIN estados e ON dr.id_estado = e.id
        LEFT JOIN municipios m ON dr.id_municipio = m.id
        LEFT JOIN parroquias p ON dr.id_parroquia = p.id
        WHERE dp.id = :id
    ");
    $stmtPersona->execute([':id' => $id_estudiante]);
    $persona = $stmtPersona->fetch();

    if (!$persona) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    // 2. Consulta de Datos Socioeconómicos
    $stmtSocio = $pdo->prepare("
        SELECT * 
        FROM datos_socioeconomicos 
        WHERE id_persona = :id
    ");
    $stmtSocio->execute([':id' => $id_estudiante]);
    $socioeconomicos = $stmtSocio->fetch();

    // 3. Consulta de Historial: Trayecto Inicial
    $stmtInicial = $pdo->prepare("
        SELECT hai.*, mpi.nombre_materia, mpi.codigo_materia
        FROM historial_academico_inicial hai
        INNER JOIN materias_pensum_inicial mpi ON hai.id_materia_inicial = mpi.id_materia_inicial
        WHERE hai.id_estudiante = :id
        ORDER BY mpi.nombre_materia ASC
    ");
    $stmtInicial->execute([':id' => $id_estudiante]);
    $historialInicial = $stmtInicial->fetchAll();

    // 4. Consulta de Historial: Carrera Regular
    $stmtCarrera = $pdo->prepare("
        SELECT hgm.*, 
               COALESCE(hgm.nombre_materia_snapshot, mc.nombre_materia) AS nombre_materia,
               COALESCE(hgm.codigo_materia_snapshot, mc.codigo_materia) AS codigo_materia,
               c.nombre_carrera,
               t.numero_trayecto,
               p.numero_periodo,
               g.nombre_grupo
        FROM historial_grupo_materia hgm
        LEFT JOIN materias_carrera mc ON hgm.id_materia = mc.id_materia
        LEFT JOIN carreras c ON hgm.id_carrera = c.id_carrera
        LEFT JOIN trayectos t ON hgm.id_trayecto = t.id_trayecto
        LEFT JOIN periodos p ON hgm.id_periodo = p.id_periodo
        LEFT JOIN grupos g ON hgm.id_grupo = g.id_grupo
        WHERE hgm.id_estudiante = :id
        ORDER BY t.numero_trayecto ASC, p.numero_periodo ASC, mc.nombre_materia ASC
    ");
    $stmtCarrera->execute([':id' => $id_estudiante]);
    $historialCarrera = $stmtCarrera->fetchAll();

} catch (PDOException $e) {
    die("Error al cargar datos del expediente: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente Estudiantil - <?= htmlspecialchars($persona['primer_nombre'] . ' ' . $persona['primer_apellido']) ?></title>
    <link rel="stylesheet" type="text/css" href="dashboard_estilos.css">
</head>
<body>

<header>
    <h1>Portal de Consulta Estudiantil</h1>

    <div class="user-info">
        <span><b><?= htmlspecialchars($_SESSION['nombre_completo']) ?></b></span>
        <div class="btns-header">
            <a href="cambiar_clave.php" class="btn-clave" title="Cambiar Clave">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span class="btn-text">Cambiar Clave</span>
            </a>

            <a href="logout.php" class="btn-logout" title="Cerrar Sesión">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="btn-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
</header>

<div class="container">

    <!-- Navegación por pestañas -->
    <div class="container-tab">
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('personal')" title="Datos Personales">
                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span class="tab-text">Datos Personales</span>
            </button>

            <button class="tab-btn" onclick="showTab('inicial')" title="Trayecto Inicial">
                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
                <span class="tab-text">Trayecto Inicial</span>
            </button>

            <button class="tab-btn" onclick="showTab('carrera')" title="Récord de Carrera">
                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                <span class="tab-text">Récord de Carrera</span>
            </button>
        </div>
    </div>

    <!-- PESTAÑA 1: DATOS PERSONALES, DIRECCIÓN Y SOCIOECONÓMICOS -->
    <div id="personal" class="tab-content active">
        <div class="card">
            <h2>Información Personal</h2>
            <div class="grid-2">
                <div class="data-item"><label>Cédula de Identidad</label><span><?= htmlspecialchars($persona['cedula']) ?></span></div>
                <div class="data-item"><label>Nombres Completos</label><span><?= htmlspecialchars($persona['primer_nombre'] . ' ' . $persona['segundo_nombre']) ?></span></div>
                <div class="data-item"><label>Apellidos Completos</label><span><?= htmlspecialchars($persona['primer_apellido'] . ' ' . $persona['segundo_apellido']) ?></span></div>
                <div class="data-item"><label>Sexo / Estado Civil</label><span><?= $persona['sexo'] ?> - <?= $persona['estado_civil'] ?></span></div>
                <div class="data-item"><label>Fecha de Nacimiento</label><span><?= date('d/m/Y', strtotime($persona['fecha_nacimiento'])) ?></span></div>
                <div class="data-item"><label>Correo Electrónico</label><span><?= htmlspecialchars($persona['correo_electronico'] ?? 'No registrado') ?></span></div>
                <div class="data-item"><label>Teléfono Celular</label><span><?= htmlspecialchars($persona['telefono_celular'] ?? 'N/A') ?></span></div>
                <div class="data-item"><label>Teléfono Local</label><span><?= htmlspecialchars($persona['telefono_local'] ?? 'N/A') ?></span></div>
            </div>
        </div>

        <div class="card">
            <h2>Dirección de Residencia</h2>
            <div class="grid-2">
                <div class="data-item"><label>Estado</label><span><?= htmlspecialchars($persona['estado_nombre'] ?? 'No registrado') ?></span></div>
                <div class="data-item"><label>Municipio</label><span><?= htmlspecialchars($persona['municipio_nombre'] ?? 'No registrado') ?></span></div>
                <div class="data-item"><label>Parroquia</label><span><?= htmlspecialchars($persona['parroquia_nombre'] ?? 'No registrado') ?></span></div>
                <div class="data-item"><label>Avenida / Calle</label><span><?= htmlspecialchars(($persona['avenida'] ? $persona['avenida'] . ', ' : '') . $persona['calle_sector']) ?></span></div>
                <div class="data-item"><label>Casa / Apto.</label><span><?= htmlspecialchars($persona['casa_apartamento'] ?? 'N/A') ?></span></div>
                <div class="data-item"><label>Punto de Referencia</label><span><?= htmlspecialchars($persona['punto_referencia'] ?? 'N/A') ?></span></div>
            </div>
        </div>

        <!-- NUEVA CARD: DATOS SOCIOECONÓMICOS -->
        <div class="card">
            <h2>Información Socioeconómica</h2>
            <div class="grid-2">
                <div class="data-item">
                    <label>Grupo Familiar</label>
                    <span><?= htmlspecialchars($socioeconomicos['grupo_familiar'] ?? 'N/A') ?> personas</span>
                </div>
                <div class="data-item">
                    <label>Personas a Cargo</label>
                    <span><?= htmlspecialchars($socioeconomicos['personas_a_cargo'] ?? 'N/A') ?></span>
                </div>
                <div class="data-item">
                    <label>Fuente de Ingresos</label>
                    <span><?= htmlspecialchars($socioeconomicos['fuente_ingresos'] ?? 'No registrado') ?></span>
                </div>
                <div class="data-item">
                    <label>Ingreso Mensual</label>
                    <span><?= isset($socioeconomicos['ingreso_mensual']) ? number_format($socioeconomicos['ingreso_mensual'], 2, ',', '.') . ' Bs.' : 'No registrado' ?></span>
                </div>
                <div class="data-item">
                    <label>Tipo de Vivienda</label>
                    <span><?= htmlspecialchars($socioeconomicos['tipo_vivienda'] ?? 'No registrado') ?></span>
                </div>
                <div class="data-item">
                    <label>Tenencia de Vivienda</label>
                    <span><?= htmlspecialchars($socioeconomicos['tenencia_vivienda'] ?? 'No registrado') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: TRAYECTO INICIAL -->
    <div id="inicial" class="tab-content">
        <div class="card">
            <h2>Asignaturas - Trayecto Inicial</h2>
            <?php if (empty($historialInicial)): ?>
                <p>No hay registros asignados para el Trayecto Inicial.</p>
            <?php else: ?>
                <div class="container-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Materia</th>
                                <th>Nota Final</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historialInicial as $item): ?>
                                <tr>
                                    <td><b><?= htmlspecialchars($item['codigo_materia'] ?? 'S/C') ?></b></td>
                                    <td><?= htmlspecialchars($item['nombre_materia']) ?></td>
                                    <td><?= $item['nota'] !== null ? htmlspecialchars($item['nota']) : '--' ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($item['estado']) ?>">
                                            <?= htmlspecialchars($item['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

   <!-- PESTAÑA 3: RÉCORD DE CARRERA -->
<div id="carrera" class="tab-content">
    <div class="card">
        <h2>Historial Académico por Trayecto y Periodo</h2>
        <?php if (empty($historialCarrera)): ?>
            <p>No tienes materias registradas en la carrera regular actualmente.</p>
        <?php else: ?>

            <?php
            // Extracto dinámico de los periodos únicos registrados
            $periodosUnicos = [];
            foreach ($historialCarrera as $item) {
                $clave = 'T' . $item['numero_trayecto'] . '-P' . $item['numero_periodo'];
                if (!isset($periodosUnicos[$clave])) {
                    $periodosUnicos[$clave] = [
                        'trayecto' => $item['numero_trayecto'],
                        'periodo'  => $item['numero_periodo'],
                        'label'    => 'Trayecto ' . $item['numero_trayecto'] . ' - Periodo ' . $item['numero_periodo']
                    ];
                }
            }
            ?>

            <!-- Botones de Filtro de Periodos -->
            <div class="period-filters">
                <button class="btn-filter active" onclick="filterPeriod('all', this)">
                    Todos los Periodos
                </button>
                <?php foreach ($periodosUnicos as $clave => $p): ?>
                    <button class="btn-filter" onclick="filterPeriod('<?= $clave ?>', this)">
                        <?= htmlspecialchars($p['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="container-table">
                <table id="tabla-carrera">
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th>Tray./Per.</th>
                            <th>Materia</th>
                            <th>Profesor</th>
                            <th>Nota</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historialCarrera as $item): 
                            $clavePeriodo = 'T' . $item['numero_trayecto'] . '-P' . $item['numero_periodo'];
                        ?>
                            <tr data-periodo="<?= $clavePeriodo ?>">
                                <td><?= htmlspecialchars($item['nombre_carrera'] ?? 'N/A') ?></td>
                                <td>T<?= $item['numero_trayecto'] ?> - P<?= $item['numero_periodo'] ?></td>
                                <td>
                                    
                                    <b><?= htmlspecialchars($item['nombre_materia']) ?></b>
                                </td>
                                <td><?= htmlspecialchars($item['profesor'] ?? 'Sin asignar') ?></td>
                                <td><b><?= $item['nota'] !== null ? htmlspecialchars($item['nota']) : '--' ?></b></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($item['estado']) ?>">
                                        <?= htmlspecialchars($item['estado']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function filterPeriod(periodKey, btnElement) {
    // 1. Cambiar el estado activo de los botones de filtro
    document.querySelectorAll('.btn-filter').forEach(btn => {
        btn.classList.remove('active');
    });
    btnElement.classList.add('active');

    // 2. Ocultar o mostrar filas de la tabla según el filtro
    const rows = document.querySelectorAll('#tabla-carrera tbody tr');
    rows.forEach(row => {
        if (periodKey === 'all' || row.getAttribute('data-periodo') === periodKey) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

</body>
</html>