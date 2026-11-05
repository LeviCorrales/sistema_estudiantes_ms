<?php require_once 'consultas_grupo.php'; ?>


<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Grupo - <?=htmlspecialchars($grupo['nombre_grupo'])?></title>
    <link rel="stylesheet" href="styles_grupo.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../../../styles_bar_nav.css">
   

</head>

<body class="body">
<script src="../../../bar_nav.js"></script>
  <!-- Barra de Navegación Lateral -->
<div class="container-slidebar">
       
</div>
<div class="main-content">
        <!-- Topbar -->
    <div class="container-topbar">

    </div>


    <div class="container">
        <!-- Header -->
        <a href="../carrera.php?id=<?=htmlspecialchars($grupo['id_carrera'])?>" class="muted">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="header">
            <div class="title-section">
                <div class="career-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="career-info">
                    <h1><?=htmlspecialchars($grupo['nombre_grupo'])?></h1>
                    <p>
                        <?=htmlspecialchars($grupo['nombre_carrera'])?> 
                        <?=htmlspecialchars($grupo['numero_trayecto'])?>-<?=htmlspecialchars($grupo['numero_periodo'])?>
                    </p>
                </div>
                
            </div>

            <div class="container-periodos-fecha">
                <div class="btn-avanzar-periodo">
                    <?php if ($next_periodo): ?>
                    <button id="btnAvanzar" data-id="<?= $grupo['id_grupo'] ?>">Avanzar Periodo</button>
                    <?php else: ?>
                    <button id="btnEgresar" class="btn-finalizar" data-id="<?= $grupo['id_grupo'] ?>">
                        <i class="fas fa-graduation-cap"></i> Egresar Grupo
                    </button>
                    <?php endif; ?>
                
                    <button id="btnRetroceder" data-id="<?= $grupo['id_grupo'] ?>">Retroceder Periodo</button>
                </div>
                <div class="container-fecha-periodo">
                    <?php if(isset($grupo['fecha_inicio'], $grupo['fecha_fin']) && $grupo['fecha_inicio'] && $grupo['fecha_fin']): ?>
                        <div class="fecha-periodo">
                            <p>Inicio: <?=htmlspecialchars($grupo['fecha_inicio'])?> </p> 
                            <p>Fin: <?=htmlspecialchars($grupo['fecha_fin'])?></p> 
                
                        </div>
                    <?php else: ?>
                        <p>No hay fechas asignadas para este periodo.</p>
                    <?php endif; ?>
                </div>
                 <!-- Fechas del periodo -->

                <div class="container-form-fecha">
                    <div class="fecha-inicio-button">
                        <p>Modificar Fecha</p>
                        <i class="fa-solid fa-angle-down"></i>
                    </div>
        
                    <form method="POST" class="form-fecha" id="formFecha">
                        <label>Fecha de inicio:</label>
                        <input type="date" name="fecha_inicio" id="f_inicio" value="<?=htmlspecialchars($grupo['fecha_inicio'] ?? '')?>" required>

                        <label>Fecha de fin:</label>
                        <input type="date" name="fecha_fin" id="f_fin" value="<?=htmlspecialchars($grupo['fecha_fin'] ?? '')?>" required>
                    
                        <button type="button" onclick="confirmarGuardar()">Guardar fechas</button>
                    </form>
                    <script>
                        function confirmarGuardar() {
                        const form = document.getElementById('formFecha');
                        const inicio = document.getElementById('f_inicio').value;
                        const fin = document.getElementById('f_fin').value;

                        // Validación rápida antes de esperar
                        if (!inicio || !fin) {
                            alert("Por favor, completa ambas fechas", "error", "mensajes-aqui");
                            return;
                        }

                        if (inicio >= fin) {
                            alert("La fecha de inicio debe ser menor a la de fin", "error", "mensajes-aqui");
                            return;
                        }

                        // 1. Lanzamos tu alert personalizado de "Procesando..." o éxito preventivo
                        alert("Guardando cambios...", "exito", "mensajes-aqui");

                        // 2. Esperamos 2 segundos (2000ms) antes de enviar
                        setTimeout(() => {
                            form.submit(); // Aquí es donde la página finalmente se recarga
                        }, 2000);
                    }
                        const btn_fecha_periodo = document.querySelector(".fecha-inicio-button")
                        const fecha_periodo = document.querySelector(".form-fecha")

                        let isOpen = false

                        btn_fecha_periodo.addEventListener("click", () => {
                            isOpen = !isOpen  // Cambia el valor (true/false)
                            
                            if(isOpen) {
                                fecha_periodo.classList.add("active")
                                btn_fecha_periodo.classList.add("active")
                            } else {
                                fecha_periodo.classList.remove("active")
                                btn_fecha_periodo.classList.remove("active")
                            }
                        })
                    </script>       
                </div>
            </div>
    
        </div>

