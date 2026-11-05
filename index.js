fetch("resumen_estudiantes.php")
  .then((res) => res.json())
  .then((data) => {
    if (data.success) {
      // 📌 1. Mostrar totales en las tarjetas superiores
      document.getElementById("total-estudiantes").textContent =
        data.total_estudiantes;
      document.getElementById("total-recientes").textContent =
        data.total_recientes;

      // 📌 2. Renderizar lista de estudiantes recientes
      const cont = document.getElementById("lista-recientes");
      cont.innerHTML = ""; // Limpiar contenedor

      data.recientes.forEach((est) => {
        // Crear un <a> que envuelva todo el item para hacerlo clickeable
        const item = document.createElement("a");
        item.href = `estudiantes/estudiante.php?id=${est.id}`;
        item.classList.add("student-item");

        // Generar contenido interno
        item.innerHTML = `
                    <div class="student-avatar">
                        ${
                          est.foto
                            ? `<img src="estudiantes/${est.foto}" alt="${est.nombre_completo}" />`
                            : `<span>${est.nombre_completo.substring(0, 2).toUpperCase()}</span>`
                        }
                    </div>
                    <div class="student-info">
                        <div class="student-name">${est.nombre_completo}</div>
                        <div class="student-details">
                            <span>${est.cedula}</span>
                            <span>${est.nombre_carrera || "Sin carrera"}</span>
                            <span><i class="fas fa-clock"></i> ${est.trayecto_periodo || "-"}</span>
                        </div>
                    </div>
                    <div class="student-date">
                        ${new Date(est.fecha_registro).toLocaleDateString()}
                    </div>
                `;

        cont.appendChild(item);
      });
    }
  })
  .catch((err) => {
    console.error("Error al obtener el resumen:", err);
    const cont = document.getElementById("lista-recientes");
    if (cont)
      cont.innerHTML = "<p>No se pudieron cargar los datos recientes.</p>";
  });
