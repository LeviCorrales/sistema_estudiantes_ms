<?php
include '../config/conexion.php';

// ------------------- RECIBIR ID DEL GRUPO -------------------
$idGrupo = $_GET['id'] ?? null;

if (!$idGrupo) {
    die("Grupo inicial no especificado.");
}

// ------------------- OBTENER DATOS DEL GRUPO -------------------
$sqlGrupo  = "SELECT * FROM trayecto_inicial WHERE id_trayecto_inicial = ?";
$stmtGrupo = $conn->prepare($sqlGrupo);
$stmtGrupo->bind_param("i", $idGrupo);
$stmtGrupo->execute();

$resultGrupo = $stmtGrupo->get_result();
$grupo       = $resultGrupo->fetch_assoc();
$stmtGrupo->close();

if (!$grupo) {
    die("Grupo inicial no encontrado.");
}

$nombreDelGrupo = $grupo['nombre_grupo_inicial'] ?? 'Sin Nombre';

// ------------------- OBTENER ESTUDIANTES -------------------
$sqlEstudiantes = "
    SELECT 
        tie.id AS id_grupo_estudiante, 
        dp.id AS id_estudiante, 
        dp.cedula,
        dp.primer_nombre, 
        dp.segundo_nombre, 
        dp.primer_apellido, 
        dp.segundo_apellido
    FROM trayecto_inicial_estudiantes tie
    JOIN datos_personales dp ON tie.id_estudiante = dp.id
    WHERE tie.id_trayecto_inicial = ?
";

$stmtEst = $conn->prepare($sqlEstudiantes);
$stmtEst->bind_param("i", $idGrupo);
$stmtEst->execute();

$resEst      = $stmtEst->get_result();
$estudiantes = $resEst->fetch_all(MYSQLI_ASSOC);
$stmtEst->close();

// ------------------- OBTENER MATERIAS (FILTRADO POR ACTIVAS) -------------------
// Añadimos "WHERE activo = 1" para que no aparezcan las materias eliminadas de forma lógica
$sqlMaterias = "SELECT id_materia_inicial, nombre_materia FROM materias_pensum_inicial WHERE activo = 1 ORDER BY nombre_materia ASC";
$resMat      = $conn->query($sqlMaterias);
$materias    = $resMat ? $resMat->fetch_all(MYSQLI_ASSOC) : [];

// ------------------- OBTENER LAS NOTAS GUARDADAS -------------------
$notasArray = [];

$sqlNotas = "
    SELECT id_estudiante, id_materia_inicial, nota 
    FROM historial_academico_inicial 
    WHERE id_trayecto_inicial = ?
";

