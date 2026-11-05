<?php
require_once '../config/conexion.php';

// ------------------- CONSULTA DE CARRERAS Y ESTADÍSTICAS -------------------
$sql = "
    SELECT 
        c.id_carrera,
        c.nombre_carrera,
        c.estado,
        -- Total de grupos por carrera
        (
            SELECT COUNT(*) 
            FROM grupos g 
            WHERE g.id_carrera = c.id_carrera
        ) AS total_grupos,
        -- Total de trayectos por carrera
        (
            SELECT COUNT(*) 
            FROM trayectos t 
            WHERE t.id_carrera = c.id_carrera
        ) AS total_trayectos,
        -- Total de estudiantes únicos por carrera
        (
            SELECT COUNT(DISTINCT ge.id_estudiante)
            FROM grupo_estudiantes ge
            JOIN grupos g ON ge.id_grupo = g.id_grupo
            WHERE g.id_carrera = c.id_carrera
        ) AS total_estudiantes
    FROM carreras c
    WHERE c.estado != 'eliminada' -- <--- CAMBIO: Excluimos las carreras borradas lógicamente
    ORDER BY c.fecha_creacion DESC
";

$res      = $conn->query($sql);
$carreras = $res->fetch_all(MYSQLI_ASSOC);


// ------------------- TOTALES GLOBALES (DASHBOARD) -------------------

// Total Carreras Activas (Solo las que están operativas actualmente)
$total_carreras = $conn->query("SELECT COUNT(*) AS total FROM carreras WHERE estado = 'activa'")
                       ->fetch_assoc()['total'];

// Listado de carreras para selects o formularios (Ya filtraba correctamente activas e inhabilitadas)
$carreras_lista = $conn->query("SELECT * FROM carreras WHERE estado IN ('activa', 'inhabilitada')");

// Total Estudiantes Activos
$total_estudiantes = $conn->query("SELECT COUNT(*) AS total FROM datos_personales WHERE estado = 'activo'")
                              ->fetch_assoc()['total'];

// Total Egresados
$total_egresados = $conn->query("SELECT COUNT(*) AS total FROM datos_personales WHERE estado = 'egresado'")
                             ->fetch_assoc()['total'];

