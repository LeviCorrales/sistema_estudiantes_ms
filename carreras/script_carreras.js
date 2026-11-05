// script.js - Gestión de modal, contexto y acciones de cards
document.addEventListener('DOMContentLoaded', function() {

   const filterSwitch = document.getElementById('status-filter-switch');
const buttons = filterSwitch.querySelectorAll('.switch-btn');
const menu = document.getElementById('context-menu');
const menuEstadoBtn = document.getElementById('menu-estado');

let selectedId = null;
let selectedCard = null;
let tipoActual = '';

// ------------------- 0. LÓGICA DE FILTRADO -------------------
const filtrarCards = (filtro) => {
    const careerCards = document.querySelectorAll('.career-card');

    careerCards.forEach(container => {
        const article = container.querySelector('.card');
        if (!article) return;

        const estaInhabilitada = article.classList.contains('card-inhabilitada');

        if (filtro === 'activas') {
            container.style.display = estaInhabilitada ? 'none' : 'block';
        } else {
            // Mostrar solo si está inhabilitada
            container.style.display = estaInhabilitada ? 'block' : 'none';
        }
    });
};

// Ejecución inicial
filtrarCards('activas');

buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        const filtro = btn.getAttribute('data-filter');

        // Manejo de clases activas en los botones
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Cambio visual del switch
        if (filtro === 'inactivas') {
            filterSwitch.classList.add('show-inactivas');
        } else {
            filterSwitch.classList.remove('show-inactivas');
        }

        // Ejecutar el filtrado
        filtrarCards(filtro);
    });
});

   
// ------------------- 1. MENÚ CONTEXTUAL -------------------
    document.addEventListener('contextmenu', function(e) {
        const card = e.target.closest('.card');

        if (card) {
            e.preventDefault();
            document.querySelectorAll('.card').forEach(c => c.classList.remove('context-open'));
            selectedCard = card;
            selectedCard.classList.add('context-open'); 
            
            selectedId = card.getAttribute('data-id');
            tipoActual = card.getAttribute('data-tipo') || 'carrera'; 

            // Visibilidad según tipo
            menuEstadoBtn.style.display = (tipoActual === 'trayecto') ? 'none' : 'block';

            // Posicionamiento
            menu.classList.add('active');
            
            let posX = e.clientX;
            let posY = e.clientY;

            // Ajuste simple para no salirse de pantalla
            if (posX + menu.offsetWidth > window.innerWidth) posX -= menu.offsetWidth;
            if (posY + menu.offsetHeight > window.innerHeight) posY -= menu.offsetHeight;

            menu.style.left = posX + 'px';
            menu.style.top = posY + 'px';
        } else {
            closeContextMenu();
        }
    });

    function closeContextMenu() {
        menu.classList.remove('active');
        
        if (selectedCard) selectedCard.classList.remove('context-open');
    }

    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target)) closeContextMenu();
    });

// ------------------- 2. ACCIONES DEL MENÚ -------------------

    document.getElementById('menu-editar').addEventListener('click', () => {
        const h3 = selectedCard.querySelector('h3');
        const nombreOriginal = h3.innerText;
        
        // FIX 1: Capturamos los valores actuales en constantes locales
        const idAEditar = selectedId;
        const tipoAEditar = tipoActual;
        
        let editando = false;
        const input = document.createElement('input');
        input.type = 'text';
        input.value = nombreOriginal;
        input.className = 'edit-inline-input';

        h3.replaceWith(input);
        input.focus();
        closeContextMenu();

        const finalizarEdicion = (guardar) => {
            if (editando) return;
            const nuevoNombre = input.value.trim();
            
            const restaurarH3 = (texto) => {
                const nuevoH3 = document.createElement('h3');
                nuevoH3.className = 'career-title';
                nuevoH3.innerText = texto;
                input.replaceWith(nuevoH3);
            };

            if (guardar && nuevoNombre !== "" && nuevoNombre !== nombreOriginal) {
                editando = true;
                ejecutarAccionGeneral('editar', {
                    id: idAEditar,        // Usamos la constante capturada
                    nombre: nuevoNombre,
                    tipo: tipoAEditar     // Usamos la constante capturada
                }, (res) => {
                    restaurarH3(nuevoNombre);
                    alert("Nombre actualizado correctamente", "exito", "mensajes-aqui");
                });
            } else {
                restaurarH3(nombreOriginal);
            }
        };

        input.onkeydown = (e) => {
            if (e.key === 'Enter') finalizarEdicion(true);
            if (e.key === 'Escape') finalizarEdicion(false);
        };
        input.onblur = () => setTimeout(() => finalizarEdicion(true), 150);
    });

    // Acción Estado (Carreras)
    menuEstadoBtn.addEventListener('click', () => {
        if (!selectedCard || tipoActual === 'trayecto') return;
        const idEstado = selectedId; // Captura

        ejecutarAccionGeneral('estado', { id: idEstado, tipo: 'carrera' }, (res) => {
            closeContextMenu();
            const ahoraInactiva = selectedCard.classList.toggle('card-inhabilitada');
            const indicador = selectedCard.querySelector('.status-badge');
            if (indicador) indicador.innerText = ahoraInactiva ? 'inhabilitada' : 'activa';
            
            // Re-filtrar para que la card desaparezca si el switch no coincide
            const filtroActivo = filterSwitch.querySelector('.switch-btn.active').getAttribute('data-filter');
            filtrarCards(filtroActivo);
        });
    });

    // Acción Eliminar
    document.getElementById('menu-eliminar').addEventListener('click', async () => {
        const idEliminar = selectedId;
        const tipoEliminar = tipoActual;
        const nombre = selectedCard.querySelector('h3').innerText;
        closeContextMenu();

        const confirmado = await window.customConfirm(`¿Eliminar "${nombre}"?`, "Confirmar");
        if (confirmado) {
            ejecutarAccionGeneral('eliminar', { id: idEliminar, tipo: tipoEliminar }, () => {
                const container = selectedCard.closest('.career-card');
                container.style.opacity = '0';
                setTimeout(() => container.remove(), 300);
            });
        }
    });

    // ------------------- 3. COMUNICACIÓN BACKEND -------------------

    function ejecutarAccionGeneral(accion, datos, callback) {
        const formData = new FormData();
        formData.append('accion', accion);
        for (let key in datos) formData.append(key, datos[key]);

        fetch('acciones_generales.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) callback(res);
            else alert(res.error || "Error en la operación", "error", "mensajes-aqui");
        })
        .catch(err => alert("Error crítico de conexión", "error", "mensajes-aqui"));
    }

    // ------------------- 4. FORMATEO AUTOMÁTICO (CAPITALIZE) -------------------

    const inputsNombres = ['nombre', 'trayecto-inicial-input', 'nombre_materia_input'];

    inputsNombres.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', (e) => {
                let cursorPosition = e.target.selectionStart;
                const transformedValue = e.target.value.replace(/(^[\p{L}]|\s[\p{L}])/gu, (match) => {
                    return match.toUpperCase();
                });
                e.target.value = transformedValue;
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
        }
    });

   // ------------------- GESTIÓN DE MODAL Y CREACIÓN -------------------
