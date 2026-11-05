// --- BREGA AHI ADAN ---

document.addEventListener("DOMContentLoaded", function () {
  // --- VARIABLES GLOBALES DE ESTADO ---
  let isDraggingHandle = false;
  let isSelecting = false; // <--- FALTABA ESTA DECLARACIÓN
  let startCell = null; // <--- FALTABA ESTA DECLARACIÓN
  let originCell = null;

  // Creamos un array con los IDs de tus inputs
  const inputsNombres_table = [
    "primer_nombre_table",
    "segundo_nombre_table",
    "primer_apellido_table",
    "segundo_apellido_table",
  ];

  const Historial = {
    undoStack: [],
    redoStack: [],
    maxPasos: 30,
    bloqueado: false, // Evita capturas infinitas durante el Deshacer

    capturar() {
      if (this.bloqueado) return;

      const estado = {
        numFilas: tbodyPersonales.rows.length,
        datos: {
          personales: this.obtenerDatosTabla(tbodyPersonales),
          direccion: this.obtenerDatosTabla(tbodyDireccion),
          socio: this.obtenerDatosTabla(tbodySocio),
        },
      };

      this.undoStack.push(JSON.stringify(estado));
      this.redoStack = [];
      if (this.undoStack.length > this.maxPasos) this.undoStack.shift();
    },

    obtenerDatosTabla(tbody) {
      return Array.from(tbody.rows).map((fila) => {
        return Array.from(fila.querySelectorAll("input, select")).map((el) => ({
          name: el.name,
          value: el.value,
          htmlContent: el.tagName === "SELECT" ? el.innerHTML : null, // Para selects dependientes
        }));
      });
    },

    async aplicar(estadoString) {
      if (!estadoString) return;
      this.bloqueado = true;
      const estado = JSON.parse(estadoString);

      // 1. Sincronizar número de filas
      while (tbodyPersonales.rows.length < estado.numFilas) {
        // Usamos tu función agregarFila pero sin animar para que sea instantáneo
        this.ejecutarAgregarSinCaptura();
      }
      while (tbodyPersonales.rows.length > estado.numFilas) {
        const i = tbodyPersonales.rows.length - 1;
        tbodyPersonales.rows[i].remove();
        tbodyDireccion.rows[i].remove();
        tbodySocio.rows[i].remove();
      }

      // 2. Restaurar valores
      this.llenar(tbodyPersonales, estado.datos.personales);
      this.llenar(tbodyDireccion, estado.datos.direccion);
      this.llenar(tbodySocio, estado.datos.socio);

      actualizarNumeros(tbodyPersonales);
      actualizarNumeros(tbodyDireccion);
      actualizarNumeros(tbodySocio);

      this.bloqueado = false;
    },

    llenar(tbody, datosTabla) {
      datosTabla.forEach((filaDatos, rIndex) => {
        const inputs = tbody.rows[rIndex].querySelectorAll("input, select");
        filaDatos.forEach((dato, cIndex) => {
          const el = inputs[cIndex];
          if (el) {
            if (dato.htmlContent) el.innerHTML = dato.htmlContent; // Restaura opciones de municipio/parroquia
            el.value = dato.value;
          }
        });
      });
    },

    // Auxiliar para no duplicar lógica de agregarFila
    ejecutarAgregarSinCaptura() {
      const fP = tbodyPersonales.rows[0].cloneNode(true);
      const fD = tbodyDireccion.rows[0].cloneNode(true);
      const fS = tbodySocio.rows[0].cloneNode(true);
      resetFila(fP, tbodyPersonales.rows.length);
      resetFila(fD, tbodyPersonales.rows.length);
      resetFila(fS, tbodyPersonales.rows.length);
      fP.querySelector(".btn-eliminar").onclick = () =>
        eliminarFila(fP, fD, fS);
      tbodyPersonales.append(fP);
      tbodyDireccion.append(fD);
      tbodySocio.append(fS);
      initSelects(fD);
    },
  };

  let sourceSelection = []; // Guardará las celdas que estaban sombreadas ORIGINALMENTE
  const handle = document.createElement("div");
  handle.className = "fill-handle";

  // --- 1. MANEJO DEL HANDLE Y SELECCIÓN ---

  // Evento: Al hacer click en el handle
  document.addEventListener("mousedown", (e) => {
    if (e.target.classList.contains("fill-handle")) {
      e.preventDefault();
      e.stopPropagation(); // ¡VITAL! Evita que el click pase a la celda y borre la selección

      isDraggingHandle = true;

      // Guardamos la "foto" de lo que está seleccionado actualmente
      sourceSelection = Array.from(document.querySelectorAll(".td-sombreado"));

      // Si por error no hay nada sombreado pero apareció el handle, tomamos su padre
      if (sourceSelection.length === 0) {
        sourceSelection = [e.target.parentElement];
      }
      return;
    }
  });

  // Actualizar posición del handle al seleccionar celdas
  document.addEventListener("mouseup", () => {
    if (isDraggingHandle) {
      ejecutarAutollenado();
    }
    isDraggingHandle = false;

    const seleccionados = document.querySelectorAll(".td-sombreado");
    if (seleccionados.length > 0) {
      const ultimaCelda = seleccionados[seleccionados.length - 1];
      ultimaCelda.style.position = "relative"; // Asegura que el handle se posicione bien
      ultimaCelda.appendChild(handle);
      originCell = seleccionados[0];
    }
  });

  // 2. Previsualización del arrastre
  document.addEventListener("mouseover", (e) => {
    if (!isDraggingHandle) return;
    const targetTd = e.target.closest("td");
    if (!targetTd) return;

    limpiarPreview();
    // Evitamos errores si originCell se perdió
    if (!originCell) return;

    const tabla = originCell.closest("table");
    const startR = originCell.parentElement.rowIndex;
    const endR = targetTd.parentElement.rowIndex;
    const col = originCell.cellIndex;

    for (let r = Math.min(startR, endR); r <= Math.max(startR, endR); r++) {
      if (tabla.rows[r] && tabla.rows[r].cells[col]) {
        tabla.rows[r].cells[col].classList.add("preview-fill");
      }
    }
  });

  function limpiarPreview() {
    document
      .querySelectorAll(".preview-fill")
      .forEach((td) => td.classList.remove("preview-fill"));
  }

  function ejecutarAutollenado() {
    const previewCeldas = Array.from(
      document.querySelectorAll(".preview-fill"),
    );
    if (previewCeldas.length === 0) return;

    // Función para limpiar el valor y convertir a número real si es posible
    const limpiarNumero = (val) => {
      if (typeof val !== "string") val = String(val);
      // Cambiamos coma por punto para que JS lo entienda como número, y quitamos espacios
      let limpio = val.replace(",", ".").trim();
      return limpio !== "" && !isNaN(limpio) ? parseFloat(limpio) : null;
    };

    // 1. Obtener valores de referencia
    const valor1Raw = obtenerValorCelda(previewCeldas[0]);
    const valor2Raw =
      previewCeldas.length > 1 ? obtenerValorCelda(previewCeldas[1]) : null;

    const num1 = limpiarNumero(valor1Raw);
    const num2 = limpiarNumero(valor2Raw);

    previewCeldas.forEach((celda, index) => {
      // Saltamos la celda origen
      if (celda === originCell && index === 0) return;

      let nuevoValor;

      // --- CASO 1: NÚMEROS PUROS (Detecta incrementos como 2, 4, 6) ---
      if (num1 !== null) {
        let step = num2 !== null ? num2 - num1 : 1;
        let resultado = num1 + index * step;
        // Si el original tenía coma, devolvemos el resultado con coma
        nuevoValor = String(valor1Raw).includes(",")
          ? String(resultado).replace(".", ",")
          : resultado;
      }

      // --- CASO 2: TEXTO + NÚMERO (Ej: "Proyecto 1", "Fila 2") ---
      else if (/\d+$/.test(valor1Raw)) {
        const textoBase = valor1Raw.replace(/\d+$/, "");
        const numBase1 = parseInt(valor1Raw.match(/\d+$/)[0]);

        let step = 1;
        // Si la segunda celda tiene el mismo texto pero diferente número, calculamos el salto
        if (
          valor2Raw &&
          valor2Raw.startsWith(textoBase) &&
          /\d+$/.test(valor2Raw)
        ) {
          const numBase2 = parseInt(valor2Raw.match(/\d+$/)[0]);
          step = numBase2 - numBase1;
        }

        nuevoValor = textoBase + (numBase1 + index * step);
      }

      // --- CASO 3: REPETICIÓN SIMPLE ---
      else {
        nuevoValor = valor1Raw;
      }

      asignarValorCelda(celda, nuevoValor);
    });

    limpiarPreview();
  }
  // --- FUNCIONES AUXILIARES ---
  function obtenerValorCelda(td) {
    const input = td.querySelector("input, select");
    return input ? input.value : td.textContent.trim();
  }

  function asignarValorCelda(td, valor) {
    const input = td.querySelector("input, select");
    if (input) {
      input.value = valor;
      // Disparamos el evento change para que se activen validaciones o selects dependientes
      input.dispatchEvent(new Event("change", { bubbles: true }));
    } else if (td.getAttribute("contenteditable") === "true") {
      td.textContent = valor;
    }
  }

  const tablas = [
    "tablaPersonales",
    "tablaDireccion",
    "tablaSocioeconomicos",
  ].map((id) => document.getElementById(id));

  // --- BORRAR CONTENIDO (DELETE / BACKSPACE) ---
  document.addEventListener("keydown", (e) => {
    // Detectamos si se presiona "Delete" (Supr) o "Backspace" (Retroceso)
    if (e.key === "Delete" || e.key === "Backspace") {
      const seleccionados = document.querySelectorAll(".td-sombreado");

      // Si no hay celdas seleccionadas, no hacemos nada
      if (seleccionados.length === 0) return;

      // Si el usuario está escribiendo dentro de un input y solo hay UNA celda
      // seleccionada, dejamos que el borrado sea normal (carácter por carácter).
      if (
        seleccionados.length === 1 &&
        (e.target.tagName === "INPUT" || e.target.tagName === "SELECT")
      ) {
        return;
      }

      // Si llegamos aquí, es que queremos borrar el rango completo
      e.preventDefault(); // Evitamos que el Backspace nos mande a la página anterior

      seleccionados.forEach((td) => {
        // Verificamos que no sea una celda protegida
        if (
          !td.querySelector(".btn-eliminar") &&
          !td.classList.contains("fila-numero")
        ) {
          const input = td.querySelector("input, select");

          if (input) {
            input.value = "";
            // Disparamos eventos para que el sistema detecte el cambio de datos
            input.dispatchEvent(new Event("change", { bubbles: true }));
            input.dispatchEvent(new Event("input", { bubbles: true }));
          } else if (td.getAttribute("contenteditable") === "true") {
            td.textContent = "";
          }
        }
      });
    }
  });
  // --- NAVEGACIÓN CON TECLADO ---
  document.addEventListener("keydown", (e) => {
    const active = document.activeElement;
    const currentTd = active.closest("td");
    if (!currentTd) return;

    const fila = currentTd.parentElement;
    const colIndex = currentTd.cellIndex;
    const esSelect = active.tagName === "SELECT";

    let targetTd = null;

    if (
      esSelect &&
      (e.key === "ArrowUp" || e.key === "ArrowDown" || e.key === "Enter")
    ) {
      return;
    }

    switch (e.key) {
      case "Enter":
      case "ArrowDown":
        if (active.tagName === "INPUT") e.preventDefault();
        if (fila.nextElementSibling) {
          targetTd = fila.nextElementSibling.cells[colIndex];
        }
        break;

      case "ArrowUp":
        if (active.tagName === "INPUT") e.preventDefault();
        if (fila.previousElementSibling) {
          targetTd = fila.previousElementSibling.cells[colIndex];
        }
        break;

      case "ArrowLeft":
        if (active.selectionStart === 0 || active.tagName !== "INPUT") {
          targetTd = currentTd.previousElementSibling;
          while (
            targetTd &&
            !targetTd.querySelector("input, select") &&
            targetTd.getAttribute("contenteditable") !== "true"
          ) {
            targetTd = targetTd.previousElementSibling;
          }
        }
        break;

      case "ArrowRight":
        if (
          active.selectionEnd === active.value?.length ||
          active.tagName !== "INPUT"
        ) {
          targetTd = currentTd.nextElementSibling;
          while (
            targetTd &&
            !targetTd.querySelector("input, select") &&
            targetTd.getAttribute("contenteditable") !== "true"
          ) {
            targetTd = targetTd.nextElementSibling;
          }
        }
        break;
    }

    if (targetTd) {
      const nextInput =
        targetTd.querySelector("input, select") ||
        (targetTd.getAttribute("contenteditable") === "true" ? targetTd : null);

      if (nextInput) {
        e.preventDefault();
        nextInput.focus();
        if (nextInput.select && nextInput.tagName !== "SELECT") {
          nextInput.select();
        }
      }
    }
  });

  // --- COPIAR (CTRL+C) ---
  document.addEventListener("keydown", (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === "c") {
      const seleccionados = document.querySelectorAll(".td-sombreado");
      if (seleccionados.length === 0) return;

      e.preventDefault();

      document
        .querySelectorAll(".celdas-copiadas")
        .forEach((el) => el.classList.remove("celdas-copiadas"));

      let textoCopiar = "";
      let filaAnterior = seleccionados[0].parentElement;

      seleccionados.forEach((td, index) => {
        td.classList.add("celdas-copiadas");

        if (index > 0) {
          if (td.parentElement === filaAnterior) {
            textoCopiar += "\t"; // Separador de columna
          } else {
            textoCopiar += "\n"; // Separador de fila
          }
        }
        const input = td.querySelector("input, select");
        textoCopiar += input ? input.value : td.textContent.trim();
        filaAnterior = td.parentElement;
      });

      navigator.clipboard.writeText(textoCopiar);
    }
  });

  // --- CORTAR (CTRL+X) ---
  document.addEventListener("keydown", (e) => {
    // Detectar Ctrl+X (o Command+X en Mac)
    if ((e.ctrlKey || e.metaKey) && e.key === "x") {
      const seleccionados = document.querySelectorAll(".td-sombreado");
      if (seleccionados.length === 0) return;

      e.preventDefault(); // Evita el corte nativo del navegador

      let textoCopiar = "";
      let filaAnterior = seleccionados[0].parentElement;

      // Array auxiliar para guardar qué inputs vamos a limpiar
      const elementosParaLimpiar = [];

      seleccionados.forEach((td, index) => {
        // 1. Construir el texto (Igual que copiar)
        if (index > 0) {
          textoCopiar += td.parentElement === filaAnterior ? "\t" : "\n";
        }

        const input = td.querySelector("input, select");
        const valor = input ? input.value : td.textContent.trim();
        textoCopiar += valor;

        // Guardamos el elemento para borrarlo después
        if (input) {
          elementosParaLimpiar.push(input);
        } else if (td.getAttribute("contenteditable") === "true") {
          elementosParaLimpiar.push(td);
        }

        filaAnterior = td.parentElement;
      });

      // 2. Escribir al portapapeles y LUEGO borrar
      navigator.clipboard
        .writeText(textoCopiar)
        .then(() => {
          // Solo entramos aquí si se copió bien
          elementosParaLimpiar.forEach((el) => {
            if (el.tagName === "INPUT" || el.tagName === "SELECT") {
              el.value = ""; // Borrar valor
              // Disparar evento change para que se actualicen validaciones/selects
              el.dispatchEvent(new Event("change", { bubbles: true }));
            } else {
              el.textContent = ""; // Borrar texto en celdas editables
            }
          });

          // Opcional: Añadir efecto visual de "cortado"
          document
            .querySelectorAll(".celdas-copiadas")
            .forEach((el) => el.classList.remove("celdas-copiadas"));
          seleccionados.forEach((td) => td.classList.add("celdas-copiadas")); // Dejamos las hormigas un momento
        })
        .catch((err) => {
          console.error("Error al cortar: ", err);
          alert("No se pudo cortar el contenido (permiso denegado)");
        });
    }
  });

  // --- PEGAR (CTRL+V) - NUEVO CÓDIGO AÑADIDO ---
  document.addEventListener("paste", (e) => {
    const active = document.activeElement;
    const startTd = active.closest("td");
    // Agrega esta línea dentro de tu evento de Pegar o cuando sea necesario limpiar
    document
      .querySelectorAll(".celdas-copiadas")
      .forEach((el) => el.classList.remove("celdas-copiadas"));
    // Verificamos que el foco esté en un input dentro de una celda
    if (!startTd || (active.tagName !== "INPUT" && active.tagName !== "SELECT"))
      return;

    e.preventDefault(); // IMPORTANTE: Detiene el pegado estándar

    const clipboardData = (e.clipboardData || window.clipboardData).getData(
      "text",
    );

    // Dividir por líneas (Filas)
    const filas = clipboardData
      .split(/\r\n|\n|\r/)
      .filter((row) => row.length > 0);

    const startRowIndex = startTd.parentElement.rowIndex;
    const startColIndex = startTd.cellIndex;
    const tabla = startTd.closest("table");

    filas.forEach((filaTexto, rIndex) => {
      // Dividir por tabuladores (Columnas)
      const columnas = filaTexto.split("\t");

      const targetRow = tabla.rows[startRowIndex + rIndex];

      if (targetRow) {
        columnas.forEach((valor, cIndex) => {
          const targetCell = targetRow.cells[startColIndex + cIndex];
          if (targetCell) {
            // Verificamos que no sea una celda protegida (boton eliminar, etc)
            if (
              !targetCell.querySelector(".btn-eliminar") &&
              !targetCell.classList.contains("fila-numero")
            ) {
              asignarValorCelda(targetCell, valor);
            }
          }
        });
      }
    });
  });

  // --- LÓGICA DE SELECCIÓN ---
  tablas.forEach((tabla) => {
    if (!tabla) return;
    tabla.addEventListener("mousedown", (e) => {
      const td = e.target.closest("td");
      if (!td || e.target.tagName === "BUTTON" || e.target.tagName === "SELECT")
        return;
      isSelecting = true;
      startCell = td;
      limpiarSeleccion();
      td.classList.add("td-sombreado");

      // Si hacemos click simple, el handle debe aparecer en esa celda
      td.style.position = "relative";
      td.appendChild(handle);
      originCell = td;
    });

    tabla.addEventListener("mouseover", (e) => {
      if (!isSelecting) return;
      const endCell = e.target.closest("td");
      if (!endCell) return;
      resaltarRango(startCell, endCell);
    });
  });

  document.addEventListener("mouseup", () => (isSelecting = false));

  // Limpiar selección al hacer click fuera
  document.addEventListener("mousedown", (e) => {
    if (!e.target.closest("table")) {
      limpiarSeleccion();
      document
        .querySelectorAll(".celdas-copiadas")
        .forEach((el) => el.classList.remove("celdas-copiadas"));
    }
  });

  function limpiarSeleccion() {
    document
      .querySelectorAll(".td-sombreado")
      .forEach((td) => td.classList.remove("td-sombreado"));
    if (handle.parentElement) handle.remove();
  }

  function resaltarRango(start, end) {
    limpiarSeleccion();
    const tabla = start.closest("table");
    // Validar que start y end estén en la misma tabla
    if (tabla !== end.closest("table")) return;

    const r1 = start.parentElement.rowIndex;
    const r2 = end.parentElement.rowIndex;
    const c1 = start.cellIndex;
    const c2 = end.cellIndex;

    for (let r = Math.min(r1, r2); r <= Math.max(r1, r2); r++) {
      for (let c = Math.min(c1, c2); c <= Math.max(c1, c2); c++) {
        const cell = tabla.rows[r].cells[c];
        if (
          cell &&
          !cell.querySelector(".btn-eliminar") &&
          !cell.classList.contains("fila-numero")
        ) {
          cell.classList.add("td-sombreado");
        }
      }
    }
  }

  const tbodyPersonales = document.querySelector("#tablaPersonales tbody");
  const tbodyDireccion = document.querySelector("#tablaDireccion tbody");
  const tbodySocio = document.querySelector("#tablaSocioeconomicos tbody");

  const inputAgregar = document.getElementById("input-agregar-filas");
  const notaAgregar = document.querySelector(".nota-agregar-filas");
  const btnAgregar = document.getElementById("addFilaPersonales");

  let indiceFila = 1; /* =========================
       NUMERACIÓN DE FILAS
    ==========================*/

  function actualizarNumeros(tbody) {
    tbody.querySelectorAll("tr").forEach((tr, i) => {
      let td = tr.querySelector(".fila-numero");
      if (!td) {
        td = document.createElement("td");
        td.className = "fila-numero";
        tr.prepend(td);
      }
      td.textContent = i + 1;
    });
  } /* =========================
       SELECTS DEPENDIENTES
    ==========================*/

  function initSelects(fila) {
    const estado = fila.querySelector(".estadoSelect");
    const municipio = fila.querySelector(".municipioSelect");
    const parroquia = fila.querySelector(".parroquiaSelect");
    if (!estado || !municipio || !parroquia) return;

    estado.addEventListener("change", () => {
      municipio.innerHTML = '<option value="">Seleccione</option>';
      parroquia.innerHTML = '<option value="">Seleccione</option>';
      if (!estado.value) return;
      fetch(`apis/get_municipios.php?id_estado=${estado.value}`)
        .then((r) => r.json())
        .then((data) =>
          data.forEach((m) => municipio.append(new Option(m.nombre, m.id))),
        );
    });

    municipio.addEventListener("change", () => {
      parroquia.innerHTML = '<option value="">Seleccione</option>';
      if (!municipio.value) return;
      fetch(`apis/get_parroquias.php?id_municipio=${municipio.value}`)
        .then((r) => r.json())
        .then((data) =>
          data.forEach((p) => parroquia.append(new Option(p.nombre, p.id))),
        );
    });
  }

  initSelects(tbodyDireccion.rows[0]); /* =========================
       RESET DE FILA
    ==========================*/

  function resetFila(fila, index) {
    fila.querySelectorAll("input, select").forEach((el) => {
      el.value = "";
      el.setAttribute("autocomplete", "off"); // <--- Agrega esto
      if (el.name) el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
    });
  }

  function mostrarNota(msg) {
    notaAgregar.textContent = msg;
    notaAgregar.style.display = "block";
    setTimeout(() => (notaAgregar.style.display = "none"), 2000);
  }

  /* ========================================================
       FUNCIÓN MAESTRA PARA ANIMAR EL CONTENEDOR .section-table
    =========================================================*/
  function animarCambioDeAltura(contenedor, accionModificadora) {
    // 1. Medir la altura actual (antes del cambio)
    const alturaInicial = contenedor.offsetHeight;

    // 2. Ejecutar la acción (agregar o quitar la fila en el DOM)
    accionModificadora();

    // 3. Medir la nueva altura que el contenido necesita (altura final)
    // Temporalmente ponemos height auto para medir el nuevo contenido
    contenedor.style.height = "auto";
    const alturaFinal = contenedor.offsetHeight;

    // 4. Resetear a la altura inicial inmediatamente (sin que el usuario lo vea)
    contenedor.style.transition = "none";
    contenedor.style.height = alturaInicial + "px";

    // 5. Forzar un "reflow" para que el navegador registre el cambio de altura
    contenedor.offsetHeight;

    // 6. Aplicar la transición y la altura final
    contenedor.style.transition = "height 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
    contenedor.style.height = alturaFinal + "px";

    // 7. Al terminar la animación, limpiar el estilo para que siga siendo flexible
    setTimeout(() => {
      contenedor.style.height = "auto";
    }, 450);
  }

  /* ========================================================
       FUNCIÓN PARA ANIMAR EL CONTENEDOR .section-table
    =========================================================*/
  function animarCambioDeAltura(contenedor, accionModificadora) {
    // 1. Medir altura actual
    const alturaInicial = contenedor.offsetHeight;

    // 2. Realizar el cambio en el DOM (agregar o quitar filas)
    accionModificadora();

    // 3. Medir la nueva altura necesaria
    contenedor.style.height = "auto";
    const alturaFinal = contenedor.offsetHeight;

    // 4. Volver a la altura inicial para preparar la transición
    contenedor.style.transition = "none";
    contenedor.style.height = alturaInicial + "px";

    // 5. Forzar el refresco del navegador (Reflow)
    contenedor.offsetHeight;

    // 6. Ejecutar la transición hacia la altura final
    contenedor.style.transition = "height 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
    contenedor.style.height = alturaFinal + "px";

    // 7. Limpiar estilos al terminar
    setTimeout(() => {
      contenedor.style.height = "auto";
    }, 450);
  }

  /* =========================
       AGREGAR FILA
    ==========================*/
  function agregarFila() {
    Historial.capturar();
    const contenedor = tbodyPersonales.closest(".section-table");

    animarCambioDeAltura(contenedor, () => {
      if (tbodyPersonales.rows.length >= 50) return;

      const fP = tbodyPersonales.rows[0].cloneNode(true);
      const fD = tbodyDireccion.rows[0].cloneNode(true);
      const fS = tbodySocio.rows[0].cloneNode(true);

      resetFila(fP, indiceFila);
      resetFila(fD, indiceFila);
      resetFila(fS, indiceFila);

      // Botón eliminar ahora llama directamente a la animación de encogimiento
      fP.querySelector(".btn-eliminar").onclick = () => {
        eliminarFila(fP, fD, fS);
      };

      tbodyPersonales.append(fP);
      tbodyDireccion.append(fD);
      tbodySocio.append(fS);

      initSelects(fD);
      actualizarNumeros(tbodyPersonales);
      actualizarNumeros(tbodyDireccion);
      actualizarNumeros(tbodySocio);

      indiceFila++;
    });
  }

  /* =========================
       ELIMINAR FILA
    ==========================*/
  function eliminarFila(fP, fD, fS) {
    Historial.capturar();
    const contenedor = fP.closest(".section-table");

    // Ejecutamos la animación de encogimiento inmediatamente al borrar
    animarCambioDeAltura(contenedor, () => {
      fP.remove();
      fD.remove();
      fS.remove();
      actualizarNumeros(tbodyPersonales);
      actualizarNumeros(tbodyDireccion);
      actualizarNumeros(tbodySocio);
    });
  }
  btnAgregar.onclick = () => agregarFila();

  inputAgregar.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const n = parseInt(inputAgregar.value);
      if (!n || n < 1) return mostrarNota("Número inválido");
      for (let i = 0; i < n; i++) agregarFila();
      inputAgregar.value = "";
    }
  });

  actualizarNumeros(tbodyPersonales);
  actualizarNumeros(tbodyDireccion);
  actualizarNumeros(tbodySocio);

  /* ========================================================
    DELEGACIÓN DE EVENTOS PARA MAYÚSCULAS
    (Funciona para filas actuales y futuras)
=========================================================*/
  tbodyPersonales.addEventListener("input", (e) => {
    // Definimos qué inputs deben capitalizarse (por nombre o clase)
    // En este caso, buscamos si el name del input contiene alguna de estas palabras
    const camposNombre = [
      "primer_nombre",
      "segundo_nombre",
      "primer_apellido",
      "segundo_apellido",
    ];

    // Verificamos si el input que disparó el evento es uno de los que nos interesa
    const esCampoNombre = camposNombre.some(
      (nombre) => e.target.name && e.target.name.includes(nombre),
    );

    if (esCampoNombre) {
      let cursorPosition = e.target.selectionStart;

      // Transformamos: primera letra y letra tras espacio a Mayúscula
      // Agregamos soporte para tildes y eñes con \p{L} y el flag u (Unicode)
      const transformedValue = e.target.value.replace(
        /(^[\p{L}]|[\s][\p{L}])/gu,
        (match) => {
          return match.toUpperCase();
        },
      );

      e.target.value = transformedValue;

      // Restauramos la posición del cursor
      e.target.setSelectionRange(cursorPosition, cursorPosition);
    }
  });

  /* =========================
       VALIDACIONES
    ==========================*/

  function validarFila(fila, i) {
    const errores = [];
    const get = (name) =>
      fila.querySelector(`[name*="${name}"]`)?.value.trim() || "";

    const cedula = get("cedula");
    const primer_nombre = get("primer_nombre");
    const primer_apellido = get("primer_apellido");
    const segundo_nombre = get("segundo_nombre");
    const segundo_apellido = get("segundo_apellido");
    const sexo = get("sexo");
    const estado_civil = get("estado_civil");
    const fecha_nacimiento = get("fecha_nacimiento");
    const telefono_celular = get("telefono_celular");
    const correo = get("correo_electronico");

    const dir =
      fila.closest("table").id === "tablaPersonales"
        ? tbodyDireccion.rows[i]
        : null;
    const est = dir?.querySelector('[name*="id_estado"]')?.value;
    const mun = dir?.querySelector('[name*="id_municipio"]')?.value;
    const par = dir?.querySelector('[name*="id_parroquia"]')?.value; // --- VALIDACIONES ---

    // --- CONFIGURACIÓN DE REGEX ---
    const regexNumeros = /\d/;
    // Esta regex permite letras mayúsculas, minúsculas, acentos, diéresis, ñ y espacios
    const regexSoloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;

    // 1. Validación Cédula
    if (!/^\d{7,8}$/.test(cedula)) {
      errores.push(`Fila ${i + 1}: Cédula inválida (debe tener 7 u 8 dígitos)`);
    }

    // 2. Validación Primer Nombre (Obligatorio)
    if (!primer_nombre) {
      errores.push(`Fila ${i + 1}: Primer nombre obligatorio`);
    } else {
      if (primer_nombre.length > 20)
        errores.push(`Fila ${i + 1}: Primer nombre demasiado largo`);
      if (regexNumeros.test(primer_nombre))
        errores.push(`Fila ${i + 1}: Primer nombre no puede contener números`);
      if (!regexSoloLetras.test(primer_nombre))
        errores.push(
          `Fila ${i + 1}: Primer nombre contiene caracteres no válidos`,
        );
    }

    // 3. Validación Segundo Nombre (Opcional)
    if (segundo_nombre) {
      if (segundo_nombre.length > 20)
        errores.push(`Fila ${i + 1}: Segundo nombre demasiado largo`);
      if (regexNumeros.test(segundo_nombre))
        errores.push(`Fila ${i + 1}: Segundo nombre no puede contener números`);
      if (!regexSoloLetras.test(segundo_nombre))
        errores.push(
          `Fila ${i + 1}: Segundo nombre contiene caracteres no válidos`,
        );
    }

    // 4. Validación Primer Apellido (Obligatorio)
    if (!primer_apellido) {
      errores.push(`Fila ${i + 1}: Primer apellido obligatorio`);
    } else {
      if (primer_apellido.length > 20)
        errores.push(`Fila ${i + 1}: Primer apellido demasiado largo`);
      if (regexNumeros.test(primer_apellido))
        errores.push(
          `Fila ${i + 1}: Primer apellido no puede contener números`,
        );
      if (!regexSoloLetras.test(primer_apellido))
        errores.push(
          `Fila ${i + 1}: Primer apellido contiene caracteres no válidos`,
        );
    }

    // 5. Validación Segundo Apellido (Opcional)
    if (segundo_apellido) {
      if (segundo_apellido.length > 20)
        errores.push(`Fila ${i + 1}: Segundo apellido demasiado largo`);
      if (regexNumeros.test(segundo_apellido))
        errores.push(
          `Fila ${i + 1}: Segundo apellido no puede contener números`,
        );
      if (!regexSoloLetras.test(segundo_apellido))
        errores.push(
          `Fila ${i + 1}: Segundo apellido contiene caracteres no válidos`,
        );
    }
    if (!["M", "F"].includes(sexo))
      errores.push(`Fila ${i + 1}: Sexo inválido`);
    if (!["Soltero", "Casado", "Divorciado", "Viudo"].includes(estado_civil))
      errores.push(`Fila ${i + 1}: Estado civil inválido`);
    if (!fecha_nacimiento)
      errores.push(`Fila ${i + 1}: Fecha de nacimiento obligatoria`);
    else if (new Date(fecha_nacimiento) > new Date())
      errores.push(`Fila ${i + 1}: Fecha de nacimiento futura`);
    if (!/^\d{11}$/.test(telefono_celular))
      errores.push(`Fila ${i + 1}: Teléfono celular inválido`);
    if (!correo) errores.push(`Fila ${i + 1}: Correo obligatorio`);
    else if (
      !/^[^\s@]+@(gmail\.com|hotmail\.com|yahoo\.com|outlook\.com)$/.test(
        correo,
      )
    )
      errores.push(`Fila ${i + 1}: Correo inválido`);

    if (!est) errores.push(`Fila ${i + 1}: Seleccione estado`);
    if (!mun) errores.push(`Fila ${i + 1}: Seleccione municipio`);
    if (!par) errores.push(`Fila ${i + 1}: Seleccione parroquia`);

    return errores;
  } /* =========================
       ENVÍO DEL FORMULARIO
    ==========================*/

  document
    .getElementById("inscripcionLoteForm")
    .addEventListener("submit", async (e) => {
      e.preventDefault();
      const errores = [];
      const cedulas = new Set();

      tbodyPersonales.querySelectorAll("tr").forEach((fila, i) => {
        errores.push(...validarFila(fila, i));
        const cedula = fila.querySelector('[name*="cedula"]')?.value.trim();
        if (cedulas.has(cedula))
          errores.push(`Fila ${i + 1}: Cédula duplicada`);
        cedulas.add(cedula);
      });

      const div = document.getElementById("erroresFormTable");
      div.innerHTML = "";
      if (errores.length) {
        div.style.color = "red";
        errores.forEach((e) =>
          div.append(
            Object.assign(document.createElement("p"), { textContent: e }),
          ),
        );
        return;
      }

      try {
        const r = await fetch(e.target.action, {
          method: e.target.method,
          body: new FormData(e.target),
        });
        const res = await r.json();
        div.style.color = res.success ? "green" : "red";
        div.innerHTML = "";
        (res.errors?.length
          ? res.errors
          : [res.message || "Guardado con éxito"]
        ).forEach((m) =>
          div.append(
            Object.assign(document.createElement("p"), { textContent: m }),
          ),
        );
        if (res.success) {
          tbodyPersonales
            .querySelectorAll("tr")
            .forEach((fila) =>
              fila
                .querySelectorAll("input, select")
                .forEach((el) => (el.value = "")),
            );
          actualizarNumeros(tbodyPersonales);
          actualizarNumeros(tbodyDireccion);
          actualizarNumeros(tbodySocio);
        }
      } catch {
        div.style.color = "red";
        div.textContent = "Error de conexión.";
      }
    });

  document.addEventListener(
    "change",
    (e) => {
      if (e.target.closest("table")) {
        Historial.capturar();
      }
    },
    true,
  );

  document.addEventListener("keydown", (e) => {
    const isCtrl = e.ctrlKey || e.metaKey;
    if (isCtrl && e.key.toLowerCase() === "z") {
      e.preventDefault();
      if (Historial.undoStack.length > 1) {
        const actual = Historial.undoStack.pop();
        Historial.redoStack.push(actual);
        const anterior = Historial.undoStack[Historial.undoStack.length - 1];
        Historial.aplicar(anterior);
      } else {
        console.log("Estado inicial alcanzado");
      }
    }
    if (isCtrl && e.key.toLowerCase() === "y") {
      e.preventDefault();
      if (Historial.redoStack.length > 0) {
        const siguiente = Historial.redoStack.pop();
        Historial.undoStack.push(siguiente);
        Historial.aplicar(siguiente);
      }
    }
  });

  // Al final de todo tu script
  Historial.capturar();
});
