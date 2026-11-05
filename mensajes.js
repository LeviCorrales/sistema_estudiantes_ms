// ---------------------------
// ALERT PERSONALIZADO
// ---------------------------
// Contenedor por defecto si no se pasa uno
let contenedorPorDefecto = document.body;

window.alert = function (msg, tipo = 'exito', contenedorId = null) {
    let contenedor = contenedorId
        ? document.getElementById(contenedorId)
        : contenedorPorDefecto;

    if (!contenedor) {
        console.error("No se encontró el contenedor para el alert");
        return;
    }

    // Crear caja del mensaje
    let caja = document.createElement('div');
    caja.innerText = msg;
    caja.style.display = "inline-block";
    caja.style.padding = '5px 15px';
    caja.style.margin = '5px 0';
    caja.style.borderRadius = '50px';
    caja.style.color = '#fff';
    caja.style.fontWeight = 'bold';
    caja.style.opacity = '0.95';
    caja.style.transition = 'all 0.3s ease';
    caja.style.boxShadow = '0 0 8px rgba(0,0,0,0.2)';

    // Estilo según tipo
    if (tipo === 'exito') {
        caja.style.backgroundColor = '#d2f4d3ff'; // verde claro
        caja.style.color = '#084e0aff';           // verde oscuro
    } else if (tipo === 'error') {
        caja.style.backgroundColor = '#f7d9d7ff'; // rojo claro
        caja.style.color = '#981107ff';           // rojo oscuro
    } else {
        caja.style.color = '#2196f3';             // azul por defecto
    }

    // Agregar al contenedor
    contenedor.appendChild(caja);

    // Auto eliminar después de 5 segundos
    setTimeout(() => {
        caja.style.opacity = '0';
        setTimeout(() => {
            if (contenedor.contains(caja)) {
                contenedor.removeChild(caja);
            }
        }, 300);
    }, 5000);
};

// ---------------------------
// CONFIRM PERSONALIZADO PRO
// ---------------------------
window.customConfirm = function (msg, titulo = "Confirmación") {
    return new Promise((resolve) => {

        // Fondo modal (Overlay)
        let modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.background = 'rgba(0,0,0,0.4)';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.zIndex = '9999';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        document.body.appendChild(modal);

        // Disparar transición de entrada
        setTimeout(() => modal.style.opacity = '1', 10);

        // Contenedor del contenido (Caja blanca)
        let contenido = document.createElement('div');
        contenido.style.background = '#fff';
        contenido.style.padding = '25px 35px';
        contenido.style.borderRadius = '12px';
        contenido.style.textAlign = 'center';
        contenido.style.minWidth = '300px';
        contenido.style.maxWidth = '400px';
        contenido.style.boxShadow = '0 12px 30px rgba(0,0,0,0.2)';
        contenido.style.transform = 'scale(0.8)';
        contenido.style.transition = 'transform 0.3s ease';
        modal.appendChild(contenido);

        // Animación de escala
        setTimeout(() => contenido.style.transform = 'scale(1)', 10);

        // Título
        let h3 = document.createElement('h3');
        h3.innerText = titulo;
        h3.style.marginBottom = '15px';
        h3.style.fontFamily = 'Arial, sans-serif';
        h3.style.color = '#333';
        h3.style.fontSize = '18px';
        contenido.appendChild(h3);

        // Mensaje
        let p = document.createElement('p');
        p.innerText = msg;
        p.style.marginBottom = '20px';
        p.style.color = '#555';
        p.style.fontSize = '15px';
        contenido.appendChild(p);

        // Contenedor de botones
        let btnContainer = document.createElement('div');
        btnContainer.style.display = 'flex';
        btnContainer.style.justifyContent = 'center';
        btnContainer.style.gap = '15px';
        contenido.appendChild(btnContainer);

        // Botón Sí
        let btnSi = document.createElement('button');
        btnSi.innerHTML = '✔ Sí';
        btnSi.style.padding = '8px 20px';
        btnSi.style.border = 'none';
        btnSi.style.borderRadius = '50px';
        btnSi.style.background = '#28a745';
        btnSi.style.color = '#fff';
        btnSi.style.cursor = 'pointer';
        btnSi.style.fontWeight = '600';
        btnSi.style.transition = 'all 0.2s';
        btnSi.onmouseover = () => btnSi.style.transform = 'scale(1.05)';
        btnSi.onmouseout = () => btnSi.style.transform = 'scale(1)';
        btnSi.onclick = () => {
            modal.remove();
            resolve(true);
        };
        btnContainer.appendChild(btnSi);

        // Botón No
        let btnNo = document.createElement('button');
        btnNo.innerHTML = '✖ No';
        btnNo.style.padding = '8px 20px';
        btnNo.style.border = 'none';
        btnNo.style.borderRadius = '50px';
        btnNo.style.background = '#dc3545';
        btnNo.style.color = '#fff';
        btnNo.style.cursor = 'pointer';
        btnNo.style.fontWeight = '600';
        btnNo.style.transition = 'all 0.2s';
        btnNo.onmouseover = () => btnNo.style.transform = 'scale(1.05)';
        btnNo.onmouseout = () => btnNo.style.transform = 'scale(1)';
        btnNo.onclick = () => {
            modal.remove();
            resolve(false);
        };
        btnContainer.appendChild(btnNo);
    });
};