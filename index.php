<?php
require_once __DIR__ . '/config/seguridad.php';
include __DIR__ . '/config/conexion.php';


// Consulta exhaustiva de todas las tablas académicas y personales
$query = "SELECT MAX(fecha) AS ultima_actualizacion FROM (
            SELECT MAX(fecha_registro) AS fecha FROM datos_personales
            UNION
            SELECT MAX(fecha_creacion) AS fecha FROM carreras
            UNION
            SELECT MAX(fecha_creacion) AS fecha FROM trayectos
            UNION
            SELECT MAX(fecha_creacion) AS fecha FROM periodos
            UNION
            SELECT MAX(fecha_creacion) AS fecha FROM grupos
            UNION
            SELECT MAX(fecha_asignacion) AS fecha FROM grupo_estudiantes
            UNION
            SELECT MAX(fecha_creacion) AS fecha FROM materias_carrera
            UNION
            SELECT MAX(fecha_actualizacion) AS fecha FROM historial_grupo_materia
            UNION
            SELECT MAX(fecha_creacion) AS fecha FROM trayecto_inicial
            UNION
            SELECT MAX(fecha_actualizacion) AS fecha FROM historial_academico_inicial
            UNION
            SELECT MAX(fecha_asignacion) AS fecha FROM grupo_inicial_materias
          ) AS gran_historial";

// Recuerda usar $conn que es el nombre en tu conexion.php
$resultado = mysqli_query($conn, $query);

if ($resultado) {
    $fila = mysqli_fetch_assoc($resultado);
    if ($fila['ultima_actualizacion']) {
        $timestamp = strtotime($fila['ultima_actualizacion']);
        $ultima = date("d/m/Y h:i A", $timestamp);
    } else {
        $ultima = "Sin actividad reciente";
    }
} else {
    $ultima = "Error de sincronización";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Estudiantil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="styles_bar_nav.css">
    <link rel="stylesheet" type="text/css" href="styles_index.css">
</head>

<body>
    <script src="bar_nav.js"></script>

    <div class="container-slidebar">
        </div>

    <div class="main-content">

        <div class="container-topbar">
            </div>

        <div class="content">
            <div class="container">
                <header>
                    <h1><i class="fas fa-tachometer-alt"></i> Panel de Control</h1>
                    <p>Bienvenido al sistema de gestión estudiantil de la Aldea Uiversitaria Simón Bolívar.</p>

                    <a class="container-instrucciones" href="instrucciones/instrucciones.php"><div>Instrucciones clave</div><i class="fa-solid fa-plus"></i></a>
                    
                </header>

                <div class="quick-access">
                    <h2><i class="fas fa-bolt"></i> Accesos Directos</h2>
                    <div class="shortcuts-grid">
                        <a href="formulario_estudiantes/index_form.php" class="shortcut-card">
                            <div class="shortcut-icon"><i class="fas fa-user-plus"></i></div>
                            <div class="shortcut-title">Agregar Estudiante</div>
                            <div class="shortcut-desc">Registrar nuevo estudiante</div>
                        </a>

                        <a href="listado/listado.php" class="shortcut-card">
                            <div class="shortcut-icon"><i class="fas fa-search"></i></div>
                            <div class="shortcut-title">Buscar Estudiante</div>
                            <div class="shortcut-desc">Listado con filtros de busqueda</div>
                        </a>

                        <a href="carreras/index_carrera.php" class="shortcut-card">
                            <div class="shortcut-icon"><i class="fas fa-book"></i></div>
                            <div class="shortcut-title">Gestionar Carreras</div>
                            <div class="shortcut-desc">Administrar programas</div>
                        </a>

                        <a href="respaldo/respaldo.php" class="shortcut-card">
                            <div class="shortcut-icon"><i class="fa-solid fa-server"></i></div>
                            <div class="shortcut-title">Respaldar</div>
                            <div class="shortcut-desc">Subir a la nube</div>
                        </a>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div>
                                <div class="stat-title">Total de Estudiantes</div>
                                <div class="stat-description">Registrados en el sistema</div>
                            </div>
                        </div>
                        <div class="stat-value">
                            <p id="total-estudiantes">--</p>
                        </div>
                       
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                            <div>
                                <div class="stat-title">Estudiantes Recientes</div>
                                <div class="stat-description">Últimos 30 días</div>
                            </div>
                        </div>
                        <div class="stat-value">
                            <p id="total-recientes">--</p>
                        </div>
                        <div class="recent-students" id="lista-recientes">
                            </div>
                    </div>
                </div>

                <div class="stats-grid stats-grid-2">
                    <div class="stat-card stat-card-2">
                        <div class="stat-header">
                            <div class="stat-icon"><i class="fas fa-book"></i></div>
                            <div>
                                <div class="stat-title">Distribución por Carrera</div>
                                <div class="stat-description">Estudiantes por programa</div>
                            </div>
                        </div>
                        <div class="chart-container" id="chart-carreras">
                            </div>
                    </div>
                </div>

           <div class="last-update">
                <i class="fas fa-sync-alt"></i>
                Última actualización: <?= $ultima ?>
            </div>
            




            </div> 
            <div class="container-btn-cuenta">
                <a href="/sistema_gestion_aldea/logout.php" class="btn-cuenta cerrar-sesion">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Cerrar sesión</span>
            </a>
             <a href="/sistema_gestion_aldea/seguridad_cuenta.php" class="btn-cuenta cambiar-clave">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Cambiar clave</span>
            </a>
          
            </div>
            
        </div> 
        
    </div> 
    <script>
        async function cargarStatsCarrera() {
            const container = document.getElementById('chart-carreras');
            container.innerHTML = ''; 

            try {
                const res = await fetch('api_estudiantes_por_carrera.php');
                const data = await res.json();

                if (!data.success) {
                    container.innerHTML = '<p>Error cargando estadísticas</p>';
                    return;
                }

                // Encontrar el máximo para calcular proporciones
                const max = Math.max(...data.datos.map(d => d.cantidad));

                data.datos.forEach(d => {
                    const bar = document.createElement('div');
                    bar.className = 'chart-bar';
                    const heightPercent = max ? (d.cantidad / max) * 100 : 0;
                    bar.style.height = heightPercent + '%';
                    bar.innerHTML = `
                        <div class="chart-bar-value">${d.cantidad}</div>
                        <div class="chart-bar-label">${d.nombre}</div>
                    `;
                    container.appendChild(bar);
                });

            } catch (err) {
                console.error(err);
                container.innerHTML = '<p>Error cargando estadísticas</p>';
            }
        }

        // Ejecutar al cargar
        cargarStatsCarrera();
    </script>

    <script src="index.js"></script>
    <script src="script_bar_nav.js"></script>
</body>

</html>