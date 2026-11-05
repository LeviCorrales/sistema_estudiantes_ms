<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['estudiante_id'])) {
    header('Location: login.php?error=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_estudiante = $_SESSION['estudiante_id'];
    $actual        = trim($_POST['clave_actual'] ?? '');
    $nueva         = trim($_POST['nueva_clave'] ?? '');
    $confirmar     = trim($_POST['confirmar_clave'] ?? '');

    // 1. Validar campos vacíos
    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        header('Location: cambiar_clave.php?error=vacio');
        exit;
    }

    // 2. Validar coincidencia de claves nuevas
    if ($nueva !== $confirmar) {
        header('Location: cambiar_clave.php?error=nomatch');
        exit;
    }

    // 3. Validar seguridad de la contraseña con Regex:
    // Mínimo 8 caracteres, 1 mayúscula, 1 número y 1 carácter especial
    $pattern = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>_\-\\]).{8,}$/';
    if (!preg_match($pattern, $nueva)) {
        header('Location: cambiar_clave.php?error=insegura');
        exit;
    }

    try {
        // 4. Obtener el hash de la contraseña actual en la BD
        $stmt = $pdo->prepare("SELECT password FROM datos_personales WHERE id = :id");
        $stmt->execute([':id' => $id_estudiante]);
        $estudiante = $stmt->fetch();

        // 5. Comprobar que la clave actual ingresada sea correcta
        if ($estudiante && password_verify($actual, $estudiante['password'])) {
            
            // 6. Generar nuevo hash e ingresar en la BD
            $nuevoHash = password_hash($nueva, PASSWORD_BCRYPT);
            
            $updateStmt = $pdo->prepare("UPDATE datos_personales SET password = :password WHERE id = :id");
            $updateStmt->execute([
                ':password' => $nuevoHash,
                ':id'       => $id_estudiante
            ]);

            header('Location: cambiar_clave.php?msg=exito');
            exit;
        } else {
            header('Location: cambiar_clave.php?error=incorrecta');
            exit;
        }

    } catch (PDOException $e) {
        die("Error al cambiar la clave: " . $e->getMessage());
    }

} else {
    header('Location: cambiar_clave.php');
    exit;
}