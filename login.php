<?php
require_once __DIR__ . '/config/seguridad.php';
require_once __DIR__ . '/config/auth_store.php';

try {
    $data = auth_store_read();
} catch (Throwable $e) {
    error_log('No se pudo leer auth_store.json: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo cargar la configuración de acceso. Revisa la instalación.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $clave = $_POST['clave'] ?? null;
    if (!auth_csrf_valid($token)) {
        $error = 'La solicitud no es válida. Recarga la página.';
    } elseif (!is_string($clave) || !password_verify($clave, $data['password_hash'])) {
        $error = 'La contraseña es incorrecta.';
    } else {
        session_regenerate_id(true);
        $_SESSION['autenticado'] = true;
        $_SESSION['autenticado_desde'] = time();
        unset($_SESSION['csrf_token']);
        header('Location: index.php');
        exit;
    }
}
$csrf = auth_csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Acceso | Sistema de Gestión Estudiantil</title>
    <link rel="stylesheet" type="text/css" href="styles_login.css">
    <link rel="stylesheet" type="text/css" href="styles_bar_nav.css">
</head>
<body>
    <main class="login">
     
        <h1>Sistema de Gestión Estudiantil</h1>
        <p>Introduce la clave de acceso para continuar.</p>
        
        <?php if ($error !== ''): ?>
            <p class="error"><?= auth_escape($error) ?></p>
        <?php endif; ?>
        
        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= auth_escape($csrf) ?>">
            
            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required autofocus autocomplete="current-password">
            
            <button type="submit">Ingresar al sistema</button>
        </form>
        
        <div class="links">
            <a href="recuperar_clave.php">¿Olvidaste la contraseña?</a>
        </div>
        
        <p class="pie">Acceso restringido al personal autorizado.</p>
    </main>
</body>
</html>
