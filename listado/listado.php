<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Estudiantes</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../styles_bar_nav.css">
  <link rel="stylesheet" type="text/css" href="style_listado.css">
  <style>
 
   
  </style>
</head>
<body>
  <script src="../bar_nav.js"></script>
  <!-- Barra de Navegación Lateral -->
    <div class="container-slidebar">
       
    </div>
    <div class="main-content">
        <!-- Topbar -->
         <div class="container-topbar">

         </div>

        <div class="content">
          
            <h1>Listado de Estudiantes</h1>

            <!-- FILTROS -->
            <div class="filters">
                <div class="filter">
                    <label for="q">Búsqueda</label>
                    <input type="text" id="q" placeholder="Buscar por cédula o nombre">
                </div>

                <div class="filter">
                    <label for="carrera">Carrera</label>
                    <select id="carrera"></select>
                </div>

                <div class="filter">
                    <label for="trayecto_periodo">Trayecto / Periodo</label>
                    <select id="trayecto_periodo"></select>
                </div>

                <div class="filter">
                    <label for="grupo">Grupo</label>
                    <select id="grupo"></select>
                </div>

                <div class="filter">
                    <label for="estado">Estado</label>
                    <select id="estado"></select>
                </div>

                <button onclick="buscar()">Buscar</button>
                <button onclick="limpiarFiltros()">Limpiar</button>
            </div>

            <!-- TABLA -->
            <div class="container-students-table">
              <table class="students-table">
              <thead>
                <tr>
                  <th>Cédula</th>
                  <th>Nombre completo</th>
                  <th>Carrera</th>
                  <th>Trayecto-Periodo</th>
                  <th>Grupo</th>
                  
                </tr>
              </thead>
              <tbody id="students-body">
                <tr><td colspan="6">Cargando estudiantes...</td></tr>
              </tbody>
            </table>
             </div>

             <div id="pagination-container" style="text-align: center; margin-top: 20px; display: none;">
    <button id="btn-cargar-mas" onclick="cargarMas()" class="btn-load-more">
        <i class="fas fa-plus"></i> Cargar más estudiantes
    </button>
</div>
            
        </div>
     
    </div>

  

 <script>
/* --- Función Helper para Evitar ataques XSS --- */
function escapeHTML(str) {
    if (!str) return '--';
    return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}

/* --------------------- Cargar filtros --------------------- */
async function cargarFiltros() {
    try {
        const res = await fetch("api_filtro.php");
        const data = await res.json();

        if (!data.success || !data.filtros) {
            console.error("Error cargando filtros: estructura incorrecta");
            return;
        }

        // Carreras
        llenarSelect(data.filtros.carreras, "carrera", "carrera");

        // Trayecto + periodo
        llenarSelect(data.filtros.trayecto_periodo, "trayecto_periodo", "trayecto_periodo");

        // Grupos (normales)
        const gruposNormales = data.filtros.grupos.filter(g => !g.id_trayecto_inicial);
        llenarSelect(gruposNormales, "grupo", "grupo");

        // Grupos de trayecto inicial
        const gruposIniciales = data.filtros.grupos.filter(g => g.id_trayecto_inicial);
        llenarSelect(gruposIniciales, "grupo", "trayecto_inicial", true); // agregar después de los normales

        // Estados
        llenarSelect(data.filtros.estados, "estado", "estado");

    } catch (err) {
        console.error("Error cargando filtros", err);
    }
}

/* --------------------- Función para llenar select --------------------- */
function llenarSelect(items, id, tipo, append = false) {
    const select = document.getElementById(id);
    if (!append) select.innerHTML = '<option value="">-- Seleccione --</option>';

    if (!items || items.length === 0) return;

    items.forEach(item => {
        const option = document.createElement("option");

        switch(tipo) {
            case "carrera":
                option.value = item.id_carrera;
                option.textContent = item.nombre_carrera;
                break;
            case "trayecto_periodo":
                option.value = item.trayecto_periodo;
                option.textContent = item.trayecto_periodo;
                break;
            case "grupo":
                option.value = item.id_grupo;
                option.textContent = item.nombre_grupo;
                break;
            case "trayecto_inicial":
                option.value = `ti_${item.id_trayecto_inicial}`;
                option.textContent = item.nombre_grupo;
                break;
            case "estado":
                option.value = item.estado;
                option.textContent = item.nombre_estado;
                if(item.estado === 'activo') option.selected = true; // <-- Seleccion por defecto
                break;
        }

        select.appendChild(option);
    });
}

let paginaActual = 0;
const limitePorPagina = 30;