$stmtNotas = $conn->prepare($sqlNotas);
if ($stmtNotas) {
    $stmtNotas->bind_param("i", $idGrupo);
    $stmtNotas->execute();
    $resNotas = $stmtNotas->get_result();

    // Armamos el arreglo con la estructura 'id_estudiante_id_materia' => nota
    while ($fila = $resNotas->fetch_assoc()) {
        $key = $fila['id_estudiante'] . '_' . $fila['id_materia_inicial'];
        
        // Solo guardamos el valor si no es nulo
        if ($fila['nota'] !== null) {
            $notasArray[$key] = $fila['nota'];
        }
    }
    $stmtNotas->close();
} else {
    die("Error preparando la consulta de notas: " . $conn->error);
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupo Inicial: <?= htmlspecialchars($grupo['nombre_grupo_inicial']) ?></title>
    
    <link rel="stylesheet" href="style_carreras.css">
    <link rel="stylesheet" href="style_grupo_inicial.css">
    <link rel="stylesheet" type="text/css" href="../styles_bar_nav.css">
    <link rel="stylesheet" href="../normalize.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <script src="../bar_nav.js"></script>

    <div class="container-slidebar"></div>

    <div class="main-content container">
        <div class="container-topbar"></div>

        <h1><?= htmlspecialchars($grupo['nombre_grupo_inicial']) ?></h1>

        <div class="container-busqueda-estudiante-inicial">
            <h2>Agregar Estudiante</h2>
            <input type="text" id="buscarEstudiante" placeholder="Buscar por nombre o cédula..." autocomplete="off">
            <div id="resultado-busqueda"></div>
        </div>

        

        <div class="container-tabla-estudiantes">
            <h2>Estudiantes del Grupo</h2>
            <table id="tabla-estudiantes">
                <colgroup>
                    <col style="width: 30%;"> <!-- Columna 1: Cédula/ID (Pequeña) -->
                    <col style="width: 70%;"> <!-- Columna 2: Nombre Completo (Ancha) -->
                    
                <thead>
                    <tr>
                        <th>Cedula</th>
                        <th>Nombre Completo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $est): ?>
                        <tr>
                            <td><?= htmlspecialchars($est['cedula'])?></td>
                            <td><?= htmlspecialchars($est['primer_nombre'].' '.$est['segundo_nombre'].' '.$est['primer_apellido'].' '.$est['segundo_apellido']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>



     <section id="historial-academico">
    <table id="tabla-notas-reporte" 
           data-nombre-grupo="<?php echo htmlspecialchars($nombreDelGrupo); ?>" 
           class="table table-bordered">
        <thead>
            <tr>
                <th>N°</th>
                <th>Cédula</th>
                <th>Apellidos y Nombres</th>
                <?php foreach ($materias as $m) : ?>
                    <th><?= htmlspecialchars($m['nombre_materia']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php $cont = 1; foreach ($estudiantes as $e) : ?>
                <tr>
                    <td><?= $cont++ ?></td>
                    <td><?= htmlspecialchars($e['cedula']) ?></td>
                    <td><?= htmlspecialchars($e['primer_nombre']." ".$e['primer_apellido']." ") ?></td>
                    
                    <?php foreach ($materias as $m) : ?>
                        <?php 
                            $key = $e['id_estudiante'] . '_' . $m['id_materia_inicial'];
                            $nota = $notasArray[$key] ?? ''; 
                        ?>
                        <td>
                            <input type="number" step="1" min="0" max="20" class="nota-input" 
                                   value="<?= htmlspecialchars($nota) ?>" 
                                   data-id-trayecto="<?= $idGrupo ?>"
                                   data-id-grupo-estudiante="<?= $e['id_grupo_estudiante'] ?? '' ?>"
                                   data-id-estudiante="<?= $e['id_estudiante'] ?>" 
                                   data-id-materia="<?= $m['id_materia_inicial'] ?>">
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

        <br>
        <button class="btn btn-warning" onclick="expulsarTodosEstudiantes(<?= $idGrupo ?>)">
            Culminar trayecto y exportar notas
        </button>

    </div> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
<script>
    const inputBuscar = document.getElementById('buscarEstudiante');
const resultadoDiv = document.getElementById('resultado-busqueda');

// CORRECCIÓN 1: Evitar error de sintaxis si $idGrupo está vacío en PHP
const idGrupo = <?= !empty($idGrupo) ? $idGrupo : 'null' ?>;

// NUEVO: Variable para rastrear la opción seleccionada con el teclado
let indiceSeleccionado = -1; 

if (inputBuscar) {
    // --- EVENTO 1: BÚSQUEDA EN TIEMPO REAL ---
    inputBuscar.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length < 2) {
            resultadoDiv.style.display = 'none';
            return;
        }

        fetch('buscar_estudiantes.php?q=' + encodeURIComponent(query) + '&id_grupo=' + idGrupo)
            .then(res => res.json())
            .then(data => {
                resultadoDiv.innerHTML = '';
                indiceSeleccionado = -1; // NUEVO: Reiniciar el índice cada vez que se busca algo nuevo
                
                if (data.length === 0) {
                    const div = document.createElement('div');
                    div.className = 'resultado-item sin-resultados'; // Se le agrega otra clase para ignorarlo con el teclado
                    div.textContent = 'No se encontraron resultados';
                    div.style.cursor = 'default';
                    div.style.color = '#999';
                    resultadoDiv.appendChild(div);
                    resultadoDiv.style.display = 'block';
                    return;
                }

                data.forEach(est => {
                    const div = document.createElement('div');
                    div.className = 'resultado-item seleccionable'; // NUEVO: Añadida la clase "seleccionable" para filtrarlo
                    div.textContent = est.nombre_completo + ' (' + est.cedula + ')';
                    div.dataset.id = est.id;
                    div.addEventListener('click', () => agregarEstudiante(est.id, est.nombre_completo));
                    resultadoDiv.appendChild(div);
                });

                resultadoDiv.style.display = 'block';
            });
    });

    // --- EVENTO 2: NAVEGACIÓN POR TECLADO (NUEVO) ---
    inputBuscar.addEventListener('keydown', function(e) {
        // Obtenemos solo los items válidos (ignoramos el mensaje de "No se encontraron resultados")
        const items = resultadoDiv.querySelectorAll('.seleccionable');
        
        if (items.length === 0 || resultadoDiv.style.display === 'none') return;

        if (e.key === 'ArrowDown') {
            e.preventDefault(); // Evita que el cursor del texto se mueva dentro del input
            indiceSeleccionado++;
            if (indiceSeleccionado >= items.length) indiceSeleccionado = 0; // Vuelve al inicio si llega al final
            resaltarItem(items);
        } 
        else if (e.key === 'ArrowUp') {
            e.preventDefault();
            indiceSeleccionado--;
            if (indiceSeleccionado < 0) indiceSeleccionado = items.length - 1; // Va al final si está en el inicio
            resaltarItem(items);
        } 
        else if (e.key === 'Enter') {
            e.preventDefault(); // Evita que se recargue la página si el input está dentro de un <form>
            if (indiceSeleccionado > -1 && items[indiceSeleccionado]) {
                // Simula un clic en el elemento activo, lo que dispara la función agregarEstudiante()
                items[indiceSeleccionado].click(); 
            }
        }
    });
}

