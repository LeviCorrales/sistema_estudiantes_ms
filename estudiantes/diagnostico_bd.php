<?php
require_once "../config/conexion.php";
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Diagnóstico Base de Datos</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; }";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }";
echo ".error { background: #f8d7da; color: #721c24; }";
echo ".success { background: #d4edda; color: #155724; }";
echo ".warning { background: #fff3cd; color: #856404; }";
echo "table { border-collapse: collapse; width: 100%; margin: 10px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Diagnóstico de Base de Datos</h1>";

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    echo "<div class='section error'>";
    echo "<h3>❌ Error de Conexión</h3>";
    echo "<p>No se pudo conectar a la base de datos: " . ($conn->connect_error ?? 'Conexión no establecida') . "</p>";
    echo "</div>";
    exit;
}

echo "<div class='section success'>";
echo "<h3>✅ Conexión Exitosa</h3>";
echo "<p>Base de datos: " . $conn->database . "</p>";
echo "<p>Servidor: " . $conn->server_info . "</p>";
echo "</div>";

// Obtener lista de tablas
echo "<div class='section'>";
echo "<h3>📋 Tablas Disponibles</h3>";

$tables_query = "SHOW TABLES";
$tables_result = $conn->query($tables_query);

if ($tables_result) {
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Acciones</th></tr>";
    
    while ($row = $tables_result->fetch_array()) {
        $table_name = $row[0];
        echo "<tr>";
        echo "<td><strong>$table_name</strong></td>";
        echo "<td><button onclick='showTableStructure(\"$table_name\")'>Ver Estructura</button></td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='error'>Error al obtener lista de tablas: " . $conn->error . "</p>";
}
echo "</div>";

// Verificar tabla datos_personales específicamente
echo "<div class='section'>";
echo "<h3>👤 Verificación de Tabla 'datos_personales'</h3>";

$check_table = "SHOW TABLES LIKE 'datos_personales'";
$table_exists = $conn->query($check_table);

if ($table_exists && $table_exists->num_rows > 0) {
    echo "<p class='success'>✅ La tabla 'datos_personales' existe</p>";
    
    // Mostrar estructura de la tabla
    $structure_query = "DESCRIBE datos_personales";
    $structure_result = $conn->query($structure_query);
    
    if ($structure_result) {
        echo "<h4>Estructura de la tabla:</h4>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $structure_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Verificar si hay datos
        $count_query = "SELECT COUNT(*) as total FROM datos_personales";
        $count_result = $conn->query($count_query);
        
        if ($count_result) {
            $count = $count_result->fetch_assoc()['total'];
            echo "<p><strong>Total de registros:</strong> $count</p>";
            
            if ($count > 0) {
                echo "<p class='success'>✅ La tabla tiene datos</p>";
                
                // Mostrar primer registro como ejemplo
                $sample_query = "SELECT * FROM datos_personales LIMIT 1";
                $sample_result = $conn->query($sample_query);
                
                if ($sample_result) {
                    $sample = $sample_result->fetch_assoc();
                    echo "<h4>Ejemplo de registro:</h4>";
                    echo "<table>";
                    echo "<tr><th>Campo</th><th>Valor</th></tr>";
                    
                    foreach ($sample as $field => $value) {
                        echo "<tr>";
                        echo "<td><strong>$field</strong></td>";
                        echo "<td>" . ($value ?? 'NULL') . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                }
            } else {
                echo "<p class='warning'>⚠️ La tabla está vacía</p>";
            }
        }
    }
} else {
    echo "<p class='error'>❌ La tabla 'datos_personales' NO existe</p>";
}
echo "</div>";

// Verificar otras tablas mencionadas en la consulta original
$other_tables = [
    'direccion_residencia',
    'estados', 
    'municipios',
    'parroquias',
    'datos_socioeconomicos',
    'grupo_estudiantes',
    'grupos',
    'carreras',
    'trayectos',
    'periodos'
];

echo "<div class='section'>";
echo "<h3>🔍 Verificación de Otras Tablas</h3>";

foreach ($other_tables as $table) {
    $check = "SHOW TABLES LIKE '$table'";
    $exists = $conn->query($check);
    
    if ($exists && $exists->num_rows > 0) {
        echo "<p class='success'>✅ $table - Existe</p>";
    } else {
        echo "<p class='error'>❌ $table - NO existe</p>";
    }
}
echo "</div>";

// Probar consulta simple
echo "<div class='section'>";
echo "<h3>🧪 Prueba de Consulta Simple</h3>";

try {
    $test_query = "SELECT id, cedula, primer_nombre, primer_apellido FROM datos_personales LIMIT 3";
    $test_result = $conn->query($test_query);
    
    if ($test_result) {
        echo "<p class='success'>✅ Consulta simple exitosa</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Cédula</th><th>Nombre</th><th>Apellido</th></tr>";
        
        while ($row = $test_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['cedula'] . "</td>";
            echo "<td>" . $row['primer_nombre'] . "</td>";
            echo "<td>" . $row['primer_apellido'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Error en consulta simple: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Excepción en consulta: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "<script>";
echo "function showTableStructure(tableName) {";
echo "  alert('Estructura de la tabla: ' + tableName + '\\n\\nPara ver la estructura completa, ejecuta: DESCRIBE ' + tableName + ' en tu cliente de base de datos.');";
echo "}";
echo "</script>";

echo "</body>";
echo "</html>";

$conn->close();
?> 