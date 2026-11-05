<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($cedula) || empty($password)) {
        header('Location: login.php?error=1');
        exit;
    }

    try {
        // Consultar el usuario por cédula
        $stmt = $pdo->prepare("SELECT id, primer_nombre, primer_apellido, cedula, password, estado FROM datos_personales WHERE cedula = :cedula");
        $stmt->execute([':cedula' => $cedula]);
        $estudiante = $stmt->fetch();

        if ($estudiante && password_verify($password, $estudiante['password'])) {
            
            // Verificar si la cuenta está activa
            if ($estudiante['estado'] !== 'activo') {
                header('Location: login.php?error=2');
                exit;
            }

            // Guardar datos clave en la sesión
            $_SESSION['estudiante_id'] = $estudiante['id'];
            $_SESSION['nombre_completo'] = $estudiante['primer_nombre'] . ' ' . $estudiante['primer_apellido'];
            $_SESSION['cedula'] = $estudiante['cedula'];

            header('Location: dashboard.php');
            exit;
        } else {
            header('Location: login.php?error=1');
            exit;
        }
    } catch (PDOException $e) {
        die("Error en la consulta: " . $e->getMessage());
    }
} else {
    header('Location: login.php');
    exit;
}