<?php
require_once __DIR__ . '/config/seguridad.php';
require_once __DIR__ . '/config/auth_store.php';

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'La solicitud no es válida. Recarga la página.';
    } else {
        $codeInput = $_POST['codigo_respaldo'] ?? '';
        $nueva = $_POST['clave_nueva'] ?? null;
        $confirmar = $_POST['confirmar_clave'] ?? null;
        $normalized = is_string($codeInput) ? strtoupper(preg_replace('/[^A-F0-9]/i', '', $codeInput)) : '';

        if (strlen($normalized) !== 16) {
            $error = 'El código de respaldo no tiene el formato correcto.';
        } elseif (!auth_valid_password($nueva)) {
            $error = 'La nueva contraseña debe tener entre 12 y 200 caracteres.';
        } elseif (!is_string($confirmar) || !hash_equals($nueva, $confirmar)) {
            $error = 'La confirmación de contraseña no coincide.';
        } else {
            try {
                $data = auth_store_read();
                $match = null;

                foreach ($data['recovery_code_hashes'] as $i => $hash) {
                    if (is_string($hash) && password_verify($normalized, $hash)) {
                        $match = $i;
                        break;
                    }
                }

                if ($match === null) {
                    $error = 'El código es incorrecto o ya fue utilizado.';
                } else {
                    // Consumir el código antes de responder y cambiar la contraseña.
                    array_splice($data['recovery_code_hashes'], $match, 1);
                    $data['password_hash'] = password_hash($nueva, PASSWORD_DEFAULT);

                    if (!auth_store_write($data)) {
                        $error = 'No se pudo guardar la recuperación. No se confirmó ningún cambio; revisa permisos de config/auth_store.json.';
                    } else {
                        // Evita reutilizar una sesión previa y fuerza un nuevo inicio de sesión.
                        $_SESSION = [];
                        if (ini_get('session.use_cookies')) {
                            $params = session_get_cookie_params();
                            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
                        }
                        session_destroy();
                        header('Location: login.php?recuperada=1');
                        exit;
                    }
                }
            } catch (Throwable $e) {
                error_log('Error de recuperación de contraseña: ' . $e->getMessage());
                $error = 'No se pudo procesar la recuperación. Revisa la configuración del sistema.';
            }
        }
    }
}

$csrf = auth_csrf_token();
auth_page_start('Recuperar contraseña');
?>
<h1>Recuperar contraseña</h1>
<p>Introduce uno de tus códigos de respaldo y establece una nueva contraseña. El código utilizado quedará invalidado.</p>
<link rel="stylesheet" href="styles_seguridad_cuenta.css">
<link rel="stylesheet" href="styles_bar_nav.css">
<?php if ($error): ?>
    <p class="error"><?= auth_escape($error) ?></p>
<?php endif; ?>

<form method="post" action="recuperar_clave.php">
    <input type="hidden" name="csrf_token" value="<?= auth_escape($csrf) ?>">
    
    <label for="codigo">Código de respaldo</label>
    <input type="text" id="codigo" name="codigo_respaldo" placeholder="AB12-CD34-EF56-7890" autocomplete="off" autocapitalize="characters" required>
    
    <label for="nueva">Nueva contraseña (mínimo 12 caracteres)</label>
    <input type="password" id="nueva" name="clave_nueva" minlength="12" maxlength="200" autocomplete="new-password" required>
    
    <label for="confirmar">Confirmar nueva contraseña</label>
    <input type="password" id="confirmar" name="confirmar_clave" minlength="12" maxlength="200" autocomplete="new-password" required>
    
    <button type="submit">Restablecer contraseña</button>
</form>

<div class="links">
    <a href="login.php">Volver al inicio de sesión</a>
</div>

<?php auth_page_end(); ?>