<?php
require_once '../../config/conexion.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos de la carrera
$stmt = $conn->prepare("SELECT id_carrera, nombre_carrera, estado, fecha_creacion FROM carreras WHERE id_carrera = ?");
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$c = $res->fetch_assoc();
$stmt->close();

if (!$c) {
    echo "Carrera no encontrada.";
    exit;
}

// Consultas estadísticas

// Total de estudiantes activos en esta carrera
$q1 = $conn->prepare("
    SELECT COUNT(DISTINCT dp.id) AS total
    FROM datos_personales dp
    INNER JOIN grupo_estudiantes ge ON ge.id_estudiante = dp.id
    INNER JOIN grupos g ON g.id_grupo = ge.id_grupo
    WHERE g.id_carrera = ? AND dp.estado = 'activo'
");
$q1->bind_param("i", $id);
$q1->execute();
$total_activos = $q1->get_result()->fetch_assoc()['total'] ?? 0;
$q1->close();

// Total de grupos de esta carrera
$q2 = $conn->prepare("SELECT COUNT(*) AS total FROM grupos WHERE id_carrera = ?");
$q2->bind_param("i", $id);
$q2->execute();
$total_grupos = $q2->get_result()->fetch_assoc()['total'] ?? 0;
$q2->close();

// Total de estudiantes egresados en esta carrera
$q3 = $conn->prepare("
    SELECT COUNT(DISTINCT dp.id) AS total
    FROM datos_personales dp
    INNER JOIN grupo_estudiantes ge ON ge.id_estudiante = dp.id
    INNER JOIN grupos g ON g.id_grupo = ge.id_grupo
    WHERE g.id_carrera = ? AND dp.estado = 'egresado'
");
$q3->bind_param("i", $id);
$q3->execute();
$total_egresados = $q3->get_result()->fetch_assoc()['total'] ?? 0;
$q3->close();

// Total de estudiantes retirados
$q4 = $conn->prepare("
    SELECT COUNT(DISTINCT dp.id) AS total
    FROM datos_personales dp
    INNER JOIN grupo_estudiantes ge ON ge.id_estudiante = dp.id
    INNER JOIN grupos g ON g.id_grupo = ge.id_grupo
    WHERE g.id_carrera = ? AND dp.estado = 'retirado'
");
$q4->bind_param("i", $id);
$q4->execute();
$total_retirados = $q4->get_result()->fetch_assoc()['total'] ?? 0;
$q4->close();

// Total de estudiantes congelados
$q5 = $conn->prepare("
    SELECT COUNT(DISTINCT dp.id) AS total
    FROM datos_personales dp
    INNER JOIN grupo_estudiantes ge ON ge.id_estudiante = dp.id
    INNER JOIN grupos g ON g.id_grupo = ge.id_grupo
    WHERE g.id_carrera = ? AND dp.estado = 'congelado'
");
$q5->bind_param("i", $id);
$q5->execute();
$total_congelados = $q5->get_result()->fetch_assoc()['total'] ?? 0;
$q5->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Carrera - <?=htmlspecialchars($c['nombre_carrera'])?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles_carrera.css">
    <link rel="stylesheet" type="text/css" href="../../styles_bar_nav.css">
    <link rel="stylesheet" href="../../normalize.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="body">

<script src="../../bar_nav.js"></script>
<div class="container-slidebar"></div>
    
<div class="main-content">
    <div class="container-topbar"></div>
        
    <div class="container">
        <a href="../index_carrera.php" class="muted"><i class="fa-solid fa-arrow-left"></i></a>
            
        <div class="header">
            <div class="title-section">
                <div class="career-info">
                    <h1><?=htmlspecialchars($c['nombre_carrera'])?></h1>
                    <p>Estado: <b><?=htmlspecialchars($c['estado'])?></b></p>
                    <p>Creada: <b><?=htmlspecialchars($c['fecha_creacion'])?></b></p>
                </div>
            </div>
        </div>

        <div class="stats-container">

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?=$total_activos?></h3>
                    <p>Total de Estudiantes Activos</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <h3><?=$total_grupos?></h3>
                    <p>Total de Grupos</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3><?=$total_egresados?></h3>
                    <p>Total de Egresados</p>
                </div>
            </div>

          
        </div>

        <div class="container-info-grupos">
                <div class="item-info-periodo">
                <h2>Total de Taryectos y periodos</h2>
                <div id="listaTrayectos">Cargando...</div>
                <button id="btnAbrirModal" class="btn">Definir trayectos y periodos</button>
            </div>
            <div class="item-info-grupo">
                <div class="item-info-grupo-title">
                    <h2>Grupos actuales</h2>
                    <button id="btnAbrirModalGrupo" class="btn">Crear Grupo</button>
                </div>

                <div class="filter-switch">
                    <span class="slider"></span>
                    <button class="btn-filtro active" data-estado="activo">Activos</button>
                    <button class="btn-filtro" data-estado="congelado">Congelados</button>
                    <button class="btn-filtro" data-estado="egresado">Egresados</button>
                </div>

                <div id="listaGrupos" class="listado-grupos">Cargando grupos...</div>


                <div id="mensajes-aqui"></div>
            </div>

            
        </div>

        <div class="item-info-materias">
            <h2>Pensum de Materias</h2>
            <div id="botonesTrayectos"></div>
            <div id="listaMaterias">Cargando materias...</div>
        </div>

<!-- Modal para crear materia -->
        <section class="container-form-materias">
            <div class="h2-grupo-inicial">
                <h2>Agregar Materia al Pensum</h2>
                <div class="plus-icon">
                    <div class="bar horizontal"></div>
                    <div class="bar vertical"></div>
                </div>
            </div>

            <form id="formCrearMateria" class="form-Crear-Materia">
                <div class="item-form-Crear-Materia">
                    <div>
                        <label>Trayecto:</label>
                        <select id="trayectoMateria" required></select>
                    </div>
                    <div>
                        <label>Periodo:</label>
                        <select id="periodoMateria" required></select>
                    </div>
                    <div>
                        <label>Nombre</label>
                        <input id="nombreMateria" required placeholder="Ej: Matemáticas Aplicadas" />
                    </div>
                    <div>
                        <label>Código (opcional):</label>
                        <input id="codigoMateria" placeholder="Ej: MAT101" />
                    </div>
                    <div>
                        <label>Créditos:</label>
                        <input type="number" id="creditosMateria" min="0" value="0" />
                    </div>
                    <div>
                        <label>Horas Semanales:</label>
                        <input type="number" id="horasMateria" min="0" value="0" />
                    </div>
                </div>

                <div class="actions-materia">
                    <button type="submit" class="btn-guardar-grupos-ini">Guardar</button>
                </div>
            </form>
        </section>

    </div> 
    <script>
        let formInicialOpen = false
        const btn_abrir_form_materias = document.querySelector(".plus-icon")
        const btn_guardar_materias = document.querySelector(".btn-guardar-grupos-ini")
        const form_crear_materias = document.getElementById("formCrearMateria")

        btn_abrir_form_materias.addEventListener("click", () => {
            if (formInicialOpen == false) {
                form_crear_materias.classList.add("active-form-Crear-Materia")
                btn_abrir_form_materias.classList.add("active-plus")
                formInicialOpen = true
            } else {
                form_crear_materias.classList.remove("active-form-Crear-Materia")
                btn_abrir_form_materias.classList.remove("active-plus")
                formInicialOpen = false
            }
        })

        btn_guardar_materias.addEventListener("click", () => {
            form_crear_materias.classList.remove("active-form-Crear-Materia")
            btn_abrir_form_materias.classList.remove("active-plus")
            formInicialOpen = false
        })
    </script>

    <script src="materias_pensum.js"></script> 

    <!-- MODAL PARA DEFINIR TRAYECTOS Y PERIODOS -->
    <div class="modal-overlay" id="modal-overlay"></div>
    
    <div id="modalTrayectos" class="modal">
        <div class="modal-content">
            <h2>Definir Trayectos y Periodos</h2>
            <form id="formTrayectos">
                <label for="numTrayectos">Cantidad de trayectos</label>
                <input type="number" id="numTrayectos" min="1" required>
                <button type="button" id="btnGenerar" class="btn secondary" style="margin: 10px 0;">Generar campos</button>

                <div id="trayectosContainer"></div>

                <div class="actions">
                    <button type="button" id="btnCerrarModal" class="btn secondary"><i class="fa-solid fa-xmark"></i></button>
                    <p class="advertencia">Esta acción es definitiva. Una vez guardado, no podrás modificar los trayectos ni los periodos.</p>
                    <button type="submit" class="btn primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PARA DEFINIR GRUPOS -->
    <div id="modalGrupo" class="modal">
        <div class="modal-content">
            <h2>Crear Grupo</h2>
            <form id="formCrearGrupo">
                <label for="nombre_grupo">Nombre del grupo</label>
                <input id="nombre_grupo" name="nombre_grupo" required maxlength="200" placeholder="Ej: Grupo A" />
                <div id="formErrorGrupo" class="error" style="display:none"></div>
                <div class="actions">
                    <button type="button" id="btnCerrarModalGrupo" class="btn secondary"><i class="fa-solid fa-xmark"></i></button>
                    <button type="submit" class="btn primary">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div> 
  
    
    <script>
    // Creamos un array con los IDs de tus inputs
    const inputsNombres = [
        'nombre_grupo',
        'nombreMateria'
    ];

    inputsNombres.forEach(id => {
        const input = document.getElementById(id);

        if (input) {
            input.addEventListener('input', (e) => {
                // 1. Guardamos la posición del cursor para que no salte al final
                let cursorPosition = e.target.selectionStart;

                // 2. Transformamos: primera letra y letra tras espacio a Mayúscula
                const transformedValue = e.target.value.replace(/(^\w|\s\w)/g, (match) => {
                    return match.toUpperCase();
                });

                // 3. Asignamos el nuevo valor
                e.target.value = transformedValue;

                // 4. Restauramos la posición del cursor
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
        }
    });
</script>

<script>
    // Pasar el id de carrera a JS para usarlo luego
    const carreraId = <?= json_encode($c['id_carrera']) ?>;
    window.carreraId = carreraId; // Para usar en crear_grupos.js
</script>

<script src="../../script_bar_nav.js"></script>
<script src="crear_trayectos.js"></script>
<script src="crear_grupos.js"></script>
<script src="menu_contextual_grupos.js"></script>
<script src="../../mensajes.js"></script>
</body>
</html>