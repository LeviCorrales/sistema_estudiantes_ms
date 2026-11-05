document.addEventListener('DOMContentLoaded', () => {
// TODO DEL MENU CONTEXTUAL 
// --- VARIABLES GLOBALES DEL MENÚ ---
let selectedGroupId = null;
let selectedGroupCard = null;

// --- INYECCIÓN DINÁMICA (Primero inyectamos, luego seleccionamos) ---
if (!document.getElementById('context-menu-grupo')) {
    const menuHtml = `
        <div id="context-menu-grupo" style="display:none;">
            <div class="context-menu-item" id="menu-grupo-editar"><i class="fas fa-edit"></i> Cambiar Nombre</div>
            <div class="context-menu-item" id="menu-grupo-congelar"><i class="fas fa-snowflake"></i> Congelar Grupo</div>
            <div class="context-menu-divider"></div>
            <div class="context-menu-item text-danger" id="menu-grupo-eliminar"><i class="fas fa-trash"></i> Eliminar Grupo</div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', menuHtml);
}

const menuGrupo = document.getElementById('context-menu-grupo');

// --- 3. FUNCIONES DE CONTROL DEL MENÚ ---
const cerrarMenuContextual = () => {
    if (!menuGrupo) return;
    menuGrupo.classList.remove('show');
    setTimeout(() => { 
        if(!menuGrupo.classList.contains('show')) menuGrupo.style.display = 'none'; 
    }, 200);
    
    document.querySelectorAll('.active-context-selection').forEach(el => {
        el.classList.remove('active-context-selection');
    });
};

// Evento para abrir el menú (Click Derecho)
document.addEventListener('contextmenu', function (e) {
    const target = e.target.closest('.grupo-card');

    if (target) {
    e.preventDefault();
    cerrarMenuContextual();

    selectedGroupCard = target;
    selectedGroupId = target.getAttribute('data-id');
    selectedGroupCard.classList.add('active-context-selection');

    // --- NUEVA LÓGICA: Detectar estado ---
    const btnCongelar = document.getElementById('menu-grupo-congelar');
    
    // Comprobamos si la tarjeta tiene la clase de congelado
    // (Asegúrate de que 'congelado' coincida con lo que viene de tu base de datos)
    const estaCongelado = selectedGroupCard.classList.contains('border-estado-congelado');

    if (estaCongelado) {
        btnCongelar.innerHTML = '<i class="fas fa-sun"></i> Activar Grupo';
        btnCongelar.dataset.accionActual = 'activar'; // Usamos dataset para saber qué hacer luego
    } else {
        btnCongelar.innerHTML = '<i class="fas fa-snowflake"></i> Congelar Grupo';
        btnCongelar.dataset.accionActual = 'congelar';
    }
    // -------------------------------------

    menuGrupo.style.display = 'block';

        const menuWidth = menuGrupo.offsetWidth || 220;
        const menuHeight = menuGrupo.offsetHeight || 150;
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        let posX = e.pageX;
        let posY = e.pageY;

        // Ajuste de Viewport (Evitar que se salga de la pantalla)
        if (e.clientX + menuWidth > windowWidth) posX -= menuWidth;
        if (e.clientY + menuHeight > windowHeight) posY -= menuHeight;

        menuGrupo.style.left = `${posX}px`;
        menuGrupo.style.top = `${posY}px`;

        requestAnimationFrame(() => {
            menuGrupo.classList.add('show');
        });
    } else {
        cerrarMenuContextual();
    }
});

// Cerrar al hacer click fuera o scroll
document.addEventListener('click', (e) => {
    if (!menuGrupo.contains(e.target)) cerrarMenuContextual();
});
window.addEventListener('scroll', cerrarMenuContextual, true);

// --- ACCIONES DEL MENÚ ---

// ACCIÓN: CONGELAR
// Sustituye tu actual event listener de 'menu-grupo-congelar' por este:
document.getElementById('menu-grupo-congelar').addEventListener('click', async () => {
    if (!selectedGroupCard || !selectedGroupId) return;
    
    const btn = document.getElementById('menu-grupo-congelar');
    const accion = btn.dataset.accionActual; // 'activar' o 'congelar'
    const nombre = selectedGroupCard.querySelector('.grupo-nombre').innerText;
    
    cerrarMenuContextual();

    // Personalizamos el mensaje de confirmación
    const mensaje = accion === 'activar' 
        ? `¿Deseas reactivar el grupo "${nombre}"?` 
        : `¿Estás seguro de congelar el grupo "${nombre}"?`;

    const confirmado = window.customConfirm 
        ? await window.customConfirm(mensaje, accion === 'activar' ? "Activar" : "Congelar")
        : confirm(mensaje);

    if (confirmado) {
        // Enviamos la acción dinámica ('congelar' o 'activar')
        const res = await ejecutarAccionGrupo(accion, { id: selectedGroupId });
        
        if (res.success) {
            // Como el estado cambió, el grupo ya no pertenece a la vista actual del filtro
            // Animamos la salida para que desaparezca de la lista
            animarSalidaCard(selectedGroupCard, `Grupo ${accion === 'activar' ? 'activado' : 'congelado'} correctamente`);
        }
    }
});
// ACCIÓN: EDITAR NOMBRE
document.getElementById('menu-grupo-editar').addEventListener('click', () => {
    if (!selectedGroupCard) return;
    cerrarMenuContextual();

    const textElement = selectedGroupCard.querySelector('.grupo-nombre');
    if (!textElement || textElement.tagName === 'INPUT') return;

    const nombreOriginal = textElement.innerText;
    const input = document.createElement('input');
    input.type = 'text';
    input.value = nombreOriginal;
    input.className = 'edit-inline-input';

    input.onclick = (e) => { e.stopPropagation(); e.preventDefault(); };
    textElement.replaceWith(input);
    input.select();
    input.focus();

    let isSaving = false;
    const finalizarEdicion = async (guardar) => {
        if (isSaving) return;
        const nuevoNombre = input.value.trim();

        if (guardar && nuevoNombre !== "" && nuevoNombre !== nombreOriginal) {
            isSaving = true;
            input.disabled = true;
            const res = await ejecutarAccionGrupo('editar', { id: selectedGroupId, nombre: nuevoNombre });
            
            if (res.success) {
                textElement.innerText = nuevoNombre;
                input.replaceWith(textElement);
            } else {
                textElement.innerText = nombreOriginal;
                input.replaceWith(textElement);
            }
        } else {
            input.replaceWith(textElement);
        }
    };

    input.onkeydown = (e) => {
        if (e.key === 'Enter') finalizarEdicion(true);
        if (e.key === 'Escape') finalizarEdicion(false);
    };
    input.onblur = () => setTimeout(() => { if(input.parentNode) finalizarEdicion(true); }, 150);
});

// ACCIÓN: ELIMINAR
document.getElementById('menu-grupo-eliminar').addEventListener('click', async () => {
    if (!selectedGroupCard || !selectedGroupId) return;
    cerrarMenuContextual();

    const nombre = selectedGroupCard.querySelector('.grupo-nombre').innerText;
    const confirmado = window.customConfirm 
        ? await window.customConfirm(`¿Eliminar definitivamente el grupo "${nombre}"?`, "Eliminar Grupo")
        : confirm(`¿Eliminar el grupo "${nombre}"?`);

    if (confirmado) {
        const res = await ejecutarAccionGrupo('eliminar', { id: selectedGroupId });
        if (res.success) {
            animarSalidaCard(selectedGroupCard, "Grupo eliminado correctamente");
        }
    }
});

// --- 5. HELPERS (Funciones de apoyo) ---

async function ejecutarAccionGrupo(accion, datos) {
    const formData = new FormData();
    formData.append('accion', accion);
    for (let key in datos) formData.append(key, datos[key]);

    try {
        const r = await fetch('acciones_grupo.php', { method: 'POST', body: formData });
        const res = await r.json();
        
        if (!res.success) {
            if(window.showAlert) showAlert(res.error || "Error", "error", "mensajes-aqui");
            else alert(res.error || "Error en la operación");
        }
        return res;
    } catch (e) {
        console.error(e);
        if(window.showAlert) showAlert("Error de conexión", "error", "mensajes-aqui");
        return { success: false };
    }
}

function animarSalidaCard(card, mensaje) {
    card.style.transition = 'all 0.4s ease';
    card.style.opacity = '0';
    card.style.transform = 'scale(0.9) translateY(-10px)';
    
    if(window.showAlert) showAlert(mensaje, "exito", "mensajes-aqui");
    
    setTimeout(() => {
        card.remove();
        // Opcional: si la lista queda vacía, recargar para mostrar mensaje de "No hay grupos"
        if(document.querySelectorAll('.grupo-card').length === 0) cargarGrupos();
    }, 400);
}
})
    