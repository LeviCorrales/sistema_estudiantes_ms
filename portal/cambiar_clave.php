<?php
session_start();
if (!isset($_SESSION['estudiante_id'])) {
    header('Location: login.php?error=3');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" type="text/css" href="estilos_form.css">
</head>
<body>

<div class="card">
    <h2>Personalizar Contraseña</h2>
    <p>Ingresa el código actual dado por la administración y define tu clave privada.</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert-error">
            <?php 
                if($_GET['error'] == 'vacio') echo "Todos los campos son obligatorios.";
                if($_GET['error'] == 'nomatch') echo "Las nuevas contraseñas no coinciden.";
                if($_GET['error'] == 'corta') echo "La nueva contraseña debe tener al menos 6 caracteres.";
                if($_GET['error'] == 'incorrecta') echo "El código/contraseña actual es incorrecto.";
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'exito'): ?>
        <div class="alert-success">¡Contraseña actualizada correctamente!</div>
    <?php endif; ?>

    <form action="procesar_cambio_clave.php" method="POST">
        <div class="form-group">
            <label for="clave_actual">Código o Contraseña Actual</label>
            <input type="password" id="clave_actual" name="clave_actual" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="nueva_clave">Nueva Contraseña Personal</label>
            <input type="password" id="nueva_clave" name="nueva_clave" placeholder="Mínimo 6 caracteres" required>
        </div>
        <div class="form-group">
            <label for="confirmar_clave">Confirmar Nueva Contraseña</label>
            <input type="password" id="confirmar_clave" name="confirmar_clave" required>
        </div>
        <button type="submit" class="btn-submit">Guardar Nueva Contraseña</button>
    </form>

    <a href="dashboard.php" class="btn-back">← Volver al Expediente</a>
</div>

</body>
</html>