async function buscar(esCargarMas = false) {
    const tbody = document.getElementById('students-body');
    const paginationContainer = document.getElementById('pagination-container');
    
    // Si es una búsqueda nueva, reiniciamos la página y limpiamos la tabla
    if (!esCargarMas) {
        paginaActual = 0;
        tbody.innerHTML = `<tr><td colspan="5">Cargando...</td></tr>`;
    }

    const q = document.getElementById('q').value;
    const carrera = document.getElementById('carrera').value;
    const trayecto_periodo = document.getElementById('trayecto_periodo').value;
    const grupo = document.getElementById('grupo').value;
    const estado = document.getElementById('estado').value || 'activo';

    const url = new URL('sistema_gestion_aldea/listado/api_estudiantes.php', window.location.origin);
    url.searchParams.append('offset', paginaActual * limitePorPagina);
    url.searchParams.append('limit', limitePorPagina);
    
    if (q) url.searchParams.append('q', q);
    if (carrera) url.searchParams.append('carrera', carrera);
    if (trayecto_periodo) url.searchParams.append('trayecto_periodo', trayecto_periodo);
    if (grupo) url.searchParams.append('grupo', grupo);
    if (estado) url.searchParams.append('estado', estado);

    try {
        const res = await fetch(url);
        const data = await res.json();

        if (!data.success || !data.estudiantes.length) {
            if (!esCargarMas) tbody.innerHTML = '<tr><td colspan="5">No se encontraron estudiantes.</td></tr>';
            paginationContainer.style.display = 'none';
            return;
        }

       // ... Dentro de tu función async function buscar(esCargarMas = false) ...
// ... Justo donde haces el data.estudiantes.map(e => { ...

const htmlEstudiantes = data.estudiantes.map(e => {
    const urlFicha = `/sistema_gestion_aldea/estudiantes/estudiante.php?id=${e.id}`;
    
    const nombreEscapado = escapeHTML(e.nombre_completo);
    const carreraEscapada = escapeHTML(e.nombre_carrera);
    const trayectoEscapado = escapeHTML(e.trayecto_periodo);
    const grupoEscapado = escapeHTML(e.nombre_grupo);

    // --- LOGICA DE COLORES PARA ELIMINADOS ---
    const esTrayectoInicial = (e.nombre_carrera === 'Trayecto Inicial');

    // Evaluamos si el registro de la celda respectiva está marcado como eliminado
    const carreraEliminada  = esTrayectoInicial ? (e.estado_ti === 'eliminada') : (e.estado_carrera === 'eliminada');
    const trayectoEliminado = esTrayectoInicial ? (e.estado_ti === 'eliminada') : (e.estado_carrera === 'eliminada'); 
    const grupoEliminado    = esTrayectoInicial ? (e.estado_ti === 'eliminada') : (e.estado_grupo === 'eliminado');

    // Generamos los atributos style correspondientes
    const styleCarrera  = carreraEliminada  ? 'style="color: #dc3545; font-weight: 600;" title="Carrera Eliminada"' : '';
    const styleTrayecto = trayectoEliminado ? 'style="color: #dc3545; font-weight: 600;" title="Trayecto Eliminado"' : '';
    const styleGrupo    = grupoEliminado    ? 'style="color: #dc3545; font-weight: 600;" title="Grupo Eliminado"' : '';
    // -----------------------------------------

    const fotoHtml = e.foto 
        ? `<img src="/sistema_gestion_aldea/estudiantes/${e.foto}" alt="${nombreEscapado}" class="student-avatar-img" />`
        : `<div class="student-avatar-fallback">${nombreEscapado.substring(0,2).toUpperCase()}</div>`;

    return `
        <tr onclick="window.location='${urlFicha}'" style="cursor:pointer;">
            <td>${escapeHTML(e.cedula)}</td>
            <td>
                <div class="student-name-cell">
                    ${fotoHtml}
                    <span>${nombreEscapado}</span>
                </div>
            </td>
            <td ${styleCarrera}>${carreraEscapada}</td>
            <td ${styleTrayecto}>${trayectoEscapado}</td>
            <td ${styleGrupo}>${grupoEscapado}</td>
        </tr>
    `;
}).join('');

        if (esCargarMas) {
            // Quitamos el mensaje de "Cargando" si existiera y añadimos
            tbody.innerHTML += htmlEstudiantes;
        } else {
            tbody.innerHTML = htmlEstudiantes;
        }

        // Mostrar botón "Cargar más" si devuelve la misma cantidad del límite
        if (data.estudiantes.length === limitePorPagina) {
            paginationContainer.style.display = 'block';
        } else {
            paginationContainer.style.display = 'none';
        }

    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="5">Error cargando estudiantes.</td></tr>';
        console.error(err);
    }
}

function cargarMas() {
    paginaActual++;
    buscar(true);
}

/* --------------------- Limpiar filtros --------------------- */
function limpiarFiltros() {
    document.getElementById('q').value = '';
    document.getElementById('carrera').value = '';
    document.getElementById('trayecto_periodo').value = '';
    document.getElementById('grupo').value = '';
    document.getElementById('estado').value = 'activo';
    buscar();
}

/* --------------------- Inicializar y Eventos --------------------- */
document.addEventListener("DOMContentLoaded", () => {
    cargarFiltros();
    buscar();

    // 1. Evento de Búsqueda en tiempo real (Input texto)
    const inputBusqueda = document.getElementById('q');
    let timeoutBusqueda;

    inputBusqueda.addEventListener('input', () => {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            buscar();
        }, 500); // 500ms de Debounce
    });

    // 2. Evento de Búsqueda automática al cambiar los selects
    const selectsFiltros = ['carrera', 'trayecto_periodo', 'grupo', 'estado'];
    selectsFiltros.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.addEventListener('change', () => {
                buscar();
            });
        }
    });
});
</script>

<script src="../script_bar_nav.js"></script>
</body>
</html>
