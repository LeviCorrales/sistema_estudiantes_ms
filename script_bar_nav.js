document.addEventListener("DOMContentLoaded", () => {
  // 0. Inyección automática del Favicon en el <head>
  if (!document.querySelector("link[rel='icon']")) {
    const faviconLink = document.createElement("link");
    faviconLink.rel = "icon";
    faviconLink.type = "image/png";
    faviconLink.href = "/sistema_gestion_aldea/images/logo_ms_sf.ico"; // Si está en otra carpeta, ajusta la ruta (ej: /sistema_gestion_aldea/favicon.png)
    document.head.appendChild(faviconLink);
  }

  // 1. Inyección del HTML de la Topbar
  const topbarHTML = `
    <div class="topbar">
      <div class="icon-bar-nav">
        <img src="/sistema_gestion_aldea/images/logo_ms_sf.png">
      </div>

      <div class="search-container">
        <div class="search-input-container">
          <span class="search-icon"><i class="fas fa-search"></i></span>
          <input type="text" 
                 class="search-input" 
                 placeholder="Buscar estudiante..."
                 aria-label="Buscar en la base de datos"
                 id="busqueda" autocomplete="off">
        </div>

        <div class="filters-panel" id="filtersPanel">
          <div class="filter-section">
            <div class="filter-options" id="resultados">
              </div>
          </div>
        </div>
      </div>
    </div>
  `;

  const container = document.querySelector(".container-topbar");
  if (container) {
    container.innerHTML = topbarHTML;
  }

  // 2. Referencias a elementos del DOM
  const searchInput = document.getElementById("busqueda");
  const resultados = document.getElementById("resultados");
  const filtersPanel = document.getElementById("filtersPanel");
  const iconBarNav = document.querySelector(".icon-bar-nav");

  // 3. Control de Interfaz (Foco y Clics)
  searchInput.addEventListener("focus", function () {
    filtersPanel.classList.add("active");
    iconBarNav.classList.add("icon-bar-nav-active");
  });

  document.addEventListener("click", function (e) {
    if (!e.target.closest(".search-container")) {
      filtersPanel.classList.remove("active");
      iconBarNav.classList.remove("icon-bar-nav-active");
    }
  });

  // 4. Lógica de Búsqueda con Debounce
  let timeout = null;

  function escapeHTML(str) {
    return str.replace(
      /[&<>'"]/g,
      (c) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          "'": "&#39;",
          '"': "&quot;",
        })[c],
    );
  }

  searchInput.addEventListener("input", () => {
    clearTimeout(timeout);
    const query = searchInput.value.trim();

    if (query.length < 2) {
      resultados.innerHTML = "Empieza a escribir para buscar...";
      return;
    }

    // Espera 300ms antes de disparar la petición (Debounce)
    timeout = setTimeout(() => {
      fetch(
        "/sistema_gestion_aldea/buscar_estudiante_api.php?q=" +
          encodeURIComponent(query),
      )
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            if (data.estudiantes.length === 0) {
              resultados.innerHTML = "No se encontraron estudiantes.";
              return;
            }

            // Construcción de la lista de resultados
            const html = `
                            <div class="results-container" id="results-container">
                                ${data.estudiantes
                                  .map((e) => {
                                    const nombres = escapeHTML(
                                      [e.primer_nombre, e.segundo_nombre]
                                        .filter(Boolean)
                                        .join(" "),
                                    );
                                    const apellidos = escapeHTML(
                                      [e.primer_apellido, e.segundo_apellido]
                                        .filter(Boolean)
                                        .join(" "),
                                    );
                                    const urlFicha = `/sistema_gestion_aldea/estudiantes/estudiante.php?id=${e.id}`;

                                    return `
                                        <a href="${urlFicha}" class="student-item-nav">
                                            <div class="student-name-nav">${nombres}${apellidos}</div>
                                            <div class="student-info-nav">
                                                <span class="student-cedula">C.I. ${escapeHTML(e.cedula)}</span>
                                            </div>
                                        </a>
                                    `;
                                  })
                                  .join("")}
                            </div>
                        `;
            resultados.innerHTML = html;
          } else {
            resultados.innerHTML = "Error en la búsqueda.";
          }
        })
        .catch(() => {
          resultados.innerHTML = "Error de conexión.";
        });
    }, 300);
  });
});
