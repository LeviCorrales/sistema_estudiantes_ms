<?php
// Sustituye el contenido de config/seguridad.php por el contenido de este archivo.
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => false, // HTTP en localhost. Usa true si instalas HTTPS.
        'samesite' => 'Lax'
    ]);
    session_start();
}

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$paginasPublicas = [
    '/sistema_gestion_aldea/login.php',
    '/sistema_gestion_aldea/logout.php',
    '/sistema_gestion_aldea/recuperar_clave.php'
];
if (in_array($script, $paginasPublicas, true)) {
    return;
}

if (empty($_SESSION['autenticado'])) {
    $esApi = (
        strtolower($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '') === 'empty'
        || preg_match('/(^|\\/)api[^\\/]*\\.php$/i', $script) === 1
    );
    if ($esApi) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'msg' => 'Sesión no autorizada']);
        exit;
    }
    header('Location: /sistema_gestion_aldea/login.php');
    exit;
}
