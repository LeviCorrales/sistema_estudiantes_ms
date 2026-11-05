<?php
// Incluir la conexión a la base de datos
include '../config/conexion.php';

// Consulta para obtener los estados
$estados = $conn->query("SELECT id, nombre FROM estados ORDER BY nombre ASC");
$municipios = $conn->query("SELECT id, nombre FROM municipios ORDER BY nombre ASC");
$parroquias = $conn->query("SELECT id, nombre FROM parroquias ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Estudiante</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="editar_estudiante.css">
<link rel="stylesheet" type="text/css" href="../styles_bar_nav.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>

<script src="../bar_nav.js"></script>
  <!-- Barra de Navegación Lateral -->
    <div class="container-slidebar">
       
    </div>
    <div class="main-content">
        <!-- Topbar -->
         <div class="container-topbar">

         </div>

        <div class="container">
            <div class="titulo-editar-estudiante">
                <h2>Editar Estudiante</h2>
            </div>

            



<form class="form-container" id="editarForm" action="api_guardar_estudiante.php" method="POST">

    <!-- DATOS PERSONALES -->
    <div class="section">
        <input type="hidden" name="id" id="id_estudiante">
        <h2 class="section-title">
            <i class="fas fa-user"></i> Datos Personales
        </h2>

        <div class="form-row">
            <div class="form-group">
                <label for="cedula">Cédula</label>
                <input class="form-control" type="text" id="cedula" name="cedula" maxlength="8">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="primer_nombre">Primer Nombre</label>
                <input class="form-control" type="text" id="primer_nombre" name="primer_nombre">
            </div>
            <div class="form-group">
                <label for="segundo_nombre">Segundo Nombre</label>
                <input class="form-control" type="text" id="segundo_nombre" name="segundo_nombre">
            </div>
            <div class="form-group">
                <label for="primer_apellido">Primer Apellido</label>
                <input class="form-control" type="text" id="primer_apellido" name="primer_apellido">
            </div>
            <div class="form-group">
                <label for="segundo_apellido">Segundo Apellido</label>
                <input class="form-control" type="text" id="segundo_apellido" name="segundo_apellido">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sexo">Sexo</label>
                <select class="form-control" name="sexo" id="sexo">
                    <option value="">Seleccione</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>
            </div>

            <div class="form-group">
                <label for="estado_civil">Estado Civil</label>
                <select class="form-control" name="estado_civil" id="estado_civil">
                    <option value="">Seleccione</option>
                    <option value="Soltero">Soltero</option>
                    <option value="Casado">Casado</option>
                    <option value="Divorciado">Divorciado</option>
                    <option value="Viudo">Viudo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input class="form-control" type="date" id="fecha_nacimiento" name="fecha_nacimiento">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="telefono_celular">Teléfono Celular</label>
                <input class="form-control" type="text" id="telefono_celular" name="telefono_celular" maxlength="11">
            </div>
         
            <div class="form-group">
                <label for="correo_electronico">Correo Electrónico</label>
                <input class="form-control" type="email" id="correo_electronico" name="correo_electronico">
            </div>
            <div class="form-group">
                <label for="etnia">Etnia Aborigen</label>
                <select id="etnia_aborigen" name="etnia_aborigen" class="form-control">
                    <option value="">Seleccione</option>
                    <option value="No">No</option>
                    <option value="Sí">Sí</option>
                </select>
            </div>
        </div>
    </div>

    <!-- DIRECCIÓN -->
    <div class="section">
        <h2 class="section-title">
            <i class="fas fa-home"></i> Dirección de Residencia
        </h2>

        <div class="address-grid">
            <div class="form-group">
                <label for="id_estado">Estado</label>
                <select id="id_estado" name="id_estado" class="form-control" required>
                    <option value="">Seleccione un estado</option>
                    <?php while($row = $estados->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_municipio">Municipio</label>
                <select id="id_municipio" name="id_municipio" class="form-control" required>
                    <option value="">Seleccione un municipio</option>
                    <?php while($row = $municipios->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_parroquia">Parroquia</label>
                <select id="id_parroquia" name="id_parroquia" class="form-control" required>
                    <option value="">Seleccione una parroquia</option>
                    <?php while($row = $parroquias->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="avenida">Avenida</label>
                    <input class="form-control" type="text" id="avenida" name="avenida">
                </div>

                <div class="form-group">
                    <label for="calle_sector">Calle / Sector</label>
                    <input class="form-control" type="text" id="calle_sector" name="calle_sector">
                </div>

                <div class="form-group">
                    <label for="casa_apartamento">No.Casa / Apartamento</label>
                    <input class="form-control" type="text" id="casa_apartamento" name="casa_apartamento">
                </div>

                <div class="form-group">
                    <label for="punto_referencia">Punto de Referencia</label>
                    <input class="form-control" type="text" id="punto_referencia" name="punto_referencia">
                </div>
            </div>
        
    </div>

    <!-- DATOS SOCIOECONÓMICOS -->
    <div class="section">
        <h2 class="section-title">
            <i class="fas fa-chart-line"></i> Datos Socioeconómicos
        </h2>

        <div class="form-row">
            <div class="form-group">
                <label for="grupo_familiar">Grupo Familiar</label>
                <input class="form-control" type="text" id="grupo_familiar" name="grupo_familiar">
            </div>
            <div class="form-group">
                <label for="personas_a_cargo">Personas a Cargo</label>
                <input class="form-control" type="number" id="personas_a_cargo" name="personas_a_cargo">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="fuente_ingresos">Fuente de Ingresos</label>
                <input class="form-control" type="text" id="fuente_ingresos" name="fuente_ingresos">
            </div>
            <div class="form-group">
                <label for="ingreso_mensual">Ingreso Mensual</label>
                <input class="form-control" type="number" id="ingreso_mensual" name="ingreso_mensual">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tipo_vivienda">Tipo de Vivienda</label>
                <input class="form-control" type="text" id="tipo_vivienda" name="tipo_vivienda">
            </div>
            <div class="form-group">
                <label for="tenencia_vivienda">Tenencia de Vivienda</label>
                <input class="form-control" type="text" id="tenencia_vivienda" name="tenencia_vivienda">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-block">Guardar Cambios</button>
    <div id="erroresForm"></div>

</form>

        </div>

    </div>


<script src="../script_bar_nav.js"></script>
<script src="editar_estudiante.js"></script>
</body>
</html>
