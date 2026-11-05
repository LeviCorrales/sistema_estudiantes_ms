document.addEventListener("DOMContentLoaded", () => {
  // --- VARIABLES Y SELECTORES ---
  const btnAbrirModal = document.getElementById("btnAbrirModalGrupo");
  const modalOverlay = document.getElementById("modal-overlay");
  const modal = document.getElementById("modalGrupo");
  const btnCerrarModal = document.getElementById("btnCerrarModalGrupo");
  const form = document.getElementById("formCrearGrupo");
  const listaGrupos = document.getElementById("listaGrupos");
  const errorBox = document.getElementById("formErrorGrupo");
  const body = document.querySelector("body");

  // Selectores para los filtros
  const filterSwitch = document.querySelector(".filter-switch"); // El contenedor
  const botonesFiltro = document.querySelectorAll(".btn-filtro");

  let estadoActual = "activo";

  botonesFiltro.forEach((boton) => {
    boton.addEventListener("click", () => {
      const nuevoEstado = boton.getAttribute("data-estado");

      if (nuevoEstado === estadoActual) return;

      // 1. Actualizar estado global
      estadoActual = nuevoEstado;

      // 2. Cambiar posición visual del slider
      // Primero quitamos cualquier estado anterior
      filterSwitch.classList.remove(
        "state-activo",
        "state-congelado",
        "state-egresado",
      );
      // Añadimos el nuevo estado (ej: state-congelado)
      filterSwitch.classList.add(`state-${nuevoEstado}`);

      // 3. Estilo visual de los botones (opcional si ya lo tienes)
      botonesFiltro.forEach((btn) => btn.classList.remove("active"));
      boton.classList.add("active");

      // 4. Cargar los datos
      cargarGrupos();
    });
  });

  // --- LÓGICA DEL MODAL ---
  btnAbrirModal?.addEventListener("click", () => {
    modalOverlay?.classList.add("active-modal-overlay");
    modal?.classList.add("active-modal-overlay");
    body.classList.add("active-modal-overlay");
    form?.reset();
    if (errorBox) errorBox.style.display = "none";
  });

  const cerrarModal = () => {
    modalOverlay?.classList.remove("active-modal-overlay");
    modal?.classList.remove("active-modal-overlay");
    body.classList.remove("active-modal-overlay");
  };

  btnCerrarModal?.addEventListener("click", cerrarModal);

  // --- CARGAR Y RENDERIZAR GRUPOS ---
  async function cargarGrupos() {
    if (!listaGrupos) return;

    listaGrupos.innerHTML =
      '<p style="text-align:center; padding:20px;">Buscando grupos...</p>';

    try {
      const url = `apis/api_get_grupos.php?carrera_id=${encodeURIComponent(carreraId)}&estado=${estadoActual}`;
      const res = await fetch(url);

      if (!res.ok) throw new Error("Error de red");
      const data = await res.json();

      if (data.success && data.grupos.length > 0) {
        mostrarLista(data.grupos);
      } else {
        listaGrupos.innerHTML = `<p style="text-align:center; padding:20px;">No hay grupos con estado: <strong>${estadoActual}</strong>.</p>`;
      }
    } catch (e) {
      console.error(e);
      listaGrupos.textContent = "Error cargando grupos.";
    }
  }

  function mostrarLista(grupos) {
    listaGrupos.innerHTML = grupos
      .map(
        (g) => `
            <a class="grupo-card border-estado-${g.estado}" href="grupo/grupo.php?id=${encodeURIComponent(g.id_grupo)}" data-id="${g.id_grupo}">
                <strong class="grupo-nombre">${escapeHtml(g.nombre_grupo)}</strong>
                <p>Trayecto ${escapeHtml(g.trayecto || "?")} - Periodo ${escapeHtml(g.periodo || "?")}</p>
                <span class="badge-estado">${escapeHtml(g.estado)}</span>
            </a>
        `,
      )
      .join("");
  }

  function escapeHtml(str) {
    if (!str) return "";
    return String(str).replace(
      /[&<>"']/g,
      (m) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[m],
    );
  }

  // --- GUARDAR NUEVO GRUPO ---
  form?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nombreGrupo = form.nombre_grupo.value.trim();

    if (nombreGrupo.length < 2) {
      alert("El nombre es muy corto", "error", "mensajes-aqui");
      return;
    }

    const dataToSend = {
      carrera_id: carreraId,
      nombre_grupo: nombreGrupo,
      id_trayecto: 0,
      id_periodo: 0,
    };

    try {
      const res = await fetch("create_group.php", {
        // Ajustada la ruta a tu API
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dataToSend),
      });
      const data = await res.json();

      if (data.success) {
        cerrarModal();
        alert("Grupo creado correctamente", "exito", "mensajes-aqui");
        cargarGrupos();
      } else {
        alert(data.error || "Error al crear", "error", "mensajes-aqui");
      }
    } catch (err) {
      console.error(err);
      alert("Error de conexión");
    }
  });

  // Inicializar lista al cargar
  cargarGrupos();
});