const btnCrear = document.getElementById('btnCrear');
const modal = document.getElementById('modal');
const modalOverlay = document.getElementById('modal-overlay');
const btnCerrar = document.getElementById('btnCerrar');
const form = document.getElementById('formCrear');
const lista = document.getElementById('lista-carreras');
const bodyBody = document.querySelector('.body');

// Abrir Modal
btnCrear.addEventListener('click', () => {
    modal.classList.add('abrir-modal-overlay');
    modalOverlay.classList.add('abrir-modal-overlay');
    bodyBody.classList.add('abrir-modal-overlay');
});

// Cerrar Modal
const cerrarModal = () => {
    modal.classList.remove('abrir-modal-overlay');
    modalOverlay.classList.remove('abrir-modal-overlay');
    bodyBody.classList.remove('abrir-modal-overlay');
    form.reset();
};

btnCerrar.addEventListener('click', cerrarModal);

// Procesar Formulario
form.addEventListener('submit', function(e) {
    e.preventDefault();

    const nombreInput = form.querySelector('[name="nombre_carrera"]');
    const nombre = nombreInput.value.trim();
    const regexSoloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;

    // Validaciones
    if (nombre.length < 3 || nombre.length > 20) {
        alert("El nombre debe tener entre 3 y 20 caracteres", "error", "mensajes-aqui");
        return;
    }
    if (!regexSoloLetras.test(nombre)) {
        alert("Solo se permiten letras y espacios", "error", "mensajes-aqui");
        return;
    }

    const formData = new FormData(form);

    fetch('carrera/create_career.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const c = data.carrera;
            
            // Creamos el contenedor principal
            const divCarrera = document.createElement('div');
            divCarrera.className = 'career-card';

            // Creamos la estructura completa de la card para que coincida con PHP
            divCarrera.innerHTML = `
                <article class="card" data-id="${c.id_carrera}" data-tipo="carrera">
                    <a class="card-link career-header" href="carrera/carrera.php?id=${encodeURIComponent(c.id_carrera)}">
                        <h3 class="career-title">${escapeHtml(c.nombre_carrera)}</h3>
                        <p class="muted career-id">
                            Estado: <span class="status-badge">${escapeHtml(c.estado)}</span>
                        </p>
                        <div class="career-stats">
                            <div class="stat-item">
                                <span class="stat-label">Grupos</span>
                                <span class="stat-value">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Estudiantes</span>
                                <span class="stat-value">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Trayectos</span>
                                <span class="stat-value">0</span>
                            </div>
                        </div>
                    </a>
                </article>
            `;

            // Insertamos al principio de la lista
            lista.insertBefore(divCarrera, lista.firstChild);
            
            cerrarModal();
            alert("Carrera creada exitosamente", "exito", "mensajes-aqui");
            
            // NOTA: Quitamos el reload para que el alert sea visible y la fluidez sea total.
        } else {
            alert(data.error || "Error al crear la carrera", "error", "mensajes-aqui");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Error de conexión con el servidor", "error", "mensajes-aqui");
    });
});

// Función para evitar inyecciones XSS en el HTML dinámico
function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
}
});