/**
 * 📌 Carga dinámica de la Barra de Navegación Lateral
 */
fetch("/sistema_gestion_aldea/bar_nav.html")
  .then((res) => {
    if (!res.ok) throw new Error("No se pudo cargar el archivo bar_nav.html");
    return res.text();
  })
  .then((data) => {
    // Inyectar el HTML en el contenedor
    document.querySelector(".container-slidebar").innerHTML = data;

    // 👇 Script para resaltar el link activo automáticamente
    const currentPath = window.location.pathname;

    document.querySelectorAll(".sidebar a").forEach((link) => {
      const linkHref = link.getAttribute("href");

      // Verificamos si la ruta actual coincide con el href del enlace
      if (linkHref && currentPath.endsWith(linkHref)) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  })
  .catch((err) => {
    console.error("Error cargando navbar:", err);
  });