<script src="fecha_grupo.js"></script>



        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Career Details -->
           <div class="career-details">
    <div class="detail-card">
        <h3><i class="fas fa-info-circle"></i> Información de la Carrera</h3>

        <div class="info-item">
            <div class="info-label">Carrera:</div>
            <div class="info-value"><?= htmlspecialchars($grupo['nombre_carrera']) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Trayecto:</div>
            <div class="info-value"><?= htmlspecialchars($grupo['numero_trayecto']) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Periodo:</div>
            <div class="info-value"><?= htmlspecialchars($grupo['numero_periodo']) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total de estudiantes:</div>
            <div class="info-value"><?= $total_estudiantes ?></div>
        </div>

        <!-- Contenedor relativo: Todo lo de adentro flotará respecto a este div -->
        <div class="vocero-container-relativo" style="position: relative;">
            
            <div class="info-item">
                <div class="info-label">Vocero:</div>
                <div class="info-value">
                    <?php if (!empty($grupo['id_vocero'])): ?>
                        <!-- Si hay vocero asignado -->
                        <?= htmlspecialchars($grupo['vocero_nombre'] . ' ' . $grupo['vocero_apellido']) ?>
                    <?php else: ?>
                        <!-- Si no hay vocero: Botón Añadir -->
                        <button class="btnToggleVocero btn-add-vocero">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Añadir Vocero
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($grupo['id_vocero'])): ?>
                <!-- Si hay vocero: Botón para cambiarlo -->
                <div class="info-actions">
                    <button class="btnToggleVocero btn-change-vocero">
                        Cambiar Vocero
                    </button>
                </div>
            <?php endif; ?>

            <!-- LA VENTANA FLOTANTE (Oculta por defecto) -->
            <div id="ventanaVocero" class="ventana-flotante" style="display:none;">
                <div class="ventana-header">
                    <h4>Selecciona un vocero</h4>
                    <button id="btnCerrarVoceroX" class="close-x">&times;</button>
                </div>
                
                <div class="table-container-vocero">
                    <table>
                        <thead>
                            <tr><th>Nombre</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Consulta de estudiantes del grupo
                            $stmt = $conn->prepare("
                                SELECT dp.id, dp.primer_nombre, dp.primer_apellido
                                FROM grupo_estudiantes ge
                                INNER JOIN datos_personales dp ON ge.id_estudiante = dp.id
                                WHERE ge.id_grupo = ?
                                ORDER BY dp.primer_apellido, dp.primer_nombre
                            ");
                            $stmt->bind_param("i", $id_grupo);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $estudiantesVocero = $res->fetch_all(MYSQLI_ASSOC);
                            $stmt->close();

                            foreach ($estudiantesVocero as $est): 
                                $nombreCompleto = htmlspecialchars($est['primer_nombre'] . ' ' . $est['primer_apellido']);
                            ?>
                                <tr class="select-vocero" data-id="<?= $est['id'] ?>" data-nombre="<?= $nombreCompleto ?>">
                                    <td><?= $nombreCompleto ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

                
             <!-- FUNCION AÑADIR/CAMBIAR VOCERO -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Usamos las clases e IDs actualizados
    const botonesToggle = document.querySelectorAll('.btnToggleVocero');
    const modal = document.getElementById('ventanaVocero');
    const btnCerrarX = document.getElementById('btnCerrarVoceroX');

    // 1. Mostrar la ventana flotante al hacer clic en "Añadir Vocero" o "Cambiar Vocero"
    botonesToggle.forEach(btn => {
        btn.addEventListener('click', () => {
            if (modal) modal.style.display = 'block';
        });
    });

    // 2. Ocultar la ventana flotante con el botón de la equis (×)
    if (btnCerrarX) {
        btnCerrarX.addEventListener('click', () => {
            if (modal) modal.style.display = 'none';
        });
    }

    // 3. Ocultar la ventana si se hace clic fuera de ella (en el contenedor o fondo)
    window.addEventListener('click', (e) => {
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // 4. Lógica de selección y asignación de vocero
    document.querySelectorAll('.select-vocero').forEach(row => {
        row.addEventListener('click', async () => {
            const idVocero = row.getAttribute('data-id');
            const nombreVocero = row.getAttribute('data-nombre');

            // Ocultar temporalmente el modal para que no estorbe con el confirm
            if (modal) modal.style.display = 'none';

            // Confirmar asignación de vocero
            const confirmado = await customConfirm(`¿Asignar a ${nombreVocero} como vocero del grupo?`);
            
            if (!confirmado) {
                // Si el usuario cancela, volvemos a mostrar el modal
                if (modal) modal.style.display = 'block';
                return;
            }

            try {
                // Fetch usando async/await
                const res = await fetch('set_vocero.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id_grupo=<?=$id_grupo?>&id_vocero=${idVocero}`
                });

                const data = await res.json();

                if (data.success) {
                    alert('Vocero asignado correctamente', 'exito', 'mensajes-aqui');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    alert('Error: ' + data.error, 'error', 'mensajes-aqui');
                    setTimeout(() => location.reload(), 2000);
                }

            } catch (err) {
                console.error(err);
                alert('Error de conexión', 'error', 'mensajes-aqui');
                setTimeout(() => location.reload(), 2000);
            }
        });
    });
});
</script>
            
    
            <!-- Students Section -->
            <div class="content-wrapper-sub">
                <div class="section-card">
                    <div class="section-header">
                        <h2><i class="fas fa-user-graduate"></i> Estudiantes</h2>
                        <div class="controls">
                            
                            <button id="btnAgregar" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nuevo
                            </button>
                         
                        </div>
                    </div>
                    
                
                    <div
                        class="container-table-responsive"
                        id="lista-estudiantes-grupo"
                        data-id-grupo="<?= (int)$id_grupo ?>"
                    >
                        <div class="table-responsive">
                            <?php if (count($estudiantes) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Cédula</th>
                                            <th>Nombre</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($estudiantes as $est): ?>
                                            <tr
                                                class="fila-estudiante-grupo"
                                                data-id-estudiante="<?= (int)$est['id'] ?>"
                                                data-nombre-estudiante="<?= htmlspecialchars(
                                                    $est['primer_nombre'] . ' ' . $est['primer_apellido'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >
                                                <td><?= htmlspecialchars($est['cedula']) ?></td>

                                                <td>
                                                    <?= htmlspecialchars(
                                                        $est['primer_nombre'] . ' ' . $est['primer_apellido']
                                                    ) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No hay estudiantes en este grupo.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                </div>


        
        
                










        

    <!-- Modal -->
    <div class="modal-overlay" id="modal-overlay"></div>
        <div id="modalAgregar" class="modal">
            <div class="modal-content">
                <span class="close" id="cerrarModal"><i class="fa-solid fa-xmark"></i></span>
                <h3>Buscar estudiante</h3>
                <input type="text" id="buscarEstudiante" placeholder="Escribe nombre o cédula..." >
                <div id="lista-estudiantes"></div>
            </div>
        </div>
    </div>


</div>

<div class="historial-container">
                    <h2>Historial Académico</h2>

                    <div class="container-ver-historial">
                        <select id="selectTrayecto"></select>

                        <select id="selectPeriodo"></select>

                        <button id="btnVerHistorial">Ver Historial</button>
                        <button id="btnGuardarHistorial">Guardar Historial</button>
                    </div>
  
                    <div class="container-tablaHistorial">
                        <table id="tablaHistorial">
                            <thead>
                                <tr id="theadMaterias">
                                    <th>Estudiante</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistorial"></tbody>
                        </table>
                    </div>

    

    
                    <div id="mensajeHistorial" style="margin-top:10px; color:green;"></div>
                    <div class="container-btn-action-historial"> 
                        <button id="btnConsolidarHistorial">
                            <i class="fa-solid fa-lock"></i> Consolidar Periodo Actual
                        </button>
                        <button id="btnExportarConsolidado">
                            <i class="fa-solid fa-download"></i>
                            <p>Exportar Acta Completa</p>
                        </button>
                    </div>
   
                    <div id="botonesMaterias"></div>

                </div>
  


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script src="script_pdf.js"></script>
<!-- LOGICA PARA VAGREGAR ESTUDIANTE -->
<script>
    const modal = document.getElementById('modalAgregar');
    const modalOverlay = document.getElementById('modal-overlay');
    const body = document.querySelector('.body');
    const btn = document.getElementById('btnAgregar');
    const span = document.getElementById('cerrarModal');
    const inputBuscar = document.getElementById('buscarEstudiante');
    const lista = document.getElementById('lista-estudiantes');

    // Manejo de apertura del modal
    btn.addEventListener("click", () => {
        modal.classList.add("active-modal-overlay");
        modalOverlay.classList.add("active-modal-overlay");
        body.classList.add("active-modal-overlay");
    });

    // Manejo de cierre del modal
    span.addEventListener("click", () => {
        modal.classList.remove("active-modal-overlay");
        modalOverlay.classList.remove("active-modal-overlay");
        body.classList.remove("active-modal-overlay");
    });

    // Buscador de estudiantes
    inputBuscar.addEventListener('keyup', () => {
        const q = inputBuscar.value.trim();

        if (q.length < 2) {
            lista.innerHTML = '';
            return;
        }

       fetch(`api_buscar_estudiantes.php?q=${encodeURIComponent(q)}`)
    .then(res => res.json())
    .then(data => {
        lista.innerHTML = '';

        if (data.length === 0) {
            lista.innerHTML = '<div class="no-results">No se encontraron estudiantes</div>';
            return;
        }

        data.forEach(est => {
            // Construir el nombre completo
            const nombres = [est.primer_nombre, est.segundo_nombre].filter(Boolean).join(' ');
            const apellidos = [est.primer_apellido, est.segundo_apellido].filter(Boolean).join(' ');
            const nombreCompleto = `${nombres} ${apellidos}`;

            const div = document.createElement('div');
            div.className = 'estudiante-item';
            div.textContent = `${nombreCompleto} - ${est.cedula}`;

            // Acción al hacer clic en un estudiante
            div.onclick = async () => {
                // 1. Usamos tu confirmación personalizada
                const confirmado = await customConfirm(`¿Agregar a ${nombreCompleto} al grupo?`);
                if (!confirmado) return;

                try {
                    // 💡 IMPORTANTE: Si este código está en un archivo .js externo, 
                    // <?= $id_grupo ?> no funcionará. Debes capturar el ID de la URL o de un input oculto.
                    // Aquí asumo que lo tienes en una variable de JS llamada idGrupo actual.
                    
                    const res = await fetch('api_agregar_estudiante_grupo.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id_grupo=<?= $id_grupo ?>&id_estudiante=${est.id}` 
                    });

                    // 2. 🔴 CAMBIO CLAVE: Leer como JSON, no como texto
                    const dataRespuesta = await res.json(); 
                    
                    // 3. 🔴 CAMBIO CLAVE: Evaluamos si fue éxito o error
                    if (dataRespuesta.success) {
                        // Es un éxito: Mostramos solo el 'message' y pasamos el tipo 'exito'
                        alert(dataRespuesta.message, 'exito', 'mensajes-aqui');
                        
                        // Recargamos la página después de 2 segundos
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        // Es un error: Mostramos solo el 'error' y pasamos el tipo 'error'
                        alert(dataRespuesta.error, 'error', 'mensajes-aqui');
                        
                        // Quité el reload aquí para que el usuario tenga tiempo de leer por qué falló
                    }

                } catch (err) {
                    console.error(err);
                    alert('Error de conexión con el servidor', 'error', 'mensajes-aqui');
                }
            };

            // Efectos visuales (hover)
            div.addEventListener('mouseenter', () => {
                div.style.backgroundColor = '#f0f0f0';
                div.style.cursor = 'pointer';
            });

            div.addEventListener('mouseleave', () => {
                div.style.backgroundColor = '';
            });

            lista.appendChild(div);
        });
    })
    .catch(error => {
        console.error('Error en la búsqueda:', error);
        lista.innerHTML = '<div class="error">Error al buscar estudiantes</div>';
    });
    });
</script>







<script>
// ================================================================
// SISTEMA DE GESTIÓN DE HISTORIAL ACADÉMICO CON SNAPSHOTS (INTEGRADO)
// ================================================================

// ---------- 1. Variables de Estado Global ----------
const idGrupo = <?= json_encode($grupo['id_grupo'] ?? 0) ?>;
const trayectoActual = <?= json_encode($grupo['id_trayecto'] ?? '') ?>;
const periodoActual = <?= json_encode($grupo['id_periodo'] ?? '') ?>;
const estudiantes = <?= json_encode($estudiantes ?? []) ?>;
const infoGrupoCompleta = <?= json_encode($infoGrupo ?? []) ?>;

let materias = [];
let historial = [];
let trayectoSeleccionado = trayectoActual || null;
let periodoSeleccionado = periodoActual || null;
window.datosPeriodoSeleccionado = null; 

// ---------- 2. Inicialización y Carga de Datos ----------
document.addEventListener('DOMContentLoaded', async () => {
    await inicializarFiltros();
});

async function inicializarFiltros() {
    try {
        const res = await fetch(`api_get_historial.php?id_grupo=${idGrupo}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error);

        const trayectoSelect = document.getElementById('selectTrayecto');
        trayectoSelect.innerHTML = '<option value="">-- Selecciona --</option>';

        (data.trayectos || []).forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id_trayecto;
            opt.textContent = `Trayecto ${t.numero_trayecto}`;
            if (String(t.id_trayecto) === String(trayectoActual)) opt.selected = true;
            trayectoSelect.appendChild(opt);
        });

        if (trayectoActual) {
            await cargarPeriodos(trayectoActual, periodoActual);
            if (periodoActual) await verHistorial(trayectoActual, periodoActual);
        }
    } catch (err) {
        mostrarMensaje('Error inicial: ' + err.message, 'red');
    }
}

async function cargarPeriodos(idTrayecto, periodoPreseleccionado = null) {
    const periodoSelect = document.getElementById('selectPeriodo');
    periodoSelect.innerHTML = '<option value="">-- Selecciona --</option>';

    try {
        const res = await fetch(`api_get_historial.php?id_grupo=${idGrupo}&id_trayecto=${idTrayecto}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error);

        (data.periodos || []).forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id_periodo;
            opt.textContent = `Periodo ${p.numero_periodo}`;
            if (String(p.id_periodo) === String(periodoPreseleccionado)) opt.selected = true;
            periodoSelect.appendChild(opt);
        });
    } catch (err) {
        mostrarMensaje('Error al cargar periodos: ' + err.message, 'red');
    }
}

