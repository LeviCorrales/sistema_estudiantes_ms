document.addEventListener("DOMContentLoaded", () => {
  const contenedor = document.getElementById("lista-estudiantes-grupo");

  if (!contenedor) return;

  const idGrupo = Number(contenedor.dataset.idGrupo);
  let menuActual = null;

  // Cierra el menú contextual si existe.
  function cerrarMenu() {
    if (menuActual) {
      menuActual.remove();
      menuActual = null;
    }
  }

  // Mostrar el menú al hacer clic derecho sobre un estudiante.
  contenedor.addEventListener("contextmenu", (evento) => {
    const fila = evento.target.closest(".fila-estudiante-grupo");

    if (!fila) return;

    evento.preventDefault();
    cerrarMenu();

    const idEstudiante = Number(fila.dataset.idEstudiante);
    const nombreCompleto = fila.dataset.nombreEstudiante;

    const menu = document.createElement("div");
    menu.className = "menu-contextual-estudiante";

    const botonEliminar = document.createElement("button");
    botonEliminar.type = "button";
    botonEliminar.textContent = "Eliminar del grupo";

    menu.appendChild(botonEliminar);
    document.body.appendChild(menu);

    // Evitar que el menú se salga de los bordes de la pantalla.
    const margen = 8;
    const ancho = menu.offsetWidth;
    const alto = menu.offsetHeight;

    const izquierda = Math.max(
      margen,
      Math.min(evento.clientX, window.innerWidth - ancho - margen),
    );

    const arriba = Math.max(
      margen,
      Math.min(evento.clientY, window.innerHeight - alto - margen),
    );

    menu.style.left = `${izquierda}px`;
    menu.style.top = `${arriba}px`;

    menuActual = menu;

    botonEliminar.addEventListener("click", async () => {
      cerrarMenu();

      // Confirmación personalizada existente en tu sistema.
      const confirmado = await customConfirm(
        `¿Seguro que deseas eliminar a ${nombreCompleto} de este grupo? Su historial académico se conservará.`,
      );

      if (!confirmado) return;

      botonEliminar.disabled = true;

      try {
        const respuesta = await fetch("api_eliminar_estudiante_grupo.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            id_grupo: String(idGrupo),
            id_estudiante: String(idEstudiante),
          }),
        });

        const data = await respuesta.json();

        if (respuesta.ok && data.success) {
          alert(data.message, "exito", "mensajes-aqui");

          // Actualizar la lista tras mostrar el mensaje.
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          alert(
            data.error || "No se pudo eliminar al estudiante.",
            "error",
            "mensajes-aqui",
          );
        }
      } catch (error) {
        console.error("Error al eliminar al estudiante del grupo:", error);

        alert("Error de conexión con el servidor.", "error", "mensajes-aqui");
      } finally {
        botonEliminar.disabled = false;
      }
    });
  });

  // Cerrar al hacer clic fuera del menú.
  document.addEventListener("click", (evento) => {
    if (menuActual && !menuActual.contains(evento.target)) {
      cerrarMenu();
    }
  });

  // Cerrar al pulsar Escape.
  document.addEventListener("keydown", (evento) => {
    if (evento.key === "Escape") {
      cerrarMenu();
    }
  });

  // Cerrar al desplazarse por la página.
  window.addEventListener("scroll", cerrarMenu, true);
});
