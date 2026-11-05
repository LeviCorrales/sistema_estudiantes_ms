
<?php
$hash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = $_POST['clave'] ?? '';

    if (strlen($clave) < 12) {
        $error = 'Usa una contraseña de al menos 12 caracteres.';
    } else {
        $hash = password_hash($clave, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generar clave de acceso</title>
</head>
<body>
    <h1>Configurar contraseña del sistema</h1>

    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="clave">Nueva contraseña:</label>
        <input type="password" id="clave" name="clave"
               minlength="12" required autocomplete="new-password">
        <button type="submit">Generar hash</button>
    </form>

    <?php if ($hash !== ''): ?>
        <h2>Hash generado</h2>
        <textarea rows="4" cols="65" readonly><?= htmlspecialchars($hash) ?></textarea>
        <p>Copia el hash y pégalo en config/clave.php.</p>
    <?php endif; ?>
</body>
</html>
