document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnAvanzar');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const idGrupo = parseInt(btn.dataset.id);
        if (!idGrupo) {
            showAlert('Grupo inválido', 'error', 'mensajes-aqui');
            return;
        }

        // 👇 confirm asíncrono con estilo
const confirmado = await customConfirm('¿Deseas avanzar este grupo al siguiente periodo?');
if (!confirmado) return;

try {
    const res = await fetch('advance_periodo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_grupo: idGrupo })
    });

    const data = await res.json();
    console.log('Respuesta del servidor:', data);

    if (data.success) {
        alert('Grupo actualizado correctamente', 'exito', 'mensajes-aqui');

        // Recargar página después de 2 segundos para que el usuario vea el mensaje
        setTimeout(() => location.reload(), 2000);

    } else {
        alert('Error: ' + data.error, 'error', 'mensajes-aqui');

        // Recargar página después de 2 segundos también en caso de error
        setTimeout(() => location.reload(), 2000);
    }

} catch (err) {
    console.error(err);
    alert('Error de conexión con el servidor', 'error', 'mensajes-aqui');

    // Recargar página después de 2 segundos en caso de fallo de conexión
    setTimeout(() => location.reload(), 2000);
}

    });
});



document.addEventListener('DOMContentLoaded', function () {
    const btnRetroceder = document.getElementById('btnRetroceder');
    if (!btnRetroceder) return;

    btnRetroceder.addEventListener('click', async () => {
        const idGrupo = parseInt(btnRetroceder.dataset.id);
        if (!idGrupo) {
            showAlert('Grupo inválido', 'error', 'mensajes-aqui');
            return;
        }

        // 👇 CONFIRM ASÍNCRONO con estilo personalizado
        const confirmado = await customConfirm('¿Deseas retroceder este grupo al periodo anterior?');
if (!confirmado) return;

try {
    const res = await fetch('retroceder_periodo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_grupo: idGrupo })
    });

    const data = await res.json();
    console.log('Respuesta del servidor:', data);

    if (data.success) {
        alert('Grupo actualizado correctamente', 'exito', 'mensajes-aqui');

        // Recargar página después de 2 segundos
        setTimeout(() => location.reload(), 2000);

    } else {
        alert('Error: ' + data.error, 'error', 'mensajes-aqui');

        // Recargar página después de 2 segundos
        setTimeout(() => location.reload(), 2000);
    }

} catch (err) {
    console.error(err);
    alert('Error de conexión con el servidor', 'error', 'mensajes-aqui');

    // Recargar página después de 2 segundos
    setTimeout(() => location.reload(), 2000);
}

    });
});