// --- FUNCIÓN 3: PINTAR EL ELEMENTO Y MOVER EL SCROLL (NUEVO) ---
function resaltarItem(items) {
    // 1. Quitar la clase "seleccionado" a todos los elementos
    items.forEach(item => item.classList.remove('seleccionado'));
    
    // 2. Agregar la clase solo al elemento activo
    if (indiceSeleccionado > -1 && items[indiceSeleccionado]) {
        const itemActivo = items[indiceSeleccionado];
        itemActivo.classList.add('seleccionado');
        
        // 3. Magia: Hace que el scroll baje o suba automáticamente para que el ítem siempre sea visible
        itemActivo.scrollIntoView({ block: 'nearest' });
    }
}

// Agregar estudiante al grupo (Se mantiene igual)
function agregarEstudiante(idEst, nombre) {
    fetch('agregar_estudiante_grupo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_trayecto_inicial=' + idGrupo + '&id_estudiante=' + idEst
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Estudiante agregado correctamente: ' + nombre, "exito", "mensajes-aqui");
            setTimeout(() => location.reload(), 2000);
        } else {
            alert('Error: ' + data.msg, "error", "mensajes-aqui");
        }
    });
}

// Guardado de notas
document.querySelectorAll('.nota-input').forEach(input => {
    input.addEventListener('change', function () {
        // 1. Captura de datos
        const idTrayecto = this.dataset.idTrayecto;
        const idEstudiante = this.dataset.idEstudiante;
        const idMateria = this.dataset.idMateria;
        
        let idGrupoEstudiante = this.dataset.idGrupoEstudiante;
        if (idGrupoEstudiante === "0" || idGrupoEstudiante === "") {
            idGrupoEstudiante = null;
        }

        let valorNota = this.value.trim();
        
        // CORRECCIÓN 1: Quitamos "valorNota === ''" de aquí para permitir que los vacíos continúen el flujo
        if (!idEstudiante || !idMateria) return;

        let notaFinal;

        // CORRECCIÓN 2: Evaluamos de forma estricta si el campo está vacío
        if (valorNota === '') {
            notaFinal = ''; // Enviamos una cadena vacía para que el PHP la identifique y guarde NULL
        } else {
            // Si tiene texto, entonces sí lo tratamos numéricamente y lo validamos
            notaFinal = Math.round(Number(valorNota));

            if (notaFinal < 0 || notaFinal > 20) {
                alert("La nota debe estar entre 0 y 20", "error", "mensajes-aqui");
                this.value = '';
                return;
            }
        }

        // Para verificar en consola qué estamos enviando
        console.log("Enviando nota:", { idTrayecto, idGrupoEstudiante, idEstudiante, idMateria, nota: notaFinal });

        const formData = new FormData();
        formData.append('id_trayecto_inicial', idTrayecto);
        formData.append('id_grupo_estudiante', idGrupoEstudiante || '');
        formData.append('id_estudiante', idEstudiante);
        formData.append('id_materia', idMateria);
        formData.append('nota', notaFinal); // Mandará '' o el número entero (0-20)

        // CORRECCIÓN 2: Leer primero como texto para capturar errores de PHP
        fetch('guardar_nota_inicial.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text()) // Leemos texto en lugar de JSON directamente
        .then(text => {
            try {
                // Intentamos convertirlo a JSON
                const data = JSON.parse(text);
                if (data.success) {
                    console.log("Nota procesada: Estudiante " + idEstudiante); 
                } else {
                    alert(data.msg || "Error al guardar", "error", "mensajes-aqui");
                }
            } catch (e) {
                // Si falla el parseo, es porque PHP devolvió un error, HTML o espacios
                console.error("El servidor devolvió algo que no es JSON. Respuesta del servidor:", text);
                alert("Error en el servidor (Revisa la consola)", "error", "mensajes-aqui");
            }
        })
        .catch(error => {
            console.error("Error de Fetch:", error);
            alert("Error de conexión con el servidor", "error", "mensajes-aqui");
        });
    });
});

// Expulsar estudiantes (Generador PDF con Modal Manual)
function expulsarTodosEstudiantes(idGrupoParam) {
    if (!idGrupoParam) {
        alert("No hay un grupo válido para expulsar", "error", "mensajes-aqui");
        return;
    }

    // --- NUEVA CONDICIÓN: VERIFICAR QUE NO HAYAN NOTAS VACÍAS ---
    const inputsNotas = document.querySelectorAll('.nota-input');
    for (let input of inputsNotas) {
        if (input.value.trim() === '') {
            alert("No se puede procesar: Todos los estudiantes deben tener todas sus notas llenas.", "error", "mensajes-aqui");
            input.focus(); // Resalta visualmente el input que está vacío
            return; // Detiene la ejecución aquí mismo
        }
    }
    // -------------------------------------------------------------

    customConfirm('¿Seguro que desea procesar el consolidado y desvincular al grupo?')
    .then(res => {
        if (!res) return;

        // --- CREACIÓN DE MODAL MANUAL ---
        const modalHtml = `
            <div id="miModalPDF">
                <div class="miModalPDF_item">
                    <h3>Exportar Reporte</h3>
                    
                    <div class="container-labels-modalPDF">
                        <label>Año del Periodo:</label>
                        <input type="number" id="pdf-ano" value="${new Date().getFullYear()}">
                        
                        <label style="">Número de Periodo:</label>
                        <input type="text" id="pdf-periodo" placeholder="Ej: 1, 2 o I">
                    </div>
                    
                    <div class="btn-modalPDF">
                        <button id="btn-cancelar-pdf" style="">Cancelar</button>
                        <button id="btn-confirmar-pdf" style="">Descargar PDF</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        document.getElementById('btn-cancelar-pdf').onclick = () => document.getElementById('miModalPDF').remove();

        document.getElementById('btn-confirmar-pdf').onclick = async (e) => {
            const btnConfirmar = e.target;
            btnConfirmar.disabled = true;

            const ano = document.getElementById('pdf-ano')?.value;
            const numPeriodo = document.getElementById('pdf-periodo')?.value;

            if (!ano || !numPeriodo) {
                alert("Debes completar el año y el periodo.");
                btnConfirmar.disabled = false;
                return;
            }

            const modalPDF = document.getElementById('miModalPDF');
            if (modalPDF) modalPDF.remove();
            
            const tablaElemento = document.querySelector('#tabla-notas-reporte');
            if (!tablaElemento) {
                alert("Error: No se encontró la tabla de notas.");
                btnConfirmar.disabled = false;
                return;
            }

            // --- CARGA DE IMÁGENES ---
            const obtenerBase64 = async (url) => {
                try {
                    const response = await fetch(url);
                    const blob = await response.blob();
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onloadend = () => resolve(reader.result);
                        reader.readAsDataURL(blob);
                    });
                } catch (err) {
                    console.error("Error cargando imagen:", url, err);
                    return null; // Retorna null si la imagen falla
                }
            };

            const img1 = await obtenerBase64("../images/logo_ministerio_educacion.png");
            const img2 = await obtenerBase64("../images/logo_mision_sucre.png");

            // --- GENERACIÓN DEL PDF ---
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            const pageWidth = doc.internal.pageSize.getWidth();
            const nombreGrupo = tablaElemento.dataset.nombreGrupo || "Grupo";

            // 1. Franja y Títulos
            doc.setFillColor(200, 200, 255);
            doc.rect(14, 10, pageWidth - 28, 8, 'F');
            doc.setFont("helvetica", "bold");
            doc.setFontSize(12);
            doc.text("ACTA DE EVALUACION DEFINITIVA", pageWidth / 2, 15, { align: "center" });

            // 2. Logos (Solo si cargaron correctamente)
            if (img1) doc.addImage(img1, 'PNG', 14, 20, 20, 22);
            if (img2) doc.addImage(img2, 'PNG', pageWidth - 36, 20, 25, 25);

            // 3. Tablas
            doc.autoTable({
                startY: 45,
                head: [['Estado', 'Municipio', 'Aldea', 'Lapso']],
                body: [['YARACUY', 'NIRGUA', '"SIMÓN BOLÍVAR"', `Año: ${ano}`]],
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center' },
                headStyles: { fillColor: [240, 240, 240], textColor: 0 }
            });

            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 2,
                head: [['Descripción del Reporte', 'Trayecto', 'Periodo', 'Sección']],
                body: [['CONSOLIDADO DE NOTAS', nombreGrupo, numPeriodo, 'FIN DE SEMANA']],
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center' },
                headStyles: { fillColor: [240, 240, 240], textColor: 0 }
            });

            doc.autoTable({
                html: '#tabla-notas-reporte',
                startY: doc.lastAutoTable.finalY + 5,
                theme: 'grid',
                styles: { fontSize: 7, halign: 'center' },
                headStyles: { fillColor: [220, 220, 255], textColor: 0 },
                didParseCell: (data) => {
                    if (data.section === 'body' && data.cell.raw?.querySelector) {
                        const input = data.cell.raw.querySelector('input');
                        if (input) data.cell.text = [input.value || '0'];
                    }
                }
            });

            // 4. Firmas
            const finalY = doc.lastAutoTable.finalY + 10;
            doc.text("VA SIN ENMIENDAS", pageWidth / 2, finalY, { align: "center" });
            const firmaY = finalY + 20;
            doc.line(30, firmaY, 100, firmaY);
            doc.text("Vocería Estudiantil", 65, firmaY + 5, { align: "center" });
            doc.line(pageWidth - 100, firmaY, pageWidth - 30, firmaY);
            doc.text("Coordinación de Aldea", pageWidth - 65, firmaY + 5, { align: "center" });

            doc.save(`Notas - ${nombreGrupo}, Periodo ${ano}-${numPeriodo}.pdf`);

            setTimeout(() => {
                btnConfirmar.disabled = false;
                if (typeof procesarExpulsionAjax === 'function') {
                    procesarExpulsionAjax(idGrupoParam);
                }
            }, 500);
        };
    });
}

function procesarExpulsionAjax(idGrupoParam) {
    fetch('expulsar_estudiantes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_trayecto_inicial=' + encodeURIComponent(idGrupoParam)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Proceso completado exitosamente", "exito", "mensajes-aqui");
            setTimeout(() => location.reload(), 2000);
        }
    });
}
</script>

    <script src="../script_bar_nav.js"></script>
    <script src="../mensajes.js"></script>
</body>
</html>