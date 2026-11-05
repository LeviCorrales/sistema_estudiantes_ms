<?php
/**
 * Almacenamiento de credenciales para el sistema local.
 * El archivo JSON debe permanecer dentro de config/ y protegido por .htaccess.
 */

function auth_store_path(): string {
    return __DIR__ . '/auth_store.json';
}

function auth_store_read(): array {
    $path = auth_store_path();
    if (!is_file($path)) {
        throw new RuntimeException('No existe config/auth_store.json. Sigue las instrucciones de instalacion.');
    }
    $raw = file_get_contents($path);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($data) || empty($data['password_hash']) || !isset($data['recovery_code_hashes']) || !is_array($data['recovery_code_hashes'])) {
        throw new RuntimeException('El archivo de credenciales no tiene un formato valido.');
    }
    return $data;
}

function auth_store_write(array $data): bool {
    $path = auth_store_path();
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) return false;

    // file_put_contents con LOCK_EX es compatible con Windows/XAMPP.
    // No se usa rename sobre un archivo existente porque puede fallar en Windows.
    return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
}

function auth_csrf_token(): string {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function auth_csrf_valid($token): bool {
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function auth_valid_password($password): bool {
    return is_string($password)
        && strlen($password) >= 12
        && strlen($password) <= 200;
}

function auth_escape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function auth_page_start(string $title): void {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="robots" content="noindex,nofollow"><title>' . auth_escape($title) . '</title>';
    echo '<style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:#f1f4f9;color:#202b3c;font-family:Arial,sans-serif}
    main{width:100%;max-width:520px;padding:30px;background:#fff;border-radius:16px;box-shadow:0 12px 40px #15223815}
    h1{font-size:23px;margin:0 0 12px}p{color:#647084;line-height:1.5}label{display:block;margin:17px 0 7px;font-weight:600}
    input{width:100%;padding:12px;border:1px solid #cbd3df;border-radius:8px;font-size:16px}button,.btn{display:inline-block;width:100%;margin-top:18px;padding:12px;border:0;border-radius:8px;background:#2458b8;color:white;text-align:center;text-decoration:none;font-size:15px;font-weight:600;cursor:pointer}
    button:hover,.btn:hover{background:#194693}.error{padding:12px;border-radius:8px;background:#fff0f0;color:#b42318}.ok{padding:12px;border-radius:8px;background:#ecfdf3;color:#166534}.warn{padding:12px;border-radius:8px;background:#fff7e6;color:#815600}.codes{font-family:monospace;line-height:1.9;overflow-wrap:anywhere;background:#f6f8fb;padding:14px;border-radius:8px}.links{display:flex;gap:14px;flex-wrap:wrap;margin-top:18px}.links a{color:#2458b8}
    </style></head><body><main>';
}

function auth_page_end(): void { echo '</main></body></html>'; }
