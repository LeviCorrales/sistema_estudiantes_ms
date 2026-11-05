document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalAgregar');
    const modalOverlay = document.getElementById('modal-overlay');
    const body = document.querySelector('.body');
    const btn = document.getElementById('btnAgregar');
    const span = document.getElementById('cerrarModal');
    const inputBuscar = document.getElementById('buscarEstudiante');
    const lista = document.getElementById('lista-estudiantes');

    // Extraer dinámicamente el ID del grupo de la URL de la página actual
    const urlParams = new URLSearchParams(window.location.search);
    const idGrupoActual = urlParams.get('id') || urlParams.get('id_grupo') || 0;

    btn.addEventListener("click", () => {
        modal.classList.add("active-modal-overlay");
        modalOverlay.classList.add("active-modal-overlay");
        body.classList.add("active-modal-overlay");
    });

    span.addEventListener("click", () => {
        modal.classList.remove("active-modal-overlay");
        modalOverlay.classList.remove("active-modal-overlay");
        body.classList.remove("active-modal-overlay");
    });

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
                        // Confirmación visual
                        const confirmado = await customConfirm(`¿Agregar a ${nombreCompleto} al grupo?`);
                        if (!confirmado) return;

                        try {
                            const res = await fetch('api_agregar_estudiante_grupo.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                // Usamos la variable JS del ID del grupo en lugar de la etiqueta PHP
                                body: `id_grupo=${idGrupoActual}&id_estudiante=${est.id}`
                            });

                            // Convertir a JSON para que la alerta sepa si hubo éxito o error
                            const dataRespuesta = await res.json();
                            
                            if (dataRespuesta.success) {
                                // Alerta de éxito controlada en JS
                                alert(dataRespuesta.message, 'exito', 'mensajes-aqui');

                                // Recargar página después de 2 segundos para ver los cambios
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                // Alerta de error usando el texto enviado por PHP
                                alert(dataRespuesta.error, 'error', 'mensajes-aqui');
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
});