// Total Materias (Optimizado para NO contar materias de carreras eliminadas)
// Nota: Si usas la estructura con id_carrera directo en la tabla:
$total_materias = $conn->query("
    SELECT COUNT(*) AS total 
    FROM materias_carrera m
    JOIN carreras c ON m.id_carrera = c.id_carrera 
    WHERE c.estado != 'eliminada'
")->fetch_assoc()['total'];


// ------------------- TRAYECTOS INICIALES (PIU) -------------------
// Forzamos el filtro para que NO traiga los que tengan estado 'eliminada'
$sql_trayectos = "SELECT * FROM trayecto_inicial WHERE estado != 'eliminada' ORDER BY fecha_creacion DESC";
$res_trayectos = $conn->query($sql_trayectos);

// Guardamos el resultado en $trayectos para que coincida con tu foreach del HTML
$trayectos     = $res_trayectos->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión - Carreras</title>
    <link rel="stylesheet" href="style_carreras.css">
    <link rel="stylesheet" type="text/css" href="../styles_bar_nav.css">
    <link rel="stylesheet" href="../normalize.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="body">
<script src="../bar_nav.js"></script>
<div class="container-slidebar">

</div>
<div class="main-content">
    <div class="container-topbar">

    </div>
    <div class="container">
        <div class="header">
            <div class="header-title">
                <h2>Gestión de Carreras Universitarias</h2>
                <p>Administración completa de planes de estudio</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" id="btn-actualizar">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
                <script>
                    document.getElementById("btn-actualizar").addEventListener("click", () => {
                        location.reload()
                    })
                </script>
                <button class="btn btn-primary" id="btnCrear">
                    <i class="fas fa-plus"></i> Nueva Carrera
                </button>
            </div>
        </div>

        <div class="dashboard">
            <div class="card-estadisticas">
                <div class="card-title">
                    <i class="fas fa-graduation-cap"></i> Total Carreras
                </div>
                <div class="stat-number"><?= htmlspecialchars($total_carreras) ?></div>
                <div class="stat-info">
                    <i class="fas fa-arrow-up"></i> Datos actualizados
                </div>
            </div>

            <div class="card-estadisticas">
                <div class="card-title">
                    <i class="fas fa-users"></i> Estudiantes Activos
                </div>
                <div class="stat-number"><?= htmlspecialchars($total_estudiantes) ?></div>
                <div class="stat-info">
                    <i class="fas fa-arrow-up"></i> Datos actualizados
                </div>
            </div>

            <div class="card-estadisticas">
                <div class="card-title">
                    <i class="fas fa-book"></i> Cursos Totales
                </div>
                <div class="stat-number"><?= htmlspecialchars($total_materias) ?></div>
                <div class="stat-info">
                    <i class="fas fa-arrow-up"></i> Datos actualizados
                </div>
            </div>

            <div class="card-estadisticas">
                <div class="card-title">
                    <i class="fas fa-user-graduate"></i> Egresados
                </div>
                <div class="stat-number"><?= htmlspecialchars($total_egresados) ?></div>
                <div class="stat-info">
                    <i class="fas fa-arrow-up"></i> Datos actualizados
                </div>
            </div>
        </div>

   
        
        <section id="lista-carreras" class="careers-container">

            <div class="filter-switch-wrapper">
                <div class="filter-switch" id="status-filter-switch">
                    <span class="slider"></span>

                    <button type="button" class="switch-btn active" data-filter="activas">
                        Habilitadas
                    </button>
                    <button type="button" class="switch-btn" data-filter="inactivas">
                        Inhabilitadas
                    </button>
                </div>
            </div>

            <div class="container-lista-carreras">
                <?php foreach ($carreras as $c): ?>
                    <div class="career-card">
                        <article class="card <?= ($c['estado'] == 'inhabilitada' || $c['estado'] == '0') ? 'card-inhabilitada' : '' ?>" 
                                data-id="<?= htmlspecialchars($c['id_carrera']) ?>" 
                                data-tipo="carrera">

                            <a class="card-link career-header" href="carrera/carrera.php?id=<?= urlencode($c['id_carrera']) ?>">
                                <h3 class="career-title"><?= htmlspecialchars($c['nombre_carrera']) ?></h3>

                                <p class="muted career-id">
                                    Estado: <span class="status-badge"><?= htmlspecialchars($c['estado']) ?></span>
                                </p>

                                <div class="career-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Grupos</span>
                                        <span class="stat-value"><?= htmlspecialchars($c['total_grupos'] ?? 0) ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Estudiantes</span>
                                        <span class="stat-value"><?= htmlspecialchars($c['total_estudiantes'] ?? 0) ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Trayectos</span>
                                        <span class="stat-value"><?= htmlspecialchars($c['total_trayectos'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>



        <section id="lista-trayectos-iniciales">
            <div class="h2-grupo-inicial">
                <h2>Grupos Iniciales</h2>
                <div class="plus-icon">
                    <div class="bar horizontal"></div>
                    <div class="bar vertical"></div>
                </div>
            </div>

            <form id="formCrearGrupos" class="container-crear-trayectos-iniciales">
                <div id="gruposContainer" class="grupos-container">
                    <input id="trayecto-inicial-input" type="text" name="grupos[]" placeholder="Nombre del grupo" required>
                </div>

                <button type="submit" class="btn-guardar-grupos-ini">Guardar Grupos Iniciales</button>
            </form>

     
      
        <div class="careers-container-inicial">
            <?php if (!empty($trayectos)) : ?>
                <?php foreach ($trayectos as $t) : ?>
                    <div class="career-card">
                        <article class="card" data-id="<?= $t['id_trayecto_inicial'] ?>" data-tipo="trayecto">
                            <a class="card-link career-header career-header-inicial" href="grupo_inicial.php?id=<?= $t['id_trayecto_inicial'] ?>">
                                <h3 class="career-title"><?= htmlspecialchars($t['nombre_grupo_inicial']) ?></h3>
                            </a>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay grupos iniciales creados.</p>
            <?php endif; ?>
        </div>

        </section>
         
        
        <!-- CREAR TRAYECTOS INICIALES -->
        <script>
             let formInicialOpen = false

            const btn_mas_inicial = document.querySelector(".plus-icon")
            const btn_guardar_grupos_ini = document.querySelector(".btn-guardar-grupos-ini")
            const form_crear_grupos_ini = document.querySelector(".container-crear-trayectos-iniciales")

            btn_mas_inicial.addEventListener("click", () => {
                if (formInicialOpen == false) {
                    form_crear_grupos_ini.classList.add("active-container-crear-trayectos-iniciales")
                    btn_mas_inicial.classList.add("active-plus")
                    formInicialOpen = true
                } else {
                    form_crear_grupos_ini.classList.remove("active-container-crear-trayectos-iniciales")
                    btn_mas_inicial.classList.remove("active-plus")
                    formInicialOpen = false
                }
            })

            btn_guardar_grupos_ini.addEventListener("click", () => {
                form_crear_grupos_ini.classList.remove("active-container-crear-trayectos-iniciales")
                btn_mas_inicial.classList.remove("active-plus")
                formInicialOpen = false
            })
            // ------------------- AJAX -------------------
            document.getElementById('formCrearGrupos').addEventListener('submit', function(e) {
                e.preventDefault();

                const gruposInputs = this.querySelectorAll('input[name="grupos[]"]');
                const min = 2;
                const max = 30;

                // Definimos el patrón: Letras, números, espacios, guiones y tildes
                const regexGrupo = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-]+$/;

                // Validar cada grupo
                for (let i = 0; i < gruposInputs.length; i++) {
                    const valor = gruposInputs[i].value.trim();

                    // 1. Vacío
                    if (valor.length === 0) {
                        alert("El nombre del grupo no puede estar vacío", "error", "mensajes-aqui");
                        gruposInputs[i].focus();
                        return;
                    }

                    // 2. Muy corto
                    if (valor.length < min) {
                        alert(`Fila ${i+1}: El nombre del grupo debe tener al menos ${min} caracteres`, "error", "mensajes-aqui");
                        gruposInputs[i].focus();
                        return;
                    }

                    // 3. Muy largo
                    if (valor.length > max) {
                        alert(`Fila ${i+1}: El nombre del grupo no puede superar los ${max} caracteres`, "error", "mensajes-aqui");
                        gruposInputs[i].focus();
                        return;
                    }

                    // 4. Caracteres no válidos (NUEVA VALIDACIÓN)
                    if (!regexGrupo.test(valor)) {
                        alert(`Fila ${i+1}: El nombre del grupo contiene caracteres no permitidos (solo letras, números, espacios y guiones)`, "error", "mensajes-aqui");
                        gruposInputs[i].focus();
                        return;
                    }
                }

                // 👉 Si todo OK, enviamos
                const formData = new FormData(this);

                fetch('guardar_trayectos_iniciales.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert("Trayecto inicial creado correctamente", "exito", "mensajes-aqui");
                            setTimeout(() => location.reload(), 2000);

                            // Reset visual
                            document.getElementById('gruposContainer').innerHTML = `
                                <div class="grupo">
                                    <input type="text" name="grupos[]" placeholder="Nombre del grupo" required>
                                </div>
                            `;
                        } else {
                            alert(data.msg || "Error desconocido", "error", "mensajes-aqui");
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Error de conexión", "error", "mensajes-aqui");
                    });
            });
        </script>

        <section id="pensum-inicial" class="pensum-inicial">
    <div class="h2-grupo-inicial">
        <h2>Pensum de Materias - Trayectos Iniciales</h2>
        <div class="plus-icon-2">
            <div class="bar horizontal"></div>
            <div class="bar vertical"></div>
        </div>
    </div>

    <form id="formAgregarMateria" class="form-Agregar-Materia">
        <div class="materia">
            <input id="nombre_materia_input" type="text" name="nombre_materia" placeholder="Nombre de la materia" required>
            <input type="text" name="codigo_materia" placeholder="Código de la materia">
            <input type="number" name="creditos" placeholder="Créditos" min="0">
            <input type="number" name="horas_semanales" placeholder="Horas semanales" min="0">
        </div>
        <button type="submit" class="btn-agregar-materia">Agregar Materia</button>
    </form>

   <?php
// Traemos únicamente las materias que están activas en el sistema
$sqlMaterias = "SELECT * FROM materias_pensum_inicial WHERE activo = 1 ORDER BY fecha_creacion ASC";
$resMaterias = $conn->query($sqlMaterias);
$materias = $resMaterias ? $resMaterias->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="container-pensum-materias-ini-tabla">
    <div class="pensum-materias-ini-tabla">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Créditos</th>
                    <th>Horas Semanales</th>
                    <th>Estatus</th> 
                </tr>
            </thead>
            <tbody id="listaMaterias">
                <?php foreach ($materias as $m): ?>
                    <tr data-id="<?= $m['id_materia_inicial'] ?>">
                        <td><?= $m['id_materia_inicial'] ?></td>
                        <td><?= htmlspecialchars($m['nombre_materia']) ?></td>
                        <td><?= htmlspecialchars($m['codigo_materia']) ?></td>
                        <td><?= $m['creditos'] ?></td>
                        <td><?= $m['horas_semanales'] ?></td>
                        <td>
                            <span class="badge bg-success">Activa</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <div id="contextMenuMateria" class="custom-context-menu" style="display: none;">
        <button id="btnEliminarMateriaMenu" type="button" class="item-menu-eliminar">
            Eliminar Materia
        </button>
    </div>
</section>

<script>
    let formMateriaOpen = false;

    const btn_mas_inicial_2 = document.querySelector(".plus-icon-2");
    const form_agregar_materia = document.getElementById("formAgregarMateria");

    // Interfaz: Abrir/Cerrar Formulario
    btn_mas_inicial_2.addEventListener("click", () => {
        if (!formMateriaOpen) {
            form_agregar_materia.classList.add("active-form-Agregar-Materia");
            btn_mas_inicial_2.classList.add("active-plus");
            formMateriaOpen = true;
        } else {
            form_agregar_materia.classList.remove("active-form-Agregar-Materia");
            btn_mas_inicial_2.classList.remove("active-plus");
            formMateriaOpen = false;
        }
    });

    // Envío del Formulario vía AJAX
    document.getElementById('formAgregarMateria').addEventListener('submit', function(e) {
        e.preventDefault();

        const nombreInput = this.querySelector('[name="nombre_materia"]');
        const codigoInput = this.querySelector('[name="codigo_materia"]');
        const creditosInput = this.querySelector('[name="creditos"]');
        const horasInput = this.querySelector('[name="horas_semanales"]');

        const nombre = nombreInput.value.trim();
        const codigo = codigoInput.value.trim();
        const creditos = parseInt(creditosInput.value, 10);
        const horas = parseInt(horasInput.value, 10);

        const nombreMin = 3;
        const nombreMax = 50;
        const codigoMax = 10;

        // -------- VALIDACIONES PATRONES --------
        const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
        const regexCodigo = /^[a-zA-Z0-9\-]+$/;
        const regexSoloNumeros = /^\d+$/;

        if (nombre.length < nombreMin || nombre.length > nombreMax) {
            alert(`El nombre debe tener entre ${nombreMin} y ${nombreMax} caracteres.`, "error", "mensajes-aqui");
            nombreInput.focus();
            return;
        }

        if (!regexNombre.test(nombre)) {
            alert("El nombre solo puede contener letras y espacios.", "error", "mensajes-aqui");
            nombreInput.focus();
            return;
        }

        if (codigo.length > 0) {
            if (codigo.length > codigoMax || !regexCodigo.test(codigo)) {
                alert(`Código inválido (Máx ${codigoMax} caracteres alfanuméricos o guiones).`, "error", "mensajes-aqui");
                codigoInput.focus();
                return;
            }
        }

        if (!regexSoloNumeros.test(creditosInput.value) || creditos < 0 || creditos > 20) {
            alert("Créditos debe ser un entero entre 0 y 20.", "error", "mensajes-aqui");
            creditosInput.focus();
            return;
        }

        if (!regexSoloNumeros.test(horasInput.value) || horas < 0 || horas > 40) {
            alert("Horas semanales debe ser un entero entre 0 y 40.", "error", "mensajes-aqui");
            horasInput.focus();
            return;
        }

        const formData = new FormData(this);

        fetch('agregar_materia_pensum.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Materia agregada al pensum correctamente", "exito", "mensajes-aqui");
                
                const tbody = document.getElementById('listaMaterias');
                const nuevaFila = document.createElement('tr');
                nuevaFila.setAttribute('data-id', data.id); // Aseguramos que la nueva fila también reciba el ID para el click derecho
                nuevaFila.innerHTML = `
                    <td>${data.id}</td>
                    <td>${escapeHTML(data.nombre_materia)}</td>
                    <td>${escapeHTML(data.codigo_materia)}</td>
                    <td>${data.creditos}</td>
                    <td>${data.horas_semanales}</td>
                    <td><span class="badge bg-success">Activa</span></td>
                `;
                tbody.appendChild(nuevaFila);

                this.reset();
                form_agregar_materia.classList.remove("active-form-Agregar-Materia");
                btn_mas_inicial_2.classList.remove("active-plus");
                formMateriaOpen = false;

                setTimeout(() => location.reload(), 1500);
            } else {
                alert(data.msg || "Error desconocido", "error", "mensajes-aqui");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error de conexión con el servidor", "error", "mensajes-aqui");
        });
    });

    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, 
            tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
        );
    }

    // ─── LÓGICA DEL MENÚ CONTEXTUAL (CLICK DERECHO) ───