// ---------- 3. Lógica de Visualización (Ver Historial) ----------
async function verHistorial(idTrayecto, idPeriodo) {
    try {
        const res = await fetch(`api_get_historial.php?id_grupo=${idGrupo}&id_trayecto=${idTrayecto}&id_periodo=${idPeriodo}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error);

        materias = data.materias || [];
        historial = data.historial || [];
        window.datosPeriodoSeleccionado = data.datosPeriodo;

        renderTabla();
    } catch (err) {
        mostrarMensaje('Error al obtener historial: ' + err.message, 'red');
    }
}

function renderTabla() {
    const thead = document.getElementById('theadMaterias');
    const tbody = document.getElementById('tbodyHistorial');
    // Verificamos si el periodo ya está "sellado"
    const esConsolidado = historial.length > 0 && Number(historial[0].consolidado) === 1;

    thead.innerHTML = '<th>Estudiante</th>';
    tbody.innerHTML = '';

    // Dibujar Cabeceras (Snapshot)
    materias.forEach(mat => {
        const hReg = historial.find(h => String(h.id_materia) === String(mat.id_materia));
        // Si hay snapshot usamos ese nombre, sino el del pensum actual
        const nombreDisplay = hReg?.nombre_materia_display || mat.nombre_materia;

        const th = document.createElement('th');
        th.innerHTML = `${nombreDisplay}<br>`;
        
        const inputProf = document.createElement('input');
        inputProf.type = 'text';
        inputProf.placeholder = 'Profesor...';
        inputProf.value = hReg?.profesor || '';
        inputProf.disabled = esConsolidado;
        inputProf.dataset.materiaId = mat.id_materia;
        
        th.appendChild(inputProf);
        thead.appendChild(th);
    });

    // Dibujar Filas Estudiantes
    estudiantes.forEach(est => {
        const tr = document.createElement('tr');
        // Importante para el PDF: Guardamos los datos en el dataset
        tr.dataset.estudianteId = est.id;
        tr.dataset.nombre = est.primer_nombre || "";
        tr.dataset.apellido = est.primer_apellido || "";
        tr.dataset.ci = est.cedula || "";

        const tdNombre = document.createElement('td');
        tdNombre.textContent = `${est.primer_apellido} ${est.primer_nombre}`;
        tr.appendChild(tdNombre);

        materias.forEach(mat => {
            const reg = historial.find(h => String(h.id_estudiante) === String(est.id) && String(h.id_materia) === String(mat.id_materia));
            const td = document.createElement('td');
            const inputNota = document.createElement('input');
            inputNota.type = 'number';
            inputNota.min = 0; inputNota.max = 20;
            
            // CORRECCIÓN AQUÍ: Evita que JavaScript convierta el NULL de la base de datos en un 0
            if (reg && reg.nota !== null && reg.nota !== '') {
                inputNota.value = Math.round(Number(reg.nota));
            } else {
                inputNota.value = ''; // Queda totalmente vacío si es NULL
            }

            inputNota.disabled = esConsolidado;
            td.appendChild(inputNota);
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });

    // Control de Interfaz
    document.getElementById('btnGuardarHistorial').disabled = esConsolidado;
    document.getElementById('btnConsolidarHistorial').disabled = esConsolidado;
    
    crearBotonesPDFporMateria();
}



 
 // Normalizar el formato del nombre del profesor
function normalizarNombreProfesor(nombre) {
    return nombre
        .toLocaleLowerCase('es')
        .replace(/(^|[\s'-])(\p{L})/gu, (coincidencia, separador, letra) => {
            return separador + letra.toLocaleUpperCase('es');
        });
}

// Validar el nombre del profesor
function validarNombreProfesor(nombre) {
    const nombreLimpio = nombre.trim().replace(/\s+/g, ' ');

    if (nombreLimpio.length < 3 || nombreLimpio.length > 80) {
        return 'El nombre del profesor debe tener entre 3 y 80 caracteres.';
    }

    if (!/^\p{L}+(?:[ '-]\p{L}+)*$/u.test(nombreLimpio)) {
        return 'El nombre solo puede contener letras, espacios, apóstrofes y guiones.';
    }

    return '';
}

// Aplicar formato también a los inputs creados dinámicamente
document.addEventListener('input', function (e) {
    if (!e.target.matches('#theadMaterias input[type="text"]')) {
        return;
    }

    const input = e.target;
    const inicio = input.selectionStart;
    const fin = input.selectionEnd;

    input.maxLength = 80;
    input.value = normalizarNombreProfesor(input.value);

    input.setSelectionRange(inicio, fin);
});

document.addEventListener('focusout', function (e) {
    if (!e.target.matches('#theadMaterias input[type="text"]')) {
        return;
    }

    e.target.value = e.target.value.trim().replace(/\s+/g, ' ');
});


 // ---------- 4. Persistencia (Guardar y Consolidar) ----------
async function guardarHistorial() {
    const idTrayecto = document.getElementById('selectTrayecto').value;
    const idPeriodo = document.getElementById('selectPeriodo').value;
    const profesorInputs = Array.from(document.querySelectorAll('#theadMaterias input[type="text"]'));
    const filas = document.querySelectorAll('#tbodyHistorial tr');
    const registros = [];
    
    // Validar los nombres de los profesores antes de guardar
    for (const input of profesorInputs) {
        input.value = input.value.trim().replace(/\s+/g, ' ');

        const errorProfesor = validarNombreProfesor(input.value);

        if (errorProfesor) {
            mostrarMensaje('❌ ' + errorProfesor, 'red');
            input.focus();
            return;
        }
    }


    for (let tr of filas) {
        const idEstudiante = tr.dataset.estudianteId;
        const notasInputs = tr.querySelectorAll('input[type="number"]');

        for (let i = 0; i < materias.length; i++) {
            const notaVal = notasInputs[i].value.trim();

            // Validar que la nota esté entre 0 y 20
            if (notaVal !== '') {
                const nota = Number(notaVal);

                if (!Number.isFinite(nota) || nota < 0 || nota > 20) {
                    mostrarMensaje(
                        `❌ La nota debe estar entre 0 y 20. Verifica los datos ingresados.`,
                        'red'
                    );
                    notasInputs[i].focus();
                    return;
                }
            }

            registros.push({
                id_estudiante: idEstudiante,
                id_materia: materias[i].id_materia,
                profesor: profesorInputs[i].value,
                nota: notaVal === '' ? null : Number(notaVal)
            });
        }
    }

    try {
        const res = await fetch('api_guardar_historial.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_grupo: idGrupo,
                id_trayecto: idTrayecto,
                id_periodo: idPeriodo,
                registros
            })
        });

        const data = await res.json();

        mostrarMensaje(
            data.success ? 'Guardado' : '❌ ' + data.error,
            data.success ? 'green' : 'red'
        );

        if (data.success) {
            verHistorial(idTrayecto, idPeriodo);
        }
    } catch (err) {
        mostrarMensaje('Error de conexión', 'red');
    }
}

async function consolidar() {
    // 1. Invocamos el confirm personalizado con await
    const respuesta = await window.customConfirm(
        "¿Deseas consolidar este periodo? Se creará una copia inmutable del pensum actual para este grupo.", 
        "Consolidación Definitiva"
    );

    // 2. Si el usuario cierra o da a "No", detenemos la ejecución
    if (!respuesta) return;

    // 3. Si aceptó, procedemos con el fetch
    try {
        const res = await fetch('api_consolidar_historial.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }, // Es buena práctica añadir el header
            body: JSON.stringify({
                id_grupo: idGrupo,
                id_trayecto: document.getElementById('selectTrayecto').value,
                id_periodo: document.getElementById('selectPeriodo').value
            })
        });

        const data = await res.json();

        if (data.success) {
            alert("¡Periodo Consolidado exitosamente!");
            location.reload();
        } else {
            alert("Error: " + data.error);
        }
    } catch (err) {
        console.error(err);
        alert("Error de conexión al consolidar");
    }
}

// ---------- 5. Generación de PDF (Actas) ----------
function crearBotonesPDFporMateria() {
    const cont = document.getElementById('botonesMaterias');
    if(!cont) return;
    cont.innerHTML = "";

    materias.forEach((mat, index) => {
        const hReg = historial.find(h => String(h.id_materia) === String(mat.id_materia));
        const nombreDisplay = hReg?.nombre_materia_display || mat.nombre_materia;
        const profesor = document.querySelector(`input[data-materia-id="${mat.id_materia}"]`)?.value || "No asignado";

        const btn = document.createElement('button');
        btn.className = "btn-pdf";
        btn.innerHTML = `📄 Acta: ${nombreDisplay}`;
        btn.onclick = () => exportarPDFMateria(mat, index, profesor, nombreDisplay);
        cont.appendChild(btn);
    });
}

function exportarPDFMateria(materia, indexMateria, nombreProfesor, nombreDisplay) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    // Color exacto del lila en la imagen
    const lila = [200, 204, 255]; 
    
    // --- Variables de Datos ---
    const infoH = window.datosPeriodoSeleccionado || {};
    const fInicio = parsearFecha(infoH.fecha_inicio || infoGrupoCompleta.fecha_inicio);
    const fFin = parsearFecha(infoH.fecha_fin || infoGrupoCompleta.fecha_fin);
    
    // --- CORRECCIÓN: Capturar Trayecto y Periodo correctos ---
    const selectT = document.getElementById('selectTrayecto');
    const selectP = document.getElementById('selectPeriodo');
    
    // Extraemos el texto de los selects del HTML
    const textoTrayecto = selectT && selectT.options[selectT.selectedIndex] ? selectT.options[selectT.selectedIndex].text : "";
    const textoPeriodo = selectP && selectP.options[selectP.selectedIndex] ? selectP.options[selectP.selectedIndex].text : "";
    
    // Limpiamos el texto para dejar solo los números (ej: "Trayecto 2" -> "2")
    // Le damos prioridad a infoH.numero_periodo que viene directo de tu API
    const numTrayecto = textoTrayecto.replace(/\D/g, '') || "-";
    const numPeriodo = infoH.numero_periodo || textoPeriodo.replace(/\D/g, '') || "-";

    // Nombres y Cédulas (dinámicos)
    const nombreVocero = `${infoGrupoCompleta.vocero_nombre || ''} ${infoGrupoCompleta.vocero_apellido || ''}`.trim() || "-";
    const ciVocero = infoGrupoCompleta.vocero_ci || "-";
    const nombreProfe = nombreProfesor || "-";
    const ciProfe = "-"; // Cámbialo si tienes la CI del profesor en la DB

    // --- 1. BANNER SUPERIOR ---
    doc.setFillColor(lila[0], lila[1], lila[2]);
    doc.rect(14, 10, 182, 6, 'F');
    doc.setFont("helvetica", "bold");
    doc.setFontSize(11);
    doc.setTextColor(0, 0, 0);
    doc.text("ACTA DE EVALUACION DEFINITIVA", 105, 14.2, { align: 'center' });

    // --- 2. LOGOS ---
    const imgMinisterio = "../../../images/logo_ministerio_educacion.png"; // Asegúrate de tener la ruta correcta
    const imgMision = "../../../images/logo_mision_sucre.png";
    
    try {
        doc.addImage(imgMinisterio, 'PNG', 14, 18, 18, 20);
        doc.addImage(imgMision, 'PNG', 177, 18, 20, 20);
    } catch (e) { 
        console.warn("No se pudieron cargar los logos."); 
    }

    // --- 3. TABLA SUPERIOR (Metadatos Institucionales) ---
    doc.autoTable({
        startY: 40,
        theme: 'grid',
        styles: { 
            lineColor: [0, 0, 0], 
            lineWidth: 0.2, 
            fontSize: 8, 
            halign: 'center', 
            valign: 'middle',
            textColor: [0, 0, 0],
            fontStyle: 'bold',
            cellPadding: 1
        },
        body: [
            // Fila 1
            [
                {content: 'Estado', styles: {fillColor: lila}}, 
                {content: 'Municipio', styles: {fillColor: lila}}, 
                {content: 'Aldea', colSpan: 2, styles: {fillColor: lila}}, 
                {content: 'Lapso', colSpan: 4, styles: {fillColor: lila}}
            ],
            // Fila 2
            [
                {content: infoGrupoCompleta.estado || 'YARACUY'}, 
                {content: infoGrupoCompleta.municipio || 'NIRGUA'}, 
                {content: `"${infoGrupoCompleta.aldea || 'SIMON BOLIVAR'}"`, colSpan: 2}, 
                {content: 'Inicio', styles: {fillColor: lila}}, 
                {content: fInicio.d || '08'}, 
                {content: fInicio.m || '01'}, 
                {content: fInicio.a || '26'}
            ],
            // Fila 3
            [
                {content: 'Unidad Curricular \\ Eje \\ Taller', styles: {fillColor: lila}}, 
                {content: 'Trayecto', styles: {fillColor: lila}}, 
                {content: 'Trimestre', styles: {fillColor: lila}}, 
                {content: 'Sección', styles: {fillColor: lila}}, 
                {content: '', styles: {fillColor: lila}}, // Celda vacía sobre 'día'
                {content: 'dia', styles: {fontSize: 6}}, 
                {content: 'mes', styles: {fontSize: 6}}, 
                {content: 'año', styles: {fontSize: 6}}
            ],
            // Fila 4 (AQUÍ SE INYECTA EL TRAYECTO Y PERIODO DINÁMICO)
            [
                {content: nombreDisplay, styles: {halign: 'left'}}, 
                {content: numTrayecto}, 
                {content: numPeriodo}, 
                {content: infoGrupoCompleta.seccion || ''}, 
                {content: 'Fin', styles: {fillColor: lila}}, 
                {content: fFin.d || '25'}, 
                {content: fFin.m || '04'}, 
                {content: fFin.a || '26'}
            ],
            // Fila 5
            [
                {content: 'Nombre de la Profesora o Profesor Asesor', colSpan: 4, styles: {fillColor: lila}}, 
                {content: 'Nombre de la Preparadora o Preparador', colSpan: 4, styles: {fillColor: lila}}
            ],
            // Fila 6
            [
                {content: nombreProfe, colSpan: 4, styles: {halign: 'left'}}, 
                {content: '', colSpan: 4}
            ]
        ],
        margin: { left: 14, right: 14 }
    });

    // --- 4. CUERPO DE NOTAS ---
    const rowsBody = Array.from(document.querySelectorAll('#tbodyHistorial tr')).map((tr, i) => {
        const nota = tr.querySelectorAll('input[type="number"]')[indexMateria]?.value || "";
        return [
            i + 1, 
            tr.dataset.ci, 
            tr.dataset.apellido, 
            tr.dataset.nombre, 
            nota, 
            notaALetras(nota)
        ];
    });

    // Rellenar hasta 20 filas
    while(rowsBody.length < 20) {
        rowsBody.push([rowsBody.length + 1, '', '', '', '', '']);
    }

    doc.autoTable({
        startY: doc.lastAutoTable.finalY, // Pegado exactamente debajo de la tabla anterior
        theme: 'grid',
        head: [
            [
                {content: 'Nº', rowSpan:2, styles: {halign: 'center', valign: 'middle'}}, 
                {content: 'C.I.', rowSpan:2, styles: {halign: 'center', valign: 'middle'}}, 
                {content: 'APELLIDO', rowSpan:2, styles: {halign: 'center', valign: 'middle'}}, 
                {content: 'NOMBRE', rowSpan:2, styles: {halign: 'center', valign: 'middle'}}, 
                {content: 'CALIFICACION', colSpan:2, styles: {halign: 'center'}}
            ],
            [
                {content: 'numérica o\nliteral', styles: {halign: 'center', fontSize: 7, cellPadding: 0.5}}, 
                {content: 'en letras', styles: {halign: 'center'}}
            ]
        ],
        body: rowsBody,
        styles: { 
            lineColor: [0, 0, 0], 
            lineWidth: 0.2, 
            fontSize: 8, 
            textColor: [0, 0, 0],
            cellPadding: 1
        },
        headStyles: { fillColor: lila },
        columnStyles: {
            0: { cellWidth: 8, halign: 'center' },
            1: { cellWidth: 22, halign: 'center' },
            2: { cellWidth: 35, halign: 'left' },
            3: { cellWidth: 35, halign: 'left' },
            4: { cellWidth: 18, halign: 'left' },
            5: { halign: 'left' }
        },
        margin: { left: 14, right: 14 }
    });

    // --- 5. PIE DE PÁGINA Y FIRMAS ---
    let finalY = doc.lastAutoTable.finalY + 4;
    
    doc.setFont("helvetica", "bold");
    doc.setFontSize(9);
    doc.text("VA SIN ENMIENDAS", 105, finalY, { align: 'center' });

    finalY += 12;
    doc.setFontSize(8);
    doc.setFont("helvetica", "normal");
    
    // -- Vocero (Izquierda) --
    doc.text(`   ${nombreVocero.toUpperCase()}   `, 55, finalY, { align: 'center' });
    doc.line(20, finalY + 1, 90, finalY + 1); 
    doc.text("Vocera o Vocero estudiantil", 55, finalY + 4, { align: 'center' });
    doc.text(`C.I:      ${ciVocero}`, 55, finalY + 8, { align: 'center' });

    // -- Coordinador (Derecha) --
    doc.text("NELLY MARTINEZ", 145, finalY, { align: 'center' });
    doc.line(110, finalY + 1, 180, finalY + 1);
    doc.text("Coordinadora o Coordinador de Aldea", 145, finalY + 4, { align: 'center' });
    doc.text("C.I:", 145, finalY + 8, { align: 'center' });

    // -- Profesor (Abajo Izquierda) --
    finalY += 20;
    doc.text(`   ${nombreProfe.toUpperCase()}   `, 55, finalY, { align: 'center' });
    doc.line(20, finalY + 1, 90, finalY + 1);
    doc.text("Profesora o Profesor Asesor", 55, finalY + 4, { align: 'center' });
    doc.text(`C.I:      ${ciProfe}`, 55, finalY + 8, { align: 'center' });

    doc.save(`Acta_${nombreDisplay.replace(/\s+/g, '_')}.pdf`);
}
// ---------- 6. Eventos y Helpers ----------
document.getElementById('selectTrayecto').addEventListener('change', e => cargarPeriodos(e.target.value));
document.getElementById('btnVerHistorial').addEventListener('click', () => {
    verHistorial(document.getElementById('selectTrayecto').value, document.getElementById('selectPeriodo').value);
});
document.getElementById('btnGuardarHistorial').addEventListener('click', guardarHistorial);
document.getElementById('btnConsolidarHistorial').addEventListener('click', consolidar);

function mostrarMensaje(txt, color) {
    const m = document.getElementById('mensajeHistorial');
    if(m) { m.textContent = txt; m.style.color = color; }
}

function notaALetras(n) {
    const notas = ["CERO","UNO","DOS","TRES","CUATRO","CINCO","SEIS","SIETE","OCHO","NUEVE","DIEZ","ONCE","DOCE","TRECE","CATORCE","QUINCE","DIECISÉIS","DIECISIETE","DIECIOCHO","DIECINUEVE","VEINTE"];
    return notas[Math.round(n)] || "";
}

function parsearFecha(f) {
    if(!f) return {d:'', m:'', a:''};
    const parts = f.split('-');
    return { d: parts[2], m: parts[1], a: parts[0] };
}
</script>

<script src="menu_estudiante_grupo.js"></script>
<script src="../../../script_bar_nav.js"></script>
<script src="../../../mensajes.js"></script>
</body>
</html>
