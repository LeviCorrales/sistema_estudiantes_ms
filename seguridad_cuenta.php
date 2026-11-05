<?php
require_once __DIR__ . '/config/seguridad.php';
require_once __DIR__ . '/config/auth_store.php';

if (empty($_SESSION['autenticado'])) {
    header('Location: login.php');
    exit;
}

try {
    $data = auth_store_read();
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudo leer la configuración de seguridad.');
}

$error = '';
$mensaje = '';
$codigosNuevos = $_SESSION['codigos_respaldo_mostrar'] ?? null;
unset($_SESSION['codigos_respaldo_mostrar']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'La solicitud expiró o no es válida. Recarga la página.';
    } elseif (($_POST['accion'] ?? '') === 'cambiar_clave') {
        $actual = $_POST['clave_actual'] ?? null;
        $nueva = $_POST['clave_nueva'] ?? null;
        $confirmar = $_POST['confirmar_clave'] ?? null;

        if (!is_string($actual) || !password_verify($actual, $data['password_hash'])) {
            $error = 'La contraseña actual es incorrecta.';
        } elseif (!auth_valid_password($nueva)) {
            $error = 'La nueva contraseña debe tener entre 12 y 200 caracteres.';
        } elseif (!is_string($confirmar) || !hash_equals($nueva, $confirmar)) {
            $error = 'La confirmación no coincide.';
        } else {
            $data['password_hash'] = password_hash($nueva, PASSWORD_DEFAULT);
            if (!auth_store_write($data)) {
                $error = 'No se pudo guardar el cambio. Revisa los permisos de config/auth_store.json.';
            } else {
                session_regenerate_id(true);
                $_SESSION['autenticado'] = true;
                $_SESSION['autenticado_desde'] = time();
                $mensaje = 'Contraseña cambiada correctamente.';
            }
        }
    } elseif (($_POST['accion'] ?? '') === 'generar_codigos') {
        // Los códigos anteriores dejan de funcionar al generar un nuevo lote.
        $plain = [];
        $hashes = [];

        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(bin2hex(random_bytes(8)));
            $plain[] = substr($code, 0, 4) . '-' . substr($code, 4, 4) . '-' . substr($code, 8, 4) . '-' . substr($code, 12, 4);
            $hashes[] = password_hash($code, PASSWORD_DEFAULT);
        }

        $data['recovery_code_hashes'] = $hashes;

        if (!auth_store_write($data)) {
            $error = 'No se pudieron guardar los códigos. Revisa los permisos de config/auth_store.json.';
        } else {
            $_SESSION['codigos_respaldo_mostrar'] = $plain;
            header('Location: seguridad_cuenta.php?codigos=generados');
            exit;
        }
    } else {
        $error = 'Acción no reconocida.';
    }
}

$csrf = auth_csrf_token();

try {
    $data = auth_store_read();
    $cantidadCodigos = count($data['recovery_code_hashes']);
} catch (Throwable $e) {
    $cantidadCodigos = 0;
}

auth_page_start('Seguridad de la cuenta');
?>
<h1>Seguridad de la cuenta</h1>
<p>Administra la contraseña de acceso.</p>

<?php if ($error): ?>
    <p class="error"><?= auth_escape($error) ?></p>
<?php endif; ?>

<?php if ($mensaje): ?>
    <p class="ok"><?= auth_escape($mensaje) ?></p>
<?php endif; ?>

<?php if (is_array($codigosNuevos) && count($codigosNuevos)): ?>
    <div class="warn">
        <strong>Guárdalos ahora.</strong> Estos códigos se muestran una sola vez. Cada código sirve para una sola recuperación. Generar otro lote invalida todos los anteriores.
    </div>
    <div class="codes">
        <?php foreach ($codigosNuevos as $code): ?>
            <?= auth_escape($code) ?><br>
        <?php endforeach; ?>
    </div>
    <p>Imprímelos o guárdalos en un lugar seguro fuera de la carpeta del sistema. No los compartas ni los guardes en una página pública.</p>
    <button type="button" onclick="navigator.clipboard.writeText(<?= json_encode(implode("\n", $codigosNuevos)) ?>).then(()=>alert('Códigos copiados')).catch(()=>alert('No se pudieron copiar; cópialos manualmente.'))">Copiar códigos</button>
<?php endif; ?>
<link rel="stylesheet" href="styles_seguridad_cuenta.css">
<link rel="stylesheet" href="styles_bar_nav.css">
<h2>Cambiar contraseña</h2>
<form method="post" action="seguridad_cuenta.php">
    <input type="hidden" name="csrf_token" value="<?= auth_escape($csrf) ?>">
    <input type="hidden" name="accion" value="cambiar_clave">

    <label for="actual">Contraseña actual</label>
    <input type="password" id="actual" name="clave_actual" autocomplete="current-password" required>

    <label for="nueva">Nueva contraseña (mínimo 12 caracteres)</label>
    <input type="password" id="nueva" name="clave_nueva" minlength="12" maxlength="200" autocomplete="new-password" required>

    <label for="confirmar">Confirmar nueva contraseña</label>
    <input type="password" id="confirmar" name="confirmar_clave" minlength="12" maxlength="200" autocomplete="new-password" required>

    <button type="submit">Guardar nueva contraseña</button>
</form>

<!-- <h2>Códigos de recuperación</h2>
<p>Códigos activos: <strong><?= (int)$cantidadCodigos ?></strong>. Al crear un nuevo lote, los códigos anteriores se invalidan.</p> -->

<!-- <form method="post" action="seguridad_cuenta.php" onsubmit="return confirm('¿Generar códigos nuevos? Los códigos anteriores dejarán de funcionar.');">
    <input type="hidden" name="csrf_token" value="<?= auth_escape($csrf) ?>">
    <input type="hidden" name="accion" value="generar_codigos">

    <button type="submit">Generar 8 códigos nuevos</button>
</form> -->

<div class="links">
    <a href="index.php">Volver al sistema</a> · <a href="logout.php">Cerrar sesión</a>
</div>

<?php 
auth_page_end(); 
?>