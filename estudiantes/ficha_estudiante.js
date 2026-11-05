let dataEstudianteGlobal = null; // Declarar arriba
// ------------------ ID ESTUDIANTE ------------------
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get("id");
if (!id) {
  alert("No se especificó ID de estudiante");
  throw new Error("No se especificó ID de estudiante");
}

// ------------------ AVATAR ------------------
// ------------------ REFERENCIAS AL DOM ------------------
const avatarImg = document.getElementById("avatar");
const avatarDiv = document.getElementById("studentAvatar");
const fileInput = document.getElementById("studentAvatarInput");

// ------------------ GENERADOR DE AVATAR ------------------

function stringToColor(str = "E") {
  let hash = 0;

  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }

  // Paleta suave inspirada en los avatares de Google

  const colores = [
    "#AECBFA", // Azul claro
    "#C2E7FF", // Azul cielo
    "#CEEAD6", // Verde suave
    "#FDE293", // Amarillo cálido
    "#F6AEA9", // Rojo suave
    "#E8C5E9", // Lila
    "#D3E3FD", // Azul lavanda
    "#FAD2CF", // Rosa pastel
    "#E2E2E2", // Gris claro
    "#D7C9F2", // Violeta
    "#B4D8D0", // Verde azulado
    "#FDE7B1", // Crema
    "#B8E0F5", // Azul hielo
    "#CDECCF", // Verde menta
    "#F8D7A8", // Durazno
    "#F2C6DE", // Rosa malva
    "#C9D7F8", // Azul periwinkle
    "#DCCEF9", // Lavanda suave
    "#E7E3D4", // Beige
    "#C5E1D4", // Verde agua
    "#F5D0A9", // Albaricoque
    "#D9E8B8", // Verde lima suave
    "#EBCFD8", // Rosa empolvado
    "#C8DCEB", // Azul grisáceo
  ];

  const indice = (hash >>> 0) % colores.length;
  return colores[indice];
}

function setAvatar(name) {
  if (!avatarImg) return;

  const nombreValido =
    name && String(name).trim() !== "" ? String(name).trim() : "Estudiante";

  const inicial = Array.from(nombreValido)[0].toLocaleUpperCase("es");

  const canvas = document.createElement("canvas");
  canvas.width = 240;
  canvas.height = 240;

  const ctx = canvas.getContext("2d");
  if (!ctx) return;

  // Fondo uniforme
  ctx.fillStyle = stringToColor(nombreValido);
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  // Inicial más delgada y mejor centrada
  ctx.fillStyle = "#3C4043";
  ctx.font = "400 112px Arial, sans-serif";
  ctx.textAlign = "center";
  ctx.textBaseline = "alphabetic";

  // Centrado óptico basado en las métricas de la fuente
  const metricas = ctx.measureText(inicial);
  const centroX = canvas.width / 2;
  const centroY =
    canvas.height / 2 +
    (metricas.actualBoundingBoxAscent - metricas.actualBoundingBoxDescent) / 2;

  ctx.fillText(inicial, centroX, centroY);

  avatarImg.src = canvas.toDataURL("image/png");
}

// ------------------ EVENTO SUBIR FOTO ------------------
if (avatarDiv && fileInput) {
  avatarDiv.addEventListener("click", () => fileInput.click());

  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (!file) return;

    // Vista previa inmediata
    const reader = new FileReader();
    reader.onload = (e) => {
      if (avatarImg) avatarImg.src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Envío al servidor
    const formData = new FormData();
    formData.append("imagen", file);
    formData.append("id", id); // 'id' recibido de la URL/Página

    fetch("upload_foto.php", { method: "POST", body: formData })
      .then((res) => res.json())
      .then((resp) => {
        if (resp.success && resp.ruta) {
          if (avatarImg) avatarImg.src = resp.ruta;
        } else {
          alert(
            "Error al subir la imagen: " + (resp.msg || "Error desconocido"),
          );
        }
      })
      .catch((err) => {
        console.error("Error subiendo imagen:", err);
      });
  });
}
// ------------------ FUNCIONES ESTUDIANTE ------------------
function inicializarEstadoActual(estado) {
  const estadoActualSpan = document.getElementById("estado-actual");
  estadoActualSpan.textContent =
    estado.charAt(0).toUpperCase() + estado.slice(1);
  estadoActualSpan.className =
    "" +
    (estado === "activo"
      ? "bg-success"
      : estado === "congelado"
        ? "bg-info"
        : estado === "retirado"
          ? "bg-danger"
          : estado === "egresado"
            ? "bg-warning"
            : "bg-secondary");
}

function guardarNuevoEstado() {
  const selectNuevoEstado = document.getElementById("nuevo-estado");
  const nuevoEstado = selectNuevoEstado.value;
  if (!nuevoEstado) return alert("Seleccione un estado");

  fetch("api_cambiar_estado_estudiante.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id_estudiante=${encodeURIComponent(id)}&nuevo_estado=${encodeURIComponent(nuevoEstado)}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return alert(data.msg || "Error al cambiar estado");
      inicializarEstadoActual(nuevoEstado);
      alert("Estado actualizado correctamente", "exito", "mensajes-aqui");
      setTimeout(() => location.reload(), 2000);
    })
    .catch((err) => {
      console.error("Error cambiando estado:", err);
      alert(
        "Ocurrió un error al actualizar el estado",
        "error",
        "mensajes-aqui",
      );
      setTimeout(() => location.reload(), 2000);
    });
}

// Función auxiliar para evitar fallos si un elemento HTML no existe en el DOM
const setTexto = (id, valor) => {
  const el = document.getElementById(id);
  if (el) el.textContent = valor ?? "";
};

// ------------------ CARGAR DATOS COMPLETOS DEL ESTUDIANTE ------------------
fetch(`api_ficha_estudiante.php?id=${id}`)
  .then((res) => res.json())
  .then((data) => {
    if (!data.success) {
      alert(data.msg || "Error al cargar los datos del estudiante");
      return;
    }

    dataEstudianteGlobal = data.estudiante;
    const e = data.estudiante;

    // ---------- DATOS PERSONALES ----------
    setTexto("cedula", e.cedula);

    // Concatenación limpia de nombre completo sin espacios dobles
    const nombreCompleto = [
      e.primer_nombre,
      e.segundo_nombre,
      e.primer_apellido,
      e.segundo_apellido,
    ]
      .filter((n) => n && n.trim() !== "")
      .join(" ");
    setTexto("nombre_completo", nombreCompleto);

    setTexto("sexo", e.sexo);
    setTexto("estado_civil", e.estado_civil);
    setTexto("fecha_nacimiento", e.fecha_nacimiento);
    setTexto("telefono_celular", e.telefono_celular);
    setTexto("correo_electronico", e.correo_electronico);
    setTexto("etnia_aborigen", e.etnia_aborigen);
    setTexto("estado-actual", e.estado);

    // ---------- DIRECCIÓN ----------
    setTexto("avenida", e.avenida);
    setTexto("calle_sector", e.calle_sector);
    setTexto("casa_apartamento", e.casa_apartamento);
    setTexto("punto_referencia", e.punto_referencia);
    setTexto("estado", e.estado_nombre);
    setTexto("municipio", e.municipio_nombre);
    setTexto("parroquia", e.parroquia_nombre);

    // ---------- DATOS SOCIOECONÓMICOS ----------
    setTexto("grupo_familiar", e.grupo_familiar);
    setTexto("personas_a_cargo", e.personas_a_cargo);
    setTexto("fuente_ingresos", e.fuente_ingresos);
    setTexto("ingreso_mensual", e.ingreso_mensual);
    setTexto("tipo_vivienda", e.tipo_vivienda);
    setTexto("tenencia_vivienda", e.tenencia_vivienda);

    // ---------- DATOS ACADÉMICOS (Múltiples Carreras) ----------
    const contenedorCarreras = document.getElementById("contenedor-carreras");

    if (contenedorCarreras) {
      contenedorCarreras.innerHTML = ""; // Limpiar contenedor

      if (e.grupos && e.grupos.length > 0) {
        // Iterar dinámicamente por cada carrera/grupo asignado
        e.grupos.forEach((grupo, index) => {
          const bloqueCarrera = document.createElement("div");
          bloqueCarrera.classList.add("carrera-bloque");

          // Separador visual si cursa más de una carrera
          if (index > 0) {
            bloqueCarrera.style.borderTop = "1px dashed #cbd5e1";
            bloqueCarrera.style.marginTop = "12px";
            bloqueCarrera.style.paddingTop = "12px";
          }

          bloqueCarrera.innerHTML = `
            <div class="info-item">
                <i class="fas fa-graduation-cap"></i>
                <div><strong>Carrera:</strong> <span>${grupo.nombre_carrera ?? "N/A"}</span></div>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-alt"></i>
                <div><strong>Grupo:</strong> <span>${grupo.nombre_grupo ?? "N/A"}</span></div>
            </div>
            <div class="info-item">
                <i class="fas fa-layer-group"></i>
                <div><strong>Trayecto y periodo:</strong> <span>Trayecto ${grupo.numero_trayecto ?? "-"} / Período ${grupo.numero_periodo ?? "-"}</span></div>
            </div>
          `;

          contenedorCarreras.appendChild(bloqueCarrera);
        });
      } else {
        contenedorCarreras.innerHTML = `
          <div class="info-item">
              <i class="fas fa-info-circle"></i>
              <div><em>El estudiante no está asignado a ninguna carrera actualmente.</em></div>
          </div>
        `;
      }
    }

    // ---------- PROMEDIO ACUMULADO ----------
    setTexto("promedio-estudiante", e.promedio ? `${e.promedio} pts` : "-");

    // ---------- ASIGNACIÓN DEL AVATAR DESDE LA API ----------
    if (avatarImg) {
      if (e.foto && e.foto.trim() !== "") {
        avatarImg.src = e.foto;
      } else {
        // Si no tiene foto, genera las iniciales
        setAvatar(e.primer_nombre || e.primer_apellido);
      }
    }

    // ---------- ESTADO Y CARGA DE HISTORIAL ----------
    if (typeof inicializarEstadoActual === "function") {
      inicializarEstadoActual(e.estado || "activo");
    }

    if (typeof cargarHistorialEstudiante === "function")
      cargarHistorialEstudiante();
    if (typeof cargarHistorialInicialEstudiante === "function")
      cargarHistorialInicialEstudiante();
  })
  .catch((err) => {
    console.error("Error al obtener datos del estudiante:", err);
    alert("Ocurrió un error cargando la ficha del estudiante.");
  });

// ------------------ RENDERIZADO DE TABLA DE MATERIAS ------------------
function mostrarTablaDetalle(tituloTexto, materias, contenedor) {
  if (!contenedor) return;
  contenedor.innerHTML = ""; // Limpiar tabla anterior

  const titulo = document.createElement("h4");
  titulo.textContent = tituloTexto;
  contenedor.appendChild(titulo);

  const table = document.createElement("table");
  table.classList.add("history-table");

  table.innerHTML = `
    <thead>
      <tr>
        <th>Materia</th>
        <th>Nota</th>
        <th>Estado</th>
      </tr>
    </thead>
  `;

  const tbody = document.createElement("tbody");

  materias.forEach((mat) => {
    const tr = document.createElement("tr");

    // Normalizar a minúsculas para coincidir con el ENUM de la BD
    const estadoMat = (mat.estado || "pendiente").toLowerCase();

    let claseEstado = "status-pending";
    if (estadoMat === "aprobada") {
      claseEstado = "status-active";
    } else if (estadoMat === "reprobada") {
      claseEstado = "status-failed";
    } else if (estadoMat === "congelada") {
      claseEstado = "status-frozen";
    }

    const tdMateria = document.createElement("td");
    tdMateria.textContent = mat.nombre_materia || "Sin nombre";

    const tdNota = document.createElement("td");
    tdNota.innerHTML = `<strong>${mat.nota ?? "-"}</strong>`;

    const tdEstado = document.createElement("td");
    const badge = document.createElement("span");
    badge.className = `status-badge ${claseEstado}`;
    badge.textContent = mat.estado || "pendiente";
    tdEstado.appendChild(badge);

    tr.appendChild(tdMateria);
    tr.appendChild(tdNota);
    tr.appendChild(tdEstado);

    tbody.appendChild(tr);
  });

  table.appendChild(tbody);
  contenedor.appendChild(table);
}
/**
 * Carga el historial académico completo de un estudiante.
 * Muestra todas las materias del pensum/carrera en estado 'pendiente' si no tienen nota registrada,
 * y recalcula el progreso y promedio en tiempo real según la carrera seleccionada.
 */
async function cargarHistorialEstudiante(idEstudiante) {
  let contenedorCarreras = document.getElementById("nav-carreras");
  const contenedorBotones = document.getElementById("nav-periodos");
  const contenedorTabla = document.getElementById("historial-contenido");

  // Crear contenedor de carreras dinámicamente si no existe en el DOM
  if (!contenedorCarreras && contenedorBotones) {
    contenedorCarreras = document.createElement("div");
    contenedorCarreras.id = "nav-carreras";
    contenedorCarreras.classList.add("nav-carreras-container");
    contenedorBotones.parentNode.insertBefore(
      contenedorCarreras,
      contenedorBotones,
    );
  }

  // Usamos el ID pasado o el global 'id'
  const estudianteId = idEstudiante || (typeof id !== "undefined" ? id : null);

  if (!estudianteId) {
    if (contenedorTabla) {
      contenedorTabla.innerHTML =
        '<p class="info-carreras">Error interno: ID de estudiante no definido.</p>';
    }
    return;
  }

  try {
    const res = await fetch(
      `api_get_historial_estudiante.php?id=${estudianteId}`,
    );
    const data = await res.json();

    if (contenedorCarreras) contenedorCarreras.innerHTML = "";
    contenedorBotones.innerHTML = "";
    contenedorTabla.innerHTML = "";

    if (!data.success || !data.historial || data.historial.length === 0) {
      contenedorTabla.innerHTML =
        '<p class="info-carreras">El estudiante no se encuentra inscrito en ningún grupo o carrera activa.</p>';
      actualizarInterfazRendimiento(0, "-", "0/0 materias");
      return;
    }

    // 1. Agrupar datos por Carrera y luego por Trayecto/Período
    const agrupadoCarreras = new Map();

    data.historial.forEach((item) => {
      const idCarrera = item.id_carrera;
      const nombreCarrera = item.nombre_carrera || `Carrera ${idCarrera}`;

      if (!agrupadoCarreras.has(idCarrera)) {
        agrupadoCarreras.set(idCarrera, {
          nombre: nombreCarrera,
          totalMateriasPensum: parseInt(item.total_materias_carrera) || 0,
          periodos: new Map(),
          todasMaterias: [],
        });
      }

      const carreraObj = agrupadoCarreras.get(idCarrera);
      carreraObj.todasMaterias.push(item);

      const labelCorto = `T${item.numero_trayecto} - P${item.numero_periodo}`;
      const labelLargo = `Trayecto ${item.numero_trayecto} - Periodo ${item.numero_periodo}`;

      if (!carreraObj.periodos.has(labelCorto)) {
        carreraObj.periodos.set(labelCorto, {
          titulo: labelLargo,
          materias: [],
        });
      }

      carreraObj.periodos.get(labelCorto).materias.push(item);
    });

    // 2. Función para seleccionar y renderizar la carrera activa
    function seleccionarCarrera(carreraData) {
      const totalMaterias =
        carreraData.totalMateriasPensum > 0
          ? carreraData.totalMateriasPensum
          : carreraData.todasMaterias.length;

      let materiasAprobadas = 0;
      let sumaNotas = 0;
      let totalNotasValidas = 0;

      carreraData.todasMaterias.forEach((m) => {
        // Conteo de materias aprobadas
        if (m.estado && m.estado.toLowerCase() === "aprobada") {
          materiasAprobadas++;
        }
        // Suma de notas para el promedio (omite nulls y materias pendientes)
        if (m.nota !== null && m.nota !== "" && !isNaN(m.nota)) {
          sumaNotas += parseFloat(m.nota);
          totalNotasValidas++;
        }
      });

      const porcentaje =
        totalMaterias > 0
          ? Math.round((materiasAprobadas / totalMaterias) * 100)
          : 0;

      const promedio =
        totalNotasValidas > 0
          ? (sumaNotas / totalNotasValidas).toFixed(2)
          : "-";

      const textoMaterias = `${materiasAprobadas}/${totalMaterias} materias aprobadas`;

      // Actualizar barras visuales de rendimiento y progreso
      actualizarInterfazRendimiento(porcentaje, promedio, textoMaterias);

      // --- Generación de Botones de Período (T1 - P1, T1 - P2, etc.) ---
      contenedorBotones.innerHTML = "";
      contenedorTabla.innerHTML = "";
      let primerBotonPeriodo = null;

      carreraData.periodos.forEach((datosGrupo, labelBtn) => {
        const btnPeriodo = document.createElement("button");
        btnPeriodo.textContent = labelBtn; // Ej: T1 - P1
        btnPeriodo.classList.add("btn-periodo");

        btnPeriodo.onclick = () => {
          contenedorBotones
            .querySelectorAll(".btn-periodo")
            .forEach((b) => b.classList.remove("active"));
          btnPeriodo.classList.add("active");
          mostrarTablaDetalle(
            datosGrupo.titulo,
            datosGrupo.materias,
            contenedorTabla,
          );
        };

        contenedorBotones.appendChild(btnPeriodo);

        if (!primerBotonPeriodo) {
          primerBotonPeriodo = btnPeriodo;
        }
      });

      // Cargar el primer período de la carrera por defecto tras insertarlos al DOM
      if (primerBotonPeriodo) {
        primerBotonPeriodo.click();
      }
    }

    // 3. Generar Botones de Carrera (en caso de que curse más de una)
    let primerBotonCarrera = null;

    agrupadoCarreras.forEach((carreraData) => {
      const btnCarrera = document.createElement("button");
      btnCarrera.textContent = carreraData.nombre;
      btnCarrera.classList.add("btn-carrera");

      btnCarrera.onclick = () => {
        if (contenedorCarreras) {
          contenedorCarreras
            .querySelectorAll(".btn-carrera")
            .forEach((b) => b.classList.remove("active"));
        }
        btnCarrera.classList.add("active");
        seleccionarCarrera(carreraData);
      };

      if (contenedorCarreras) {
        contenedorCarreras.appendChild(btnCarrera);
      }

      if (!primerBotonCarrera) {
        primerBotonCarrera = btnCarrera;
      }
    });

    // Autoseleccionar la primera carrera al cargar
    if (primerBotonCarrera) {
      primerBotonCarrera.click();
    }
  } catch (err) {
    console.error("Error cargando historial:", err);
    contenedorTabla.innerHTML =
      '<p class="info">Ocurrió un error al cargar los datos del servidor.</p>';
  }
}
// --- FUNCIÓN PARA ACTUALIZAR LAS BARRAS VISUALMENTE ---
function actualizarInterfazRendimiento(porcentaje, promedio, textoMaterias) {
  // 1. Elementos de la barra de Progreso
  const barProgreso = document.querySelector(
    ".progress-item:nth-child(1) .progress-fill",
  );
  const txtProgreso = document.querySelector(".progress-item:nth-child(1) p");

  if (barProgreso && txtProgreso) {
    barProgreso.style.width = porcentaje + "%";
    txtProgreso.innerHTML = `<strong>${porcentaje}%</strong> completado (${textoMaterias})`;
    // Color según progreso: Rojo < 40%, Amarillo < 75%, Verde resto
    barProgreso.style.backgroundColor =
      porcentaje < 40 ? "#ff4d4d" : porcentaje < 75 ? "#ffd11a" : "#27ae60";
  }

  // 2. Elementos de la barra de Promedio (Rendimiento)
  const barRendimiento = document.querySelector(
    ".progress-item:nth-child(2) .progress-fill",
  );
  const txtPromedio = document.querySelector(".progress-item:nth-child(2) p");

  if (barRendimiento && txtPromedio) {
    const notaNum = parseFloat(promedio);
    const esValido = !isNaN(notaNum);
    const porcBarraPromedio = esValido ? (notaNum / 20) * 100 : 0;

    barRendimiento.style.width = porcBarraPromedio + "%";
    txtPromedio.innerHTML = `Promedio: <strong>${promedio}</strong>`;

    // Color según nota: Rojo < 12, Amarillo < 16, Verde >= 16
    barRendimiento.style.backgroundColor =
      !esValido || notaNum < 12
        ? "#ff4d4d"
        : notaNum < 16
          ? "#ffd11a"
          : "#27ae60";
  }

  // 3. Actualizar elemento flotante de promedio si existe
  const promedioSpan = document.getElementById("promedio-estudiante");
  if (promedioSpan) {
    promedioSpan.textContent = promedio;
  }
}
// ... (Todo el código anterior es el mismo) ...

// ------------------ INICIALIZACIÓN (Función principal que ya tenías) ------------------

fetch(`api_ficha_estudiante.php?id=${id}`)
  .then((res) => res.json())
  .then((data) => {
    if (!data.success) {
      console.error("Error al cargar los datos del estudiante:", data.msg);
      return;
    }

    const e = data.estudiante;

    // ... (Aquí iría el resto de tu código para llenar la ficha de datos personales) ...

    // ---------- LLAMADA AL HISTORIAL (CORREGIDA) ----------
    // Usamos la variable global 'id'
    cargarHistorialEstudiante(id);
    cargarHistorialInicialEstudiante(id); // ⭐️ CORRECCIÓN APLICADA AQUÍ
  })
  .catch((err) => {
    console.error("Error al obtener datos del estudiante:", err);
  });

// ------------------ HISTORIAL INICIAL (SOLO LECTURA) ------------------
// 🎉 FUNCIÓN MEJORADA: Acepta y valida el ID
async function cargarHistorialInicialEstudiante(idEstudiante) {
  const estudianteId = idEstudiante || id; // Usa el pasado o el global

  if (!estudianteId) return; // Si no hay ID, salimos.

  try {
    const res = await fetch(`api_get_historial_inicial.php?id=${estudianteId}`); // Usa el ID
    const data = await res.json();
    if (!data.success) return;

    const contenedor = document.getElementById("historial-inicial-periodos");
    contenedor.innerHTML = "";

    // ... (El resto de la lógica de agrupamiento y renderizado es correcta) ...

    const agrupado = {};
    data.historial.forEach((item) => {
      const key = `Trayecto IniciaL`;
      if (!agrupado[key]) agrupado[key] = [];
      agrupado[key].push(item);
    });

    for (let periodo in agrupado) {
      const divPeriodo = document.createElement("div");
      divPeriodo.classList.add("historial-periodo");

      const titulo = document.createElement("h4");
      titulo.textContent = periodo;
      divPeriodo.appendChild(titulo);

      const table = document.createElement("table");
      table.classList.add("history-table");

      const thead = document.createElement("thead");
      thead.innerHTML = `
                <tr>
                    <th>Materia</th>
                    <th>Nota</th>
                    <th>Estado</th>
                </tr>
            `;
      table.appendChild(thead);

      const tbody = document.createElement("tbody");
      agrupado[periodo].forEach((mat) => {
        const estadoBadge = `<span class="status-badge ${
          mat.estado === "Aprobada"
            ? "status-active"
            : mat.estado === "Reprobada"
              ? "status-failed"
              : "status-pending"
        }">${mat.estado ?? "Pendiente"}</span>`;

        const tr = document.createElement("tr");
        tr.innerHTML = `
                    <td>${mat.nombre_materia}</td>
                    <td>${mat.nota ?? "-"}</td>
                    <td>${estadoBadge}</td>
                `;
        tbody.appendChild(tr);
      });

      table.appendChild(tbody);
      divPeriodo.appendChild(table);
      contenedor.appendChild(divPeriodo);
    }
  } catch (err) {
    console.error("Error cargando historial inicial:", err);
  }
}

// ------------------ BOTONES RESIDENCIA / SOCIOECONÓMICOS ------------------
const btn_more_residence = document.getElementById("btn-more-residence");
const btn_more_socioeconomic = document.getElementById(
  "btn-more-socioeconomic",
);
const info_residence = document.getElementById("info-residence");
const info_socioeconomic = document.getElementById("info-socioeconomic");

let open_info_residence = false;
btn_more_residence.addEventListener("click", () => {
  open_info_residence = !open_info_residence;
  info_residence.classList.toggle("info-residence-more", open_info_residence);
  btn_more_residence.classList.toggle(
    "info-residence-more",
    open_info_residence,
  );
});

let open_info_socioeconomic = false;
btn_more_socioeconomic.addEventListener("click", () => {
  open_info_socioeconomic = !open_info_socioeconomic;
  info_socioeconomic.classList.toggle(
    "info-socioeconomic-more",
    open_info_socioeconomic,
  );
  btn_more_socioeconomic.classList.toggle(
    "info-socioeconomic-more",
    open_info_socioeconomic,
  );
});

// BOTÓN EDITAR
const btnEditar = document.getElementById("btnEditar");
btnEditar.addEventListener("click", () => {
  if (!id) return alert("No se encontró el ID del estudiante");
  // Redirige a la página de edición pasando el ID
  window.location.href = `editar_estudiante.php?id=${id}`;
});

async function generarPDF() {
  if (!dataEstudianteGlobal) {
    alert("Los datos aún se están cargando...");
    return;
  }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF("p", "mm", "letter");

  // --- Estilo igual al Récord Académico ---
  const GRIS = [240, 240, 240];
  const NARANJA = [196, 98, 16];
  const margen = 10;
  const anchoPagina = doc.internal.pageSize.getWidth();
  const altoPagina = doc.internal.pageSize.getHeight();

  const getTxt = (id) =>
    document.getElementById(id)?.textContent?.trim() || "No registrado";

  const fechaHoy = () => {
    const d = new Date();
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    return `${dd}-${mm}-${d.getFullYear()}`;
  };

  const nombre = getTxt("nombre_completo");
  const cedula = getTxt("cedula");
  const estado = getTxt("estado-actual");

  const gruposAcademicos = Array.isArray(dataEstudianteGlobal.grupos)
    ? dataEstudianteGlobal.grupos
    : [];

  const carrerasRegistradas = gruposAcademicos
    .map((g) => g.nombre_carrera)
    .filter((n) => n && n.trim() !== "");

  const carrera = carrerasRegistradas.length
    ? [...new Set(carrerasRegistradas)].join(", ")
    : "No registrado";

  // --- Línea naranja superior (sustituye al encabezado con logos) ---
  doc.setDrawColor(...NARANJA);
  doc.setLineWidth(0.6);
  doc.line(margen, 12, anchoPagina - margen, 12);
  doc.setDrawColor(0);
  doc.setLineWidth(0.2);

  let currentY = 16;

  // --- Tabla genérica: barra gris de título + filas etiqueta/valor ---
  const tabla = (titulo, filas) => {
    // Evita que el título quede solo al final de una página
    if (currentY + 30 > altoPagina - 20) {
      doc.addPage();
      currentY = 15;
    }

    const barraTitulo = [
      {
        content: titulo,
        colSpan: 2,
        styles: { halign: "center", fontStyle: "bold", fillColor: GRIS },
      },
    ];

    const cuerpo = filas.map(([etiqueta, valor]) => [
      { content: etiqueta, styles: { fontStyle: "bold" } },
      valor,
    ]);

    doc.autoTable({
      startY: currentY,
      body: [barraTitulo, ...cuerpo],
      theme: "grid",
      margin: { top: 15, bottom: 20, left: margen, right: margen },
      styles: {
        font: "helvetica",
        fontSize: 9,
        cellPadding: 1.5,
        textColor: 0,
        lineColor: 0,
        lineWidth: 0.2,
        overflow: "linebreak",
        valign: "middle",
      },
      columnStyles: {
        0: { cellWidth: 60 },
        1: { cellWidth: "auto" },
      },
      rowPageBreak: "avoid",
    });

    currentY = doc.lastAutoTable.finalY + 5;
  };

  // --- Pie de página (igual al récord) ---
  const agregarPiePagina = () => {
    const total = doc.getNumberOfPages();
    const y = altoPagina - 8;
    const fecha = fechaHoy();

    for (let i = 1; i <= total; i++) {
      doc.setPage(i);
      doc.setDrawColor(0);
      doc.setLineWidth(0.3);
      doc.line(margen, y - 4, anchoPagina - margen, y - 4);

      doc.setFontSize(7);
      doc.setTextColor(0);
      doc.setFont("helvetica", "bold");
      doc.text(fecha, margen, y);
      doc.text(`${i}/${total}`, anchoPagina / 2, y, { align: "center" });
      doc.setFont("helvetica", "bolditalic");
      doc.text("Ficha del Estudiante", anchoPagina - margen, y, {
        align: "right",
      });
    }
  };

  // ================== CONTENIDO ==================

  // Identificación
  tabla("FICHA DEL ESTUDIANTE", [
    ["Nombre completo", nombre],
    ["Cédula de identidad", cedula],
    ["PNF", carrera],
    ["Estado académico", estado],
  ]);

  // Datos personales
  tabla("DATOS PERSONALES", [
    ["Fecha de nacimiento", getTxt("fecha_nacimiento")],
    ["Género", getTxt("sexo")],
    ["Estado civil", getTxt("estado_civil")],
    ["Teléfono celular", getTxt("telefono_celular")],
    ["Correo electrónico", getTxt("correo_electronico")],
    ["Etnia aborigen", getTxt("etnia_aborigen")],
  ]);

  // Información académica
  const filasAcademicas = [];

  if (gruposAcademicos.length > 0) {
    gruposAcademicos.forEach((grupo, index) => {
      const numero = gruposAcademicos.length > 1 ? ` (${index + 1})` : "";

      filasAcademicas.push(
        [`PNF${numero}`, grupo.nombre_carrera || "No registrado"],
        [`Grupo${numero}`, grupo.nombre_grupo || "No registrado"],
        [
          `Trayecto y período${numero}`,
          `TRAYECTO ${grupo.numero_trayecto ?? "-"} - PERIODO ${grupo.numero_periodo ?? "-"}`,
        ],
      );
    });
  } else {
    filasAcademicas.push(
      ["PNF", "No registrado"],
      ["Grupo", "No registrado"],
      ["Trayecto y período", "No registrado"],
    );
  }

  filasAcademicas.push(["Promedio académico", getTxt("promedio-estudiante")]);

  tabla("INFORMACIÓN ACADÉMICA", filasAcademicas);

  // Datos de residencia
  tabla("DATOS DE RESIDENCIA", [
    ["Estado", getTxt("estado")],
    ["Municipio", getTxt("municipio")],
    ["Parroquia", getTxt("parroquia")],
    ["Avenida", getTxt("avenida")],
    ["Calle o sector", getTxt("calle_sector")],
    ["Casa o apartamento", getTxt("casa_apartamento")],
    ["Punto de referencia", getTxt("punto_referencia")],
  ]);

  // Datos socioeconómicos
  tabla("DATOS SOCIOECONÓMICOS", [
    ["Grupo familiar", getTxt("grupo_familiar")],
    ["Personas a cargo", getTxt("personas_a_cargo")],
    ["Fuente de ingresos", getTxt("fuente_ingresos")],
    ["Ingresos mensuales familiares", getTxt("ingreso_mensual")],
    ["Tipo de vivienda", getTxt("tipo_vivienda")],
    ["Tenencia de la vivienda", getTxt("tenencia_vivienda")],
  ]);

  agregarPiePagina();

  doc.save(`Ficha_${cedula}.pdf`);
}
