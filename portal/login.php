<?php
session_start();
if (isset($_SESSION['estudiante_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Estudiantil - Iniciar Sesión</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: #ffffff; padding: 2.5rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h2 { text-align: center; color: #1e293b; margin-bottom: 0.5rem; }
        .login-card p { text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; color: #334155; font-weight: 600; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; outline: none; transition: border-color 0.2s; }
        .form-group input:focus { border-color: #2563eb; }
        .btn-submit { width: 100%; padding: 0.75rem; background-color: #2563eb; color: #ffffff; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #1d4ed8; }
        .alert-error { background-color: #fef2f2; color: #dc2626; padding: 0.75rem; border-radius: 6px; border: 1px solid #fecaca; margin-bottom: 1rem; font-size: 0.875rem; text-align: center; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Consulta Estudiantil</h2>
    <p>Ingresa tus credenciales para ver tu expediente</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert-error">
            <?php 
                if($_GET['error'] == '1') echo "Cédula o contraseña incorrectas.";
                if($_GET['error'] == '2') echo "Tu usuario se encuentra inactivo/retirado.";
                if($_GET['error'] == '3') echo "Debes iniciar sesión primero.";
            ?>
        </div>
    <?php endif; ?>

    <form action="procesar_login.php" method="POST">
        <div class="form-group">
            <label for="cedula">Cédula de Identidad</label>
            <input type="text" id="cedula" name="cedula" placeholder="Ej: 25123456" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Ingresar al Sistema</button>
    </form>
</div>

</body>
</html>