const contextMenu = document.getElementById('contextMenuMateria');
let materiaIdSeleccionada = null;
let filaSeleccionada = null;

// 1. Escuchar el click derecho en el tbody
document.getElementById('listaMaterias').addEventListener('contextmenu', function(e) {
    const tr = e.target.closest('tr');
    if (!tr) return;

    e.preventDefault();

    // NUEVO: Si ya había una fila seleccionada previamente, le quitamos el color
    if (filaSeleccionada) {
        filaSeleccionada.classList.remove('fila-marcada');
    }

    materiaIdSeleccionada = tr.getAttribute('data-id');
    filaSeleccionada = tr;

    // NUEVO: Marcamos la fila actual con el color de selección
    filaSeleccionada.classList.add('fila-marcada');

    contextMenu.style.display = 'block';
    contextMenu.style.left = `${e.pageX}px`;
    contextMenu.style.top = `${e.pageY}px`;
});

// 2. Ocultar el menú con click izquierdo afuera
document.addEventListener('click', function(e) {
    if (e.target.closest('#contextMenuMateria')) return;
    contextMenu.style.display = 'none';

    // NUEVO: Si cierra el menú haciendo click afuera, desmarcamos la fila
    if (filaSeleccionada) {
        filaSeleccionada.classList.remove('fila-marcada');
        filaSeleccionada = null;
        materiaIdSeleccionada = null;
    }
});


