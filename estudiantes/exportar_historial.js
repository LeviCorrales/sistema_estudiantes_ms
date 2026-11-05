// =====================================================
//  RÉCORD ACADÉMICO - Diseño Misión Sucre
//  Requiere: jsPDF + jspdf-autotable
//  Usa la variable global `id` de la página (como antes)
// =====================================================

// --- CONFIGURACIÓN ---------------------------------------------------------
const LOGO_IZQUIERDO = "images/gobierno_bolivariano.jpg"; // insignia larga
const LOGO_DERECHO = "images/200_aniversario.jpg"; // bicentenario (~16:9)

const ALDEA = "ALDEA BOLIVARIANA SIMON BOLIVAR";
const UBICACION_ALDEA = "YARACUY - NIRGUA - CAPITAL NIRGUA";

const MARGEN = 10; // mm
const GRIS = [240, 240, 240];
const COLOR_LINEA_ENCABEZADO = [196, 98, 16]; // línea naranja bajo los logos

const TEXTO_LEGAL = `Para los Programas Nacionales de Formación (PNF), de acuerdo a los artículos 17 y 18 de la Resolución N°2539 publicada en Gaceta Oficial N°39.839 de fecha 10/01/2012, la escala de calificación es de uno(1) al veinte(20), siendo la nota mínima aprobatoria doce (12) puntos para las unidades curriculares, y dieciséis (16) puntos para la unidad de proyecto; así como se requiere un mínimo de setenta y cinco por ciento (75%) de asistencia a las horas presenciales de la unidad curricular. En el caso de los Programas de Formación de Grado (PFG) acreditados por la Universidad Bolivariana de Venezuela (UBV), según lo establece el artículo 13 del Reglamento General de Valoración del Desempeño Estudiantil de la UBV, la escala de calificación es de uno(1) al veinte(20), siendo la nota mínima aprobatoria diez(10) puntos para cualquier unidad curricular.`;

// --- UTILIDADES ------------------------------------------------------------
function obtenerTextoSeguro(id, alternativo = "No registrado") {
  return document.getElementById(id)?.textContent?.trim() || alternativo;
}

// Quita el código numérico del inicio: "2001-MATEMATICA I" -> "MATEMATICA I"
const limpiarMateria = (n = "") =>
  n.includes("-") ? n.split("-").slice(1).join("-").trim() : n.trim();

const fechaHoy = () => {
  const d = new Date();
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  return `${dd}-${mm}-${d.getFullYear()}`;
};

async function fetchHistorial(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error(`Error HTTP ${res.status}`);
  return res.json();
}

// Trae nombres y apellidos por separado desde api_ficha_estudiante.php
async function obtenerNombresApellidos() {
  try {
    const res = await fetchHistorial(`api_ficha_estudiante.php?id=${id}`);
    if (res.success && res.estudiante) {
      const e = res.estudiante;
      const unir = (...partes) => partes.filter(Boolean).join(" ").trim();
      return {
        nombres:
          unir(e.primer_nombre, e.segundo_nombre).toUpperCase() ||
          "No registrado",
        apellidos:
          unir(e.primer_apellido, e.segundo_apellido).toUpperCase() ||
          "No registrado",
      };
    }
  } catch (err) {
    console.warn("No se pudieron obtener nombres y apellidos", err);
  }
  // Respaldo: lo que haya en la página
  return {
    nombres: obtenerTextoSeguro(
      "nombres",
      obtenerTextoSeguro("nombre_completo"),
    ),
    apellidos: obtenerTextoSeguro("apellidos"),
  };
}

// Carga una imagen y devuelve { data, w, h } para respetar la proporción
const getImageData = (url) =>
  new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = "Anonymous";
    img.onload = () => {
      const canvas = document.createElement("canvas");
      canvas.width = img.width;
      canvas.height = img.height;
      canvas.getContext("2d").drawImage(img, 0, 0);
      resolve({
        data: canvas.toDataURL("image/png"),
        w: img.width,
        h: img.height,
      });
    };
    img.onerror = reject;
    img.src = url;
  });

// --- DIBUJO ----------------------------------------------------------------
async function dibujarLogos(pdf, pageW) {
  const altoIzq = 8;
  const altoDer = 12;
  try {
    const izq = await getImageData(LOGO_IZQUIERDO);
    const anchoIzq = altoIzq * (izq.w / izq.h);
    pdf.addImage(izq.data, "PNG", MARGEN, 10, anchoIzq, altoIzq);
  } catch (e) {
    console.warn("Logo izquierdo no cargado", e);
  }
  try {
    const der = await getImageData(LOGO_DERECHO);
    const anchoDer = altoDer * (der.w / der.h);
    pdf.addImage(
      der.data,
      "PNG",
      pageW - MARGEN - anchoDer,
      8,
      anchoDer,
      altoDer,
    );
  } catch (e) {
    console.warn("Logo derecho no cargado", e);
  }

  // Línea naranja bajo el encabezado
  pdf.setDrawColor(...COLOR_LINEA_ENCABEZADO);
  pdf.setLineWidth(0.6);
  pdf.line(MARGEN, 22, pageW - MARGEN, 22);
  pdf.setDrawColor(0);
  pdf.setLineWidth(0.2);
}

function dibujarPie(pdf, pageW, pageH) {
  const total = pdf.getNumberOfPages();
  const y = pageH - 8;
  const fecha = fechaHoy();

  for (let i = 1; i <= total; i++) {
    pdf.setPage(i);
    pdf.setDrawColor(0);
    pdf.setLineWidth(0.3);
    pdf.line(MARGEN, y - 4, pageW - MARGEN, y - 4);

    pdf.setFontSize(7);
    pdf.setFont("helvetica", "bold");
    pdf.text(fecha, MARGEN, y);
    pdf.text(`${i}/${total}`, pageW / 2, y, { align: "center" });
    pdf.setFont("helvetica", "bolditalic");
    pdf.text("Record Académico", pageW - MARGEN, y, { align: "right" });
  }
}

// Agrupa por carrera -> trayecto -> período (ordenado)
function agruparGeneral(historial) {
  const mapa = {};
  historial.forEach((mat) => {
    const idCarrera = mat.id_carrera ?? 0;
    const trayecto = mat.numero_trayecto ?? "-";
    const periodo = mat.numero_periodo ?? "-";
    const key = `${idCarrera}-T${trayecto}-P${periodo}`;
    if (!mapa[key]) {
      mapa[key] = {
        idCarrera,
        carrera: mat.nombre_carrera || `Carrera ${idCarrera}`,
        trayecto,
        periodo,
        materias: [],
      };
    }
    mapa[key].materias.push(mat);
  });

  const grupos = Object.values(mapa).sort(
    (a, b) =>
      a.idCarrera - b.idCarrera ||
      Number(a.trayecto) - Number(b.trayecto) ||
      Number(a.periodo) - Number(b.periodo),
  );

  // Si hay más de un PNF, se indica en el título de cada grupo
  const variasCarreras = new Set(grupos.map((g) => g.idCarrera)).size > 1;
  return grupos.map((g) => ({
    titulo:
      `${variasCarreras ? g.carrera + " - " : ""}` +
      `TRAYECTO ${g.trayecto} - PERIODO ${g.periodo}`,
    materias: g.materias,
  }));
}

// --- GENERADOR COMÚN -------------------------------------------------------
async function generarRecord({
  url,
  armarGrupos,
  pnf,
  prefijoArchivo,
  msgVacio,
}) {
  let data;
  try {
    data = await fetchHistorial(url);
  } catch (e) {
    console.error(e);
    return alert("No se pudo obtener el historial del estudiante.");
  }
  if (!data.success || !data.historial?.length) return alert(msgVacio);

  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF({
    orientation: "portrait",
    unit: "mm",
    format: "letter",
  });
  const pageW = pdf.internal.pageSize.getWidth();
  const pageH = pdf.internal.pageSize.getHeight();

  const cedula = obtenerTextoSeguro("cedula");
  const { nombres, apellidos } = await obtenerNombresApellidos();
  const grupos = armarGrupos(data.historial);
  const pnfTexto = typeof pnf === "function" ? pnf(data.historial) : pnf;

  await dibujarLogos(pdf, pageW);

  // ---- Tabla 1: título + datos del estudiante ----
  const etiqueta = (t) => ({ content: t, styles: { fontStyle: "bold" } });

  const filas = [
    [
      {
        content: "RECORD ACADÉMICO",
        colSpan: 4,
        styles: { halign: "center", fontStyle: "bold", fillColor: GRIS },
      },
    ],
    [etiqueta("Cédula de Identidad:"), { content: cedula, colSpan: 3 }],
    [etiqueta("Apellidos:"), apellidos, etiqueta("Nombres:"), nombres],
    [etiqueta("Aldea Universitaria:"), { content: ALDEA, colSpan: 3 }],
    [etiqueta("Ubicación Aldea:"), { content: UBICACION_ALDEA, colSpan: 3 }],
    [
      etiqueta("Turno:"),
      { content: obtenerTextoSeguro("turno", "-"), colSpan: 3 },
    ],
  ];
  if (pnfTexto)
    filas.push([etiqueta("PNF:"), { content: pnfTexto, colSpan: 3 }]);

  const estiloBase = {
    fontSize: 8,
    cellPadding: 1,
    textColor: 0,
    lineColor: 0,
    lineWidth: 0.2,
  };

  pdf.autoTable({
    startY: 26,
    body: filas,
    theme: "grid",
    margin: { top: 15, bottom: 20, left: MARGEN, right: MARGEN },
    styles: estiloBase,
    columnStyles: { 0: { cellWidth: 38 }, 2: { cellWidth: 25 } },
  });

  // ---- Tabla 2: calificaciones ----
  const tituloGrupo = (t) => [
    {
      content: t,
      colSpan: 2,
      styles: { halign: "center", fontStyle: "bold", fillColor: GRIS },
    },
  ];
  const encabezadoColumnas = [
    {
      content: "Unidad Curricular",
      styles: { halign: "center", fontStyle: "bold", fillColor: GRIS },
    },
    {
      content: "Nota",
      styles: { halign: "center", fontStyle: "bold", fillColor: GRIS },
    },
  ];

  const cuerpo = [];
  grupos.forEach((g, i) => {
    cuerpo.push(tituloGrupo(g.titulo));
    if (i === 0) cuerpo.push(encabezadoColumnas); // como en el modelo: una sola vez
    g.materias.forEach((m) =>
      cuerpo.push([limpiarMateria(m.nombre_materia), m.nota ?? "-"]),
    );
  });

  pdf.autoTable({
    startY: pdf.lastAutoTable.finalY,
    body: cuerpo,
    theme: "grid",
    margin: { top: 15, bottom: 20, left: MARGEN, right: MARGEN },
    styles: estiloBase,
    columnStyles: {
      0: { cellWidth: "auto" },
      1: { cellWidth: 15, halign: "center" },
    },
  });

  // ---- Texto legal ----
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(7);
  const lineas = pdf.splitTextToSize(TEXTO_LEGAL, pageW - MARGEN * 2);
  const altoTexto = lineas.length * 3.2;
  let y = pdf.lastAutoTable.finalY + 6;

  if (y + altoTexto > pageH - 20) {
    pdf.addPage();
    y = 20;
  }
  pdf.text(lineas, MARGEN, y);

  // ---- Pie de página en todas las hojas ----
  dibujarPie(pdf, pageW, pageH);

  pdf.save(`${prefijoArchivo}_${cedula}.pdf`);
}

// --- BOTONES ---------------------------------------------------------------
document.getElementById("btn-export-general").addEventListener("click", () =>
  generarRecord({
    url: `api_get_historial_estudiante.php?id=${id}`,
    armarGrupos: agruparGeneral,
    pnf: (historial) =>
      [...new Set(historial.map((m) => m.nombre_carrera).filter(Boolean))].join(
        ", ",
      ) || "No registrado",
    prefijoArchivo: "Record",
    msgVacio: "No hay historial para exportar.",
  }),
);

document.getElementById("btn-export-inicial").addEventListener("click", () =>
  generarRecord({
    url: `api_get_historial_inicial.php?id=${id}`,
    armarGrupos: (historial) => [
      { titulo: "TRAYECTO INICIAL", materias: historial },
    ],
    pnf: null, // el trayecto inicial no tiene PNF
    prefijoArchivo: "Trayecto_Inicial",
    msgVacio: "No hay historial inicial para exportar.",
  }),
);
