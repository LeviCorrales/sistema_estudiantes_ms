document.addEventListener("DOMContentLoaded", () => {
  // --- VARIABLES DE ELEMENTOS DOM ---
  const numTrayectosInput = document.getElementById("numTrayectos");
  const btnGenerar = document.getElementById("btnGenerar");
  const trayectosContainer = document.getElementById("trayectosContainer");
  const form = document.getElementById("formTrayectos");
  const listaTrayectos = document.getElementById("listaTrayectos");

  const btnAbrirModal = document.getElementById("btnAbrirModal");
  const modalOverlay = document.getElementById("modal-overlay");
  const modal = document.getElementById("modalTrayectos");
  const body = document.querySelector(".body");
  const btnCerrarModal = document.getElementById("btnCerrarModal");

  // carreraId se inyecta en el HTML como variable global por PHP
  // const carreraId = ...

  // --- 1. GESTIÓN DEL MODAL ---
  btnAbrirModal.addEventListener("click", () => {
    modalOverlay.classList.add("active-modal-overlay");
    modal.classList.add("active-modal-overlay");
    body.classList.add("active-modal-overlay");

    // Limpiar formulario y lista inputs trayectos
    form.reset();
    trayectosContainer.innerHTML = "";
  });

  btnCerrarModal.addEventListener("click", () => {
    modalOverlay.classList.remove("active-modal-overlay");
    modal.classList.remove("active-modal-overlay");
    body.classList.remove("active-modal-overlay");
  });

  // --- 2. GENERACIÓN DINÁMICA DE INPUTS ---
  btnGenerar.addEventListener("click", () => {
    const cantidad = parseInt(numTrayectosInput.value);
    trayectosContainer.innerHTML = "";

    if (isNaN(cantidad) || cantidad < 1) {
      alert("Ingresa un número válido de trayectos", "error", "mensajes-aqui");
      return;
    }

    for (let i = 1; i <= cantidad; i++) {
      const div = document.createElement("div");
      div.className = "trayecto-periodos";

      div.innerHTML = `
                <label>Trayecto ${i}</label>
                <input type="number" min="1" required name="periodos_${i}" placeholder="Nº de Periodos" />
            `;

      trayectosContainer.appendChild(div);
    }
  });

  // --- 3. RENDERIZADO Y CARGA DE DATOS ---
  function mostrarLista(trayectos) {
    if (!trayectos || trayectos.length === 0) {
      listaTrayectos.textContent = "No hay trayectos definidos aún.";
      return;
    }

    let html = "";
    trayectos.forEach((t) => {
      for (let p = 1; p <= t.periodos; p++) {
        html += `<div>Trayecto ${t.numero}-${p}</div>`;
      }
    });

    listaTrayectos.innerHTML = html;
  }

  async function cargarTrayectos() {
    try {
      const res = await fetch(
        `apis/api_obtener_trayectos.php?carrera_id=${encodeURIComponent(carreraId)}`,
      );
      if (!res.ok) throw new Error("Error cargando trayectos");

      const data = await res.json();

      if (data.success && data.trayectos.length > 0) {
        mostrarLista(data.trayectos);
        // OCULTAR BOTÓN SI YA EXISTEN TRAYECTOS
        btnAbrirModal.style.display = "none";
      } else {
        listaTrayectos.textContent = "No hay trayectos definidos aún.";
        btnAbrirModal.style.display = "inline-block";
      }
    } catch (err) {
      listaTrayectos.textContent = "Error cargando trayectos.";
      console.error(err);
    }
  }

  // --- 4. ENVÍO DEL FORMULARIO ---
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const cantidadTrayectos = parseInt(numTrayectosInput.value);
    if (isNaN(cantidadTrayectos) || cantidadTrayectos < 1) {
      alert("Ingresa un número válido de trayectos", "error", "mensajes-aqui");
      return;
    }

    const trayectos = [];

    for (let i = 1; i <= cantidadTrayectos; i++) {
      const input = form.querySelector(`input[name="periodos_${i}"]`);
      if (!input) {
        alert("Faltan periodos para un trayecto.", "error", "mensajes-aqui");
        return;
      }

      const periodos = parseInt(input.value);
      if (isNaN(periodos) || periodos < 1) {
        alert(
          `Ingresa un número válido de periodos para el trayecto ${i}`,
          "error",
          "mensajes-aqui",
        );
        return;
      }

      trayectos.push({ numero: i, periodos });
    }

    // CONFIRMACIÓN GLOBAL
    const confirmar = await customConfirm(
      "Esta acción solo se puede realizar una vez.\n¿Deseas continuar?",
      "Confirmar definición",
    );

    if (!confirmar) return;

    // GUARDAR DATOS
    try {
      const res = await fetch("apis/api_guardar_trayectos.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ carrera_id: carreraId, trayectos }),
      });

      const data = await res.json();

      if (data.success) {
        alert(
          "Trayectos y periodos guardados correctamente",
          "exito",
          "mensajes-aqui",
        );
        mostrarLista(trayectos);

        modal.classList.remove("active-modal-overlay");
        modalOverlay.classList.remove("active-modal-overlay");
        body.classList.remove("active-modal-overlay");

        setTimeout(() => location.reload(), 1000);
      } else {
        alert(data.error || "Error desconocido", "error", "mensajes-aqui");
      }
    } catch (err) {
      alert("Error de conexión", "error", "mensajes-aqui");
      console.error(err);
    }
  });

  // Inicializar
  cargarTrayectos();
});
