<?php
// generar_claves_iniciales.php (Ejecutar para asignar claves aleatorias de 8 caracteres)
require_once 'config/db.php';

// Función para generar un código seguro de 8 caracteres
function generarClaveAleatoria($longitud = 8) {
    // Caracteres legibles (sin 0, O, 1, I, l)
    $caracteres = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
    $max = strlen($caracteres) - 1;
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[random_int(0, $max)];
    }
    return $codigo;
}

try {
    // Seleccionamos los estudiantes que aún no tienen contraseña asignada
    $stmt = $pdo->query("SELECT id, cedula, primer_nombre, primer_apellido FROM datos_personales WHERE password IS NULL OR password = ''");
    $estudiantes = $stmt->fetchAll();

    if (empty($estudiantes)) {
        die("No hay estudiantes pendientes por asignar contraseña.");
    }

    $updateStmt = $pdo->prepare("UPDATE datos_personales SET password = :password WHERE id = :id");

    echo "<h2>Claves Iniciales Generadas (8 Caracteres)</h2>";
    echo "<p><strong>Atención:</strong> Copia o imprime esta lista. Una vez cerrada esta página, no se podrán recuperar los códigos en texto plano.</p>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; font-family: sans-serif;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>Cédula</th><th>Estudiante</th><th>Clave Inicial (8 Caracteres)</th></tr>";

    foreach ($estudiantes as $e) {
        // 1. Generar la clave aleatoria de 8 caracteres
        $clavePlana = generarClaveAleatoria(8);
        
        // 2. Encriptar la clave para la base de datos
        $hash = password_hash($clavePlana, PASSWORD_BCRYPT);

        // 3. Guardar el hash en la BD
        $updateStmt->execute([
            ':password' => $hash,
            ':id'       => $e['id']
        ]);

        // 4. Mostrar en pantalla para el control de la administración
        $nombre = htmlspecialchars(($e['primer_nombre'] ?? '') . ' ' . ($e['primer_apellido'] ?? ''));
        echo "<tr>";
        echo "<td>" . htmlspecialchars($e['cedula']) . "</td>";
        echo "<td>" . $nombre . "</td>";
        echo "<td><strong style='font-family: monospace; font-size: 1.1em; color: #2563eb;'>" . htmlspecialchars($clavePlana) . "</strong></td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}