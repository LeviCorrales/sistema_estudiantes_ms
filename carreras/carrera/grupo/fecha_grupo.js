document.addEventListener('DOMContentLoaded', function () {

    /**
     * 1. FUNCIÓN MAESTRA PARA ACCIONES DE GRUPO
     * Maneja el flujo: Confirmar -> Deshabilitar -> Fetch -> Alert -> Recargar
     */
    async function ejecutarAccionGrupo(btnId, url, mensajeConfirm, mensajeExito) {
        const btn = document.getElementById(btnId);
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const idGrupo = parseInt(btn.dataset.id);
            if (!idGrupo) {
                showAlert('ID de grupo no válido', 'error', 'mensajes-aqui');
                return;
            }

            // Confirmación con tu función customConfirm
            const confirmado = await customConfirm(mensajeConfirm);
            if (!confirmado) return;

            // Feedback visual: Bloquear botón para evitar doble clic
            const contenidoOriginal = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_grupo: idGrupo })
                });

                // Capturamos el texto primero por si el PHP devuelve un error de texto (HTML)
                const text = await res.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("Respuesta no válida del servidor:", text);
                    throw new Error("El servidor no devolvió un JSON válido. Revisa la consola.");
                }

                if (data.success) {
                    showAlert(mensajeExito, 'exito', 'mensajes-aqui');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('Error: ' + (data.error || 'Desconocido'), 'error', 'mensajes-aqui');
                    // Restaurar botón solo si falló para permitir reintento
                    btn.disabled = false;
                    btn.innerHTML = contenidoOriginal;
                }

            } catch (err) {
                console.error("Error en Fetch:", err);
                showAlert(err.message || 'Error de conexión con el servidor', 'error', 'mensajes-aqui');
                btn.disabled = false;
                btn.innerHTML = contenidoOriginal;
            }
        });
    }

    /**
     * 2. FUNCIÓN PARA MOSTRAR ALERTAS (Estilo Bootstrap)
     */
    function showAlert(mensaje, tipo, contenedorId) {
        const contenedor = document.getElementById(contenedorId);
        if (!contenedor) {
            console.warn("No se encontró el contenedor de mensajes:", contenedorId);
            return;
        }

        const div = document.createElement('div');
        const colorClass = (tipo === 'exito' || tipo === 'success') ? 'alert-success' : 'alert-danger';
        const icono = (tipo === 'exito' || tipo === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';

        div.className = `alert ${colorClass} alert-dismissible fade show shadow-sm`;
        div.role = 'alert';
        div.innerHTML = `
            <i class="fas ${icono} me-2"></i> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        contenedor.appendChild(div);

        // Auto-eliminar después de 4 segundos
        setTimeout(() => {
            if (div) {
                div.classList.remove('show');
                setTimeout(() => div.remove(), 300);
            }
        }, 4000);
    }

    /**
     * 3. INICIALIZACIÓN DE BOTONES
     */
    
    // Botón de Egresar
    ejecutarAccionGrupo(
        'btnEgresar', 
        'egresar_grupo.php', 
        '¡Atención! ¿Estás seguro de egresar a este grupo? Todos los estudiantes cambiarán su estatus a EGRESADO.',
        '¡El grupo ha egresado exitosamente!'
    );

    // Botón de Avanzar
    ejecutarAccionGrupo(
        'btnAvanzar', 
        'advance_periodo.php', 
        '¿Deseas avanzar este grupo al siguiente periodo?',
        '¡Periodo actualizado correctamente!'
    );

    // Botón de Retroceder
    ejecutarAccionGrupo(
        'btnRetroceder', 
        'retroceder_periodo.php', 
        '¿Deseas retroceder este grupo al periodo anterior?',
        'El grupo ha vuelto al periodo anterior.'
    );

});