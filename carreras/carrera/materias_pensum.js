// ESTE CODIGO CREA Y MODIFICA LAS MATERIAS DEL PENSUM, INCLUYE MENU CONTEXTUAL
let materias = [];
let trayectosInfo = [];
let materiaSeleccionadaId = null;
let filaSeleccionadaElemento = null;
const menuMateria = document.getElementById("contextMenuMateria");

// --- INYECCIÓN DEL MENÚ CONTEXTUAL (Aseguramos que exista) ---
if (!document.getElementById("contextMenuMateria")) {
  const menuHtml = `
       
        <div id="contextMenuMateria" class="context-menu-materia">
            <div class="context-menu-item" id="menu-materia-editar"><i class="fas fa-edit"></i> Cambiar Nombre</div>
            <div class="context-menu-item text-danger" id="menu-materia-eliminar">Eliminar Materia</div>
        </div>
    `;
  document.body.insertAdjacentHTML("beforeend", menuHtml);
}

document.addEventListener("DOMContentLoaded", async () => {
  const carreraId = window.carreraId;
  const modal = document.getElementById("modalMateria");
  const form = document.getElementById("formCrearMateria");
  const trayectoSelect = document.getElementById("trayectoMateria");
  const periodoSelect = document.getElementById("periodoMateria");
  const contenedorBotones = document.getElementById("botonesTrayectos");
  const listaMaterias = document.getElementById("listaMaterias");
  const menuMateriaObj = document.getElementById("contextMenuMateria");

  if (!carreraId) {
    console.error("carreraId no definido");
    if (listaMaterias)
      listaMaterias.innerHTML = "<p>Error: carreraId no definido.</p>";
    return;
  }

  const cerrarMenuContextual = () => {
    menuMateriaObj.classList.remove("show");
    setTimeout(() => {
      if (!menuMateriaObj.classList.contains("show"))
        menuMateriaObj.style.display = "none";
    }, 200);
    document.querySelectorAll(".fila-seleccionada").forEach((el) => {
      el.classList.remove("fila-seleccionada");
    });
  };

  // --- CARGAR TRAYECTOS ---
  async function cargarTrayectos() {
    try {
      const res = await fetch(
        `apis/api_obtener_trayectos.php?carrera_id=${carreraId}`,
      );
      const data = await res.json();
      if (!data.success)
        throw new Error(data.error || "Error cargando trayectos");

      trayectosInfo = data.trayectos || [];

      if (trayectoSelect) {
        trayectoSelect.innerHTML = trayectosInfo
          .map(
            (t) =>
              `<option value="${t.id_trayecto}">Trayecto ${t.numero}</option>`,
          )
          .join("");
        if (trayectosInfo.length > 0)
          await cargarPeriodos(trayectoSelect.value);
      }
      generarBotonesTrayectos();
    } catch (err) {
      console.error(err);
      if (contenedorBotones)
        contenedorBotones.innerHTML = "<p>Error cargando trayectos.</p>";
    }
  }

  // --- CARGAR PERIODOS ---
  async function cargarPeriodos(idTrayecto) {
    if (!periodoSelect) return;
    try {
      const res = await fetch(
        `api_get_periodos_por_trayecto.php?id_trayecto=${encodeURIComponent(idTrayecto)}`,
      );
      const data = await res.json();
      if (!data.success)
        throw new Error(data.error || "Error cargando periodos");
      periodoSelect.innerHTML = data.periodos
        .map(
          (p) =>
            `<option value="${p.id_periodo}">Periodo ${p.numero_periodo}</option>`,
        )
        .join("");
    } catch (err) {
      periodoSelect.innerHTML = '<option value="">Error</option>';
    }
  }

  if (trayectoSelect) {
    trayectoSelect.addEventListener("change", () =>
      cargarPeriodos(trayectoSelect.value),
    );
  }

  // --- GUARDAR MATERIA ---
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const payload = {
        id_carrera: Number(carreraId),
        id_trayecto: Number(trayectoSelect.value || 0),
        id_periodo: Number(periodoSelect.value || 0),
        nombre_materia: (
          document.getElementById("nombreMateria")?.value || ""
        ).trim(),
        codigo_materia: (
          document.getElementById("codigoMateria")?.value || ""
        ).trim(),
        creditos: Number(
          document.getElementById("creditosMateria")?.value || 0,
        ),
        horas_semanales: Number(
          document.getElementById("horasMateria")?.value || 0,
        ),
      };

      try {
        const res = await fetch("api_guardar_materia_pensum.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!data.success) {
          alert("No se pudo guardar la materia.", "error", "mensajes-aqui");
          return;
        }

        alert("Materia guardada correctamente", "exito", "mensajes-aqui");
        cargarMaterias();
      } catch (err) {
        alert("Error: " + err.message);
      }
    });
  }

  // --- CARGAR LISTADO DE MATERIAS ---
  window.cargarMaterias = async () => {
    try {
      const res = await fetch(
        `api_get_materias_pensum.php?id_carrera=${carreraId}`,
      );
      const data = await res.json();
      if (!data.success) throw new Error(data.error);
      materias = data.materias || [];
      marcarPrimerBotonSiExiste();
    } catch (err) {
      if (listaMaterias)
        listaMaterias.innerHTML = "<p>No hay materias registradas.</p>";
    }
  };

  function generarBotonesTrayectos() {
    if (!contenedorBotones) return;
    contenedorBotones.innerHTML = "";
    trayectosInfo.forEach((tray) => {
      const grupoTray = document.createElement("div");
      grupoTray.className = "trayecto-group";
      for (let per = 1; per <= tray.periodos; per++) {
        const btn = document.createElement("button");
        btn.className = "btn-trayecto";
        btn.textContent = `Trayecto ${tray.numero} - ${per}`;
        btn.dataset.numeroTrayecto = String(tray.numero);
        btn.dataset.numeroPeriodo = String(per);
        btn.onclick = () => filtrarMaterias(tray.numero, per, btn);
        grupoTray.appendChild(btn);
      }
      contenedorBotones.appendChild(grupoTray);
    });
  }

  // --- RENDERIZADO ---
  function mostrarMaterias(data) {
    if (!listaMaterias) return;
    if (!data || data.length === 0) {
      listaMaterias.innerHTML =
        '<p style="text-align:center; padding:20px;">No hay materias para este periodo.</p>';
      return;
    }

    listaMaterias.innerHTML = `
        <table class="tabla-materias">
            <thead>
                <tr>
                    <th>Nombre</th><th>Código</th><th>Trayecto</th><th>Periodo</th><th>Créditos</th><th>Horas</th>
                </tr>
            </thead>
            <tbody>
                ${data
                  .map(
                    (m) => `
                    <tr data-id="${m.id_materia}" class="fila-materia">
                        <td class="col-nombre">${escapeHtml(m.nombre_materia)}</td>
                        <td>${escapeHtml(m.codigo_materia || "-")}</td>
                        <td>${escapeHtml(m.numero_trayecto)}</td>
                        <td>${escapeHtml(m.numero_periodo)}</td>
                        <td>${escapeHtml(m.creditos)}</td>
                        <td>${escapeHtml(m.horas_semanales)}</td>
                    </tr>
                `,
                  )
                  .join("")}
            </tbody>
        </table>`;
  }

  // --- LÓGICA DEL MENÚ CONTEXTUAL (Exactamente como Grupos) ---
  document.addEventListener("contextmenu", function (e) {
    const target = e.target.closest(".fila-materia");

    if (target) {
      e.preventDefault();
      cerrarMenuContextual();

      filaSeleccionadaElemento = target;
      materiaSeleccionadaId = target.getAttribute("data-id");
      filaSeleccionadaElemento.classList.add("fila-seleccionada");

      menuMateriaObj.style.display = "block";

      const menuWidth = menuMateriaObj.offsetWidth || 180;
      const menuHeight = menuMateriaObj.offsetHeight || 100;
      const windowWidth = window.innerWidth;
      const windowHeight = window.innerHeight;

      let posX = e.pageX;
      let posY = e.pageY;

      // Ajuste de posición inteligente
      if (e.clientX + menuWidth > windowWidth) {
        posX -= menuWidth;
        menuMateriaObj.style.transformOrigin = "top right";
      } else {
        menuMateriaObj.style.transformOrigin = "top left";
      }

      if (e.clientY + menuHeight > windowHeight) {
        posY -= menuHeight;
        menuMateriaObj.style.transformOrigin =
          e.clientX + menuWidth > windowWidth ? "bottom right" : "bottom left";
      }

      menuMateriaObj.style.left = `${posX}px`;
      menuMateriaObj.style.top = `${posY}px`;

      requestAnimationFrame(() => {
        menuMateriaObj.classList.add("show");
      });
    } else {
      cerrarMenuContextual();
    }
  });

  document.addEventListener("click", (e) => {
    if (!menuMateriaObj.contains(e.target)) {
      cerrarMenuContextual();
    }
  });

  window.addEventListener("scroll", cerrarMenuContextual, true);

  // --- ACCIONES DEL MENÚ (Asignación de eventos) ---
  document
    .getElementById("menu-materia-editar")
    .addEventListener("click", () => {
      cerrarMenuContextual();
      window.iniciarRenombrar();
    });

  document
    .getElementById("menu-materia-eliminar")
    .addEventListener("click", () => {
      cerrarMenuContextual();
      window.ejecutarEliminarDesdeMenu();
    });

  function filtrarMaterias(numT, numP, btn) {
    const filtradas = materias.filter(
      (m) =>
        Number(m.numero_trayecto) == numT && Number(m.numero_periodo) == numP,
    );
    mostrarMaterias(filtradas);
    document
      .querySelectorAll(".btn-trayecto")
      .forEach((b) => b.classList.remove("activo"));
    if (btn) btn.classList.add("activo");
  }

  function marcarPrimerBotonSiExiste() {
    const first = document.querySelector(".btn-trayecto");
    if (first) first.click();
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

  // Inicialización
  await cargarTrayectos();
  await window.cargarMaterias();
});

/**
 * FUNCIONES GLOBALES Y HELPERS
 */

window.customConfirm = function (msg, titulo = "Confirmación") {
  return new Promise((resolve) => {
    let modal = document.createElement("div");
    Object.assign(modal.style, {
      position: "fixed",
      top: "0",
      left: "0",
      width: "100%",
      height: "100%",
      background: "rgba(0,0,0,0.4)",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      zIndex: "9999",
      opacity: "0",
      transition: "opacity 0.3s ease",
    });
    document.body.appendChild(modal);
    setTimeout(() => (modal.style.opacity = "1"), 10);

    let contenido = document.createElement("div");
    Object.assign(contenido.style, {
      background: "#fff",
      padding: "25px 35px",
      borderRadius: "12px",
      textAlign: "center",
      minWidth: "320px",
      maxWidth: "400px",
      boxShadow: "0 12px 30px rgba(0,0,0,0.2)",
      transform: "scale(0.8)",
      transition: "transform 0.3s ease",
    });
    modal.appendChild(contenido);
    setTimeout(() => (contenido.style.transform = "scale(1)"), 10);

    contenido.innerHTML = `
            <h3 style="margin-bottom:15px; font-family:sans-serif; color:#333;">${titulo}</h3>
            <p style="margin-bottom:20px; color:#555; line-height:1.5;">${msg}</p>
            <div style="display:flex; justify-content:center; gap:15px;">
                <button id="confirmYes" style="padding:10px 25px; border:none; border-radius:50px; background:#28a745; color:#fff; cursor:pointer; font-weight:600;">Sí, continuar</button>
                <button id="confirmNo" style="padding:10px 25px; border:none; border-radius:50px; background:#dc3545; color:#fff; cursor:pointer; font-weight:600;">Cancelar</button>
            </div>
        `;

    modal.querySelector("#confirmYes").onclick = () => {
      modal.style.opacity = "0";
      setTimeout(() => {
        modal.remove();
        resolve(true);
      }, 300);
    };
    modal.querySelector("#confirmNo").onclick = () => {
      modal.style.opacity = "0";
      setTimeout(() => {
        modal.remove();
        resolve(false);
      }, 300);
    };
  });
};

window.iniciarRenombrar = () => {
  if (!filaSeleccionadaElemento) return;

  const celdaNombre = filaSeleccionadaElemento.querySelector(".col-nombre");
  const nombreActual = celdaNombre.innerText;

  celdaNombre.innerHTML = `<input type="text" class="input-edit-inline" value="${nombreActual}" style="width:100%; padding:4px;">`;
  const input = celdaNombre.querySelector("input");

  input.focus();
  input.select();

  const finalizarEdicion = async (guardar) => {
    const nuevoNombre = input.value.trim();
    if (guardar && nuevoNombre !== "" && nuevoNombre !== nombreActual) {
      try {
        const res = await fetch("api_renombrar_materia.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            id_materia: materiaSeleccionadaId,
            nuevo_nombre: nuevoNombre,
          }),
        });
        const data = await res.json();
        if (data.success) {
          celdaNombre.innerText = nuevoNombre;
          const idx = materias.findIndex(
            (m) => m.id_materia == materiaSeleccionadaId,
          );
          if (idx !== -1) materias[idx].nombre_materia = nuevoNombre;
        } else {
          alert(data.error);
          celdaNombre.innerText = nombreActual;
        }
      } catch (e) {
        alert("Error de red");
        celdaNombre.innerText = nombreActual;
      }
    } else {
      celdaNombre.innerText = nombreActual;
    }
  };

  input.onkeydown = (e) => {
    if (e.key === "Enter") finalizarEdicion(true);
    if (e.key === "Escape") finalizarEdicion(false);
  };

  input.onblur = () => {
    // Pequeño delay por si se hizo click en el botón de guardar del menú
    setTimeout(() => {
      if (input.parentNode) finalizarEdicion(false);
    }, 150);
  };
};

window.ejecutarEliminarDesdeMenu = async () => {
  if (!materiaSeleccionadaId) return;

  const confirmado = await window.customConfirm(
    "¿Estás seguro de eliminar esta materia del pensum?",
    "Eliminar Materia",
  );
  if (!confirmado) return;

  try {
    const res = await fetch("api_eliminar_materia.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_materia: materiaSeleccionadaId }),
    });
    const data = await res.json();
    if (data.success) {
      alert("Materia eliminada correctamente", "exito", "mensajes-aqui");

      // Animación de salida (Lógica de Grupos)
      filaSeleccionadaElemento.style.transition =
        "opacity 0.3s, transform 0.3s";
      filaSeleccionadaElemento.style.opacity = "0";
      filaSeleccionadaElemento.style.transform = "scale(0.95)";
      setTimeout(async () => {
        await window.cargarMaterias();
      }, 300);
    } else {
      alert(data.error);
    }
  } catch (e) {
    alert("Error de conexión");
  }
};
