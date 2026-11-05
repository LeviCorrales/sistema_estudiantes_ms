document.addEventListener("DOMContentLoaded", function () {
  const estadoSelect = document.getElementById("estado");
  const municipioSelect = document.getElementById("municipio");
  const parroquiaSelect = document.getElementById("parroquia");

  // Creamos un array con los IDs de tus inputs
  const inputsNombres = [
    "primer_nombre",
    "segundo_nombre",
    "primer_apellido",
    "segundo_apellido",
  ];

  inputsNombres.forEach((id) => {
    const input = document.getElementById(id);

    if (input) {
      input.addEventListener("input", (e) => {
        // 1. Guardamos la posición del cursor para que no salte al final
        let cursorPosition = e.target.selectionStart;

        // 2. Transformamos: primera letra y letra tras espacio a Mayúscula
        const transformedValue = e.target.value.replace(
          /(^\w|\s\w)/g,
          (match) => {
            return match.toUpperCase();
          },
        );

        // 3. Asignamos el nuevo valor
        e.target.value = transformedValue;

        // 4. Restauramos la posición del cursor
        e.target.setSelectionRange(cursorPosition, cursorPosition);
      });
    }
  });

  // Cargar municipios al cambiar estado
  estadoSelect?.addEventListener("change", function () {
    const estadoId = this.value;
    municipioSelect.innerHTML =
      '<option value="">Seleccione un municipio</option>';
    parroquiaSelect.innerHTML =
      '<option value="">Seleccione una parroquia</option>';

    if (estadoId) {
      fetch(`apis/get_municipios.php?id_estado=${estadoId}`)
        .then((res) => res.json())
        .then((data) => {
          data.forEach((m) => {
            const option = document.createElement("option");
            option.value = m.id;
            option.textContent = m.nombre;
            municipioSelect.appendChild(option);
          });
        })
        .catch((err) => console.error("Error al cargar municipios:", err));
    }
  });

  // Cargar parroquias al cambiar municipio
  municipioSelect?.addEventListener("change", function () {
    const municipioId = this.value;
    parroquiaSelect.innerHTML =
      '<option value="">Seleccione una parroquia</option>';

    if (municipioId) {
      fetch(`apis/get_parroquias.php?id_municipio=${municipioId}`)
        .then((res) => res.json())
        .then((data) => {
          data.forEach((p) => {
            const option = document.createElement("option");
            option.value = p.id;
            option.textContent = p.nombre;
            parroquiaSelect.appendChild(option);
          });
        })
        .catch((err) => console.error("Error al cargar parroquias:", err));
    }
  });
});

document
  .getElementById("inscripcionForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const form = this;
    const erroresForm = document.getElementById("erroresForm");
    erroresForm.innerHTML = "";
    const errores = [];

    // Expresión regular para detectar si hay números
    // Definimos las reglas fuera de la función para mayor eficiencia
    const tieneNumeros = /\d/;
    const regexSoloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;

    // --- Validaciones principales ---

    // Cédula
    const cedula = form.cedula.value.trim();
    if (!/^\d{7,8}$/.test(cedula))
      errores.push("Cédula debe tener 7 u 8 dígitos.");

    // Función auxiliar mejorada
    const validarCampoTexto = (valor, nombreCampo, obligatorio = true) => {
      // Si es obligatorio y está vacío
      if (obligatorio && !valor) {
        errores.push(`${nombreCampo} es obligatorio.`);
        return; // Salimos de la función para este campo
      }

      // Si el campo tiene contenido (sea obligatorio u opcional), validamos el formato
      if (valor) {
        if (valor.length > 20) {
          errores.push(`${nombreCampo} no puede tener más de 20 caracteres.`);
        } else if (tieneNumeros.test(valor)) {
          errores.push(`${nombreCampo} no puede contener números.`);
        } else if (!regexSoloLetras.test(valor)) {
          errores.push(
            `${nombreCampo} contiene caracteres no válidos (solo letras y espacios).`,
          );
        }
      }
    };

    // Ejecutamos la validación para cada campo
    // Nota: Enviamos 'false' en el tercer parámetro para los campos que no son obligatorios
    validarCampoTexto(form.primer_nombre.value.trim(), "Primer nombre", true);
    validarCampoTexto(
      form.segundo_nombre.value.trim(),
      "Segundo nombre",
      false,
    );
    validarCampoTexto(
      form.primer_apellido.value.trim(),
      "Primer apellido",
      true,
    );
    validarCampoTexto(
      form.segundo_apellido.value.trim(),
      "Segundo apellido",
      false,
    );

    // --- Mostrar errores ---
    if (errores.length > 0) {
      errores.forEach((error) => {
        const li = document.createElement("li");
        li.textContent = error;
        erroresForm.appendChild(li);
      });
    } else {
      console.log("Formulario válido, enviando...");
    }

    const sexo = form.sexo.value;
    if (!["M", "F"].includes(sexo)) errores.push("Seleccione un sexo válido.");

    const estadoCivil = form.estado_civil.value;
    if (!["Soltero", "Casado", "Divorciado", "Viudo"].includes(estadoCivil))
      errores.push("Seleccione un estado civil válido.");

    const fechaNac = form.fecha_nacimiento.value;
    if (!fechaNac) errores.push("Fecha de nacimiento es requerida.");
    else if (new Date(fechaNac) > new Date())
      errores.push("La fecha de nacimiento no puede ser futura.");

    const telefonoCel = form.telefono_celular.value.trim();
    if (!/^\d{11}$/.test(telefonoCel))
      errores.push("Teléfono celular debe tener 11 dígitos.");

    const correo = form.correo_electronico.value.trim();
    if (!correo) errores.push("Correo electrónico es obligatorio.");
    else if (correo.length > 100) errores.push("Correo demasiado largo.");
    else if (
      !/^[^\s@]+@(gmail\.com|hotmail\.com|yahoo\.com|outlook\.com)$/.test(
        correo,
      )
    )
      errores.push(
        "Correo electrónico debe ser @gmail.com, @hotmail.com, @yahoo.com o @outlook.com",
      );

    // Dirección
    if (!form.id_estado.value) errores.push("Debe seleccionar un estado.");
    if (!form.id_municipio.value)
      errores.push("Debe seleccionar un municipio.");
    if (!form.id_parroquia.value)
      errores.push("Debe seleccionar una parroquia.");

    // Mostrar errores y detener envío si existen
    if (errores.length > 0) {
      erroresForm.style.color = "red";
      errores.forEach((err) => {
        const p = document.createElement("p");
        p.textContent = err;
        erroresForm.appendChild(p);
      });
      return;
    }

    // --- Enviar datos al backend ---
    try {
      const res = await fetch(form.action, {
        method: form.method,
        body: new FormData(form),
      });

      const data = await res.json();
      erroresForm.innerHTML = ""; // limpiar errores previos

      if (data.success) {
        erroresForm.style.color = "green";
        erroresForm.textContent =
          data.message || "Datos guardados correctamente.";
        form.reset();
      } else {
        erroresForm.style.color = "red";
        if (data.errors) {
          data.errors.forEach((err) => {
            const p = document.createElement("p");
            p.textContent = err;
            erroresForm.appendChild(p);
          });
        } else {
          erroresForm.textContent = "Error al guardar los datos.";
        }
      }
    } catch (err) {
      erroresForm.style.color = "red";
      erroresForm.textContent = "Error al conectar con el servidor.";
      console.error(err);
    }
  });

const btn_ind = document.getElementById("btn-ind");
const btn_lot = document.getElementById("btn-lot");

const marcar_btn_form = document.querySelector(".btn-type-form");

const form_container = document.querySelector(".form-container");
const form_table_container = document.querySelector(".form-table-container");

let form_table = false;

btn_ind.addEventListener("click", () => {
  if (form_table == true) {
    form_container.classList.add("mostrar-form");
    form_table_container.classList.remove("mostrar-form");
    marcar_btn_form.classList.remove("marcado-btn-form");
    form_table = false;
  } else {
  }
});

btn_lot.addEventListener("click", () => {
  if (form_table == false) {
    form_container.classList.remove("mostrar-form");
    form_table_container.classList.add("mostrar-form");
    marcar_btn_form.classList.add("marcado-btn-form");
    form_table = true;
  }
});