document.getElementById('btnEliminarMateriaMenu').addEventListener('click', async function () {
    // Guardar referencias antes de abrir la confirmación
    const idMateriaAEliminar = materiaIdSeleccionada;
    const filaAEliminar = filaSeleccionada;

    if (!idMateriaAEliminar || !filaAEliminar) return;

    contextMenu.style.display = 'none';

    const mensaje =
        "¿Estás seguro de que deseas eliminar esta materia del pensum activo?\n" +
        "(Nota: Los historiales antiguos no se verán afectados)";

    let confirmado;

    try {
        confirmado = await window.customConfirm(
            mensaje,
            "Eliminar Materia"
        );
    } catch (err) {
        console.error("Error en la confirmación:", err);
        alert("No se pudo mostrar la confirmación.", "error", "mensajes-aqui");
        return;
    }

    if (!confirmado) {
        filaAEliminar.classList.remove('fila-marcada');

        if (filaSeleccionada === filaAEliminar) {
            filaSeleccionada = null;
            materiaIdSeleccionada = null;
        }

        return;
    }

    try {
        const formData = new FormData();
        formData.append('id_materia', idMateriaAEliminar);

        const res = await fetch('eliminar_materia_pensum.php', {
            method: 'POST',
            body: formData
        });

        // Detectar respuestas HTTP incorrectas
        if (!res.ok) {
            throw new Error(`Error HTTP ${res.status}`);
        }

        const data = await res.json();

        if (!data.success) {
            filaAEliminar.classList.remove('fila-marcada');

            alert(
                data.msg || "No se pudo eliminar la materia.",
                "error",
                "mensajes-aqui"
            );
            return;
        }

        // Actualizar la fila original, sin depender de variables globales
        filaAEliminar.classList.add('materia-inactiva');
        filaAEliminar.classList.remove('fila-marcada');

        const badge = filaAEliminar.querySelector('.badge');

        if (badge) {
            badge.className = 'badge bg-danger';
            badge.textContent = 'Inactiva';
        }

        alert(
            data.msg || "Materia dada de baja con éxito.",
            "exito",
            "mensajes-aqui"
        );

        if (filaSeleccionada === filaAEliminar) {
            filaSeleccionada = null;
            materiaIdSeleccionada = null;
        }

        setTimeout(() => location.reload(), 1000);

    } catch (err) {
        console.error("Error al dar de baja la materia:", err);

        alert(
            "No se pudo completar la solicitud. Verifica la conexión e inténtalo nuevamente.",
            "error",
            "mensajes-aqui"
        );
    }
});

