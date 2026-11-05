document.addEventListener("DOMContentLoaded", function () {
  const estadoSelect = document.getElementById("id_estado");
  const municipioSelect = document.getElementById("id_municipio");
  const parroquiaSelect = document.getElementById("id_parroquia");

  // --- Cargar municipios/parroquias dinámicamente ---
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
        });
    }
  });

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
        });
    }
  });

  // --- Cargar datos existentes del estudiante ---
  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get("id");
  if (!id) return alert("No se encontró ID de estudiante");

  fetch(`api_ficha_estudiante.php?id=${id}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return alert("Error cargando datos");
      const e = data.estudiante;
      document.getElementById("id_estudiante").value = id;
      document.getElementById("cedula").value = e.cedula ?? "";
      document.getElementById("primer_nombre").value = e.primer_nombre ?? "";
      document.getElementById("segundo_nombre").value = e.segundo_nombre ?? "";
      document.getElementById("primer_apellido").value =
        e.primer_apellido ?? "";
      document.getElementById("segundo_apellido").value =
        e.segundo_apellido ?? "";
      document.getElementById("sexo").value = e.sexo ?? "";
      document.getElementById("estado_civil").value = e.estado_civil ?? "";
      document.getElementById("fecha_nacimiento").value =
        e.fecha_nacimiento ?? "";
      document.getElementById("telefono_celular").value =
        e.telefono_celular ?? "";

      document.getElementById("correo_electronico").value =
        e.correo_electronico ?? "";
      document.getElementById("etnia_aborigen").value = e.etnia_aborigen ?? "";
      document.getElementById("id_estado").value = e.id_estado ?? "";
      document.getElementById("id_municipio").value = e.id_municipio ?? "";
      document.getElementById("id_parroquia").value = e.id_parroquia ?? "";
      document.getElementById("avenida").value = e.avenida ?? "";
      document.getElementById("calle_sector").value = e.calle_sector ?? "";
      document.getElementById("casa_apartamento").value =
        e.casa_apartamento ?? "";
      document.getElementById("punto_referencia").value =
        e.punto_referencia ?? "";
      document.getElementById("grupo_familiar").value = e.grupo_familiar ?? "";
      document.getElementById("personas_a_cargo").value =
        e.personas_a_cargo ?? "";
      document.getElementById("fuente_ingresos").value =
        e.fuente_ingresos ?? "";
      document.getElementById("ingreso_mensual").value =
        e.ingreso_mensual ?? "";
      document.getElementById("tipo_vivienda").value = e.tipo_vivienda ?? "";
      document.getElementById("tenencia_vivienda").value =
        e.tenencia_vivienda ?? "";
    });

  // --- Validaciones y envío ---
  document
    .getElementById("editarForm")
    .addEventListener("submit", async function (e) {
      e.preventDefault();
      const form = this;
      const erroresForm = document.getElementById("erroresForm");
      erroresForm.innerHTML = "";
      const errores = [];

      // --- Copiar todas las validaciones exactas del formulario original ---
      const cedula = form.cedula.value.trim();
      if (!/^\d{7,8}$/.test(cedula))
        errores.push("Cédula debe tener 7 u 8 dígitos.");
      const primerNombre = form.primer_nombre.value.trim();
      if (!primerNombre) errores.push("Primer nombre es obligatorio.");
      const primerApellido = form.primer_apellido.value.trim();
      if (!primerApellido) errores.push("Primer apellido es obligatorio.");
      const sexo = form.sexo.value;
      if (!["M", "F"].includes(sexo))
        errores.push("Seleccione un sexo válido.");
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
      const correo_electronico = form.correo_electronico.value.trim();
      if (!correo_electronico)
        errores.push("Correo electrónico es obligatorio.");
      else if (
        !/^[^\s@]+@(gmail\.com|hotmail\.com|yahoo\.com|outlook\.com)$/.test(
          correo_electronico,
        )
      )
        errores.push(
          "Correo electrónico debe ser @gmail.com, @hotmail.com, @yahoo.com o @outlook.com",
        );
      if (!form.id_estado.value) errores.push("Debe seleccionar un estado.");
      if (!form.id_municipio.value)
        errores.push("Debe seleccionar un municipio.");
      if (!form.id_parroquia.value)
        errores.push("Debe seleccionar una parroquia.");

      if (errores.length > 0) {
        erroresForm.style.color = "red";
        errores.forEach((err) => {
          const p = document.createElement("p");
          p.textContent = err;
          erroresForm.appendChild(p);
        });
        return;
      }

      // --- Enviar al backend (UPDATE) ---
      try {
        const res = await fetch(form.action, {
          method: form.method || "POST",
          body: new FormData(form),
          headers: {
            Accept: "application/json",
          },
        });

        // Leer primero el texto para detectar respuestas que no sean JSON.
        const texto = await res.text();
        let data;

        try {
          data = JSON.parse(texto);
        } catch {
          console.error("Respuesta no válida del servidor:", texto);
          throw new Error(
            `El servidor respondió con HTTP ${res.status}, pero no devolvió JSON válido.`,
          );
        }

        erroresForm.innerHTML = "";

        if (data.success) {
          erroresForm.style.color = "green";

          const p = document.createElement("p");
          p.textContent = data.message || "Datos actualizados correctamente.";
          erroresForm.appendChild(p);

          return;
        }

        erroresForm.style.color = "red";

        // Mostrar todos los errores que devuelva el PHP.
        const erroresServidor = Array.isArray(data.errors)
          ? data.errors
          : Array.isArray(data.errores)
            ? data.errores
            : [];

        if (erroresServidor.length > 0) {
          erroresServidor.forEach((err) => {
            const p = document.createElement("p");
            p.textContent = String(err);
            erroresForm.appendChild(p);
          });
        } else {
          const p = document.createElement("p");
          p.textContent =
            data.message || data.msg || "No se pudieron actualizar los datos.";
          erroresForm.appendChild(p);
        }
      } catch (err) {
        console.error("Error al actualizar estudiante:", err);

        erroresForm.style.color = "red";
        erroresForm.innerHTML = "";

        const p = document.createElement("p");

        if (err instanceof TypeError) {
          p.textContent =
            "No se pudo conectar con el servidor. Comprueba la conexión e inténtalo nuevamente.";
        } else {
          p.textContent =
            err.message || "No se pudo procesar la respuesta del servidor.";
        }

        erroresForm.appendChild(p);
      }
    });
});