</script>

<div id="contextMenuMateria" class="custom-context-menu">
    <button id="btnEliminarMateriaMenu" type="button" class="item-menu-eliminar">
        <i class="fas fa-trash"></i> Eliminar Materia
    </button>
</div>

    <!-- CONTEXT MENU PARA CARRERAS -->
    </div> <div id="context-menu" class="context-menu">
        <ul>
            <li id="menu-editar"><i class="fas fa-edit"></i> Cambiar Nombre</li>
            <li id="menu-estado"><i class="fas fa-sync-alt"></i> Alternar Estado</li>
            <li id="menu-eliminar" class="text-danger">Eliminar Definitivamente</li>
        </ul>
    </div>


    <!-- MODAl PARA CREAR CARRERAS -->
    <div class="modal-overlay" id="modal-overlay"></div>
    <div id="modal" class="modal">
        <div class="modal-content" id="modal-content">
            <h2>Crear Carrera</h2>
            <form id="formCrear">
                <input id="nombre" name="nombre_carrera" required maxlength="200" placeholder="Ej: Ingeniería en Sistemas">
                <div class="actions">
                    <button type="button" id="btnCerrar" class="btn secondary"><i class="fa-solid fa-xmark"></i></button>
                    <button type="submit" class="btn primary" id="crear-carrer">Crear</button>
                </div>
            </form>
            <div id="formError" class="error" style="display:none"></div>
        </div>
    </div>

</div> <script src="../script_bar_nav.js"></script>
<script src="script_carreras.js"></script>
<script src="../mensajes.js"></script>
</body>
</html>