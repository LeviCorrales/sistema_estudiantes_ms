<?php
// Incluir la conexión a la base de datos
include '../config/conexion.php';

// Consulta para obtener los estados
$estados = $conn->query("SELECT id, nombre FROM estados ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de estudiantes</title>
    <link rel="stylesheet" type="text/css" href="styles_form.css">
    <link rel="stylesheet" type="text/css" href="../styles_bar_nav.css">
    <link rel="stylesheet" type="text/css" href="../normalize.css"> <!-- opcional -->
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
            <div class="container-btn-type-form">
                <div class="btn-type-form">
                    <button id="btn-ind">Agregar estudiante</button>
                    <button id="btn-lot">Carga masiva</button>
                    <div class="marcar-btn-form"></div>
                </div>
            </div>
            
            <div class="form-container mostrar-form">
                <form id="inscripcionForm" action="procesar_datos.php" method="post">
                    <!-- Sección de Datos Personales -->
                    <div class="section">
                        <h2 class="section-title">
                            <div>
                                <i class="fas fa-user"></i>
                            </div>
                             Datos Personales
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cedula" class="required">Cédula</label>
                                <input type="text" id="cedula" name="cedula" required class="form-control" placeholder="Ej: V-12345678" >
                                
                            </div>
                        
                        </div>
                        
                        <div class="form-row">
                        
                            
                            <div class="form-group">
                                <label for="primer_nombre" class="required">Primer Nombre</label>
                                <input type="text" id="primer_nombre" name="primer_nombre" class="form-control" required>
                        
                            </div>
                            
                            
                            <div class="form-group">
                                <label for="segundo_nombre">Segundo Nombre</label>
                                <input type="text" id="segundo_nombre" name="segundo_nombre" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="primer_apellido" class="required">Primer Apellido</label>
                                <input type="text" id="primer_apellido" name="primer_apellido" class="form-control" required>
                            </div>


                            <div class="form-group">
                                <label for="segundo_apellido">Segundo Apellido</label>
                                <input type="text" id="segundo_apellido" name="segundo_apellido" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="required">Sexo</label>
                                <select id="sexo" class="form-control" name="sexo" required>
                                    <option value="">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            
                            </div>
                            <div class="form-group">
                                <label class="required">Estado civil</label>
                                <select id="estado_civil" name="estado_civil" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    <option value="Soltero">Soltero(a)</option>
                                    <option value="Casado">Casado(a)</option>
                                    <option value="Divorciado">Divorciado(a)</option>
                                    <option value="Viudo">Viudo(a)</option>
                                </select>
                            
                            </div>
                            
                            <div class="form-group">
                                <label for="fecha_nacimiento" class="required">Fecha de Nacimiento:</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required class="form-control" >
                            
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono_celular" class="required">Teléfono Celular:</label>
                                <input type="tel" id="telefono_celular" name="telefono_celular" pattern="[0-9]{11}" class="form-control" placeholder="0412-1234567" required>
                                
                            </div>

                           
                            <div class="form-group">
                                <label for="correo_electronico" class="required">Correo Electrónico:</label>
                                <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" placeholder="ejemplo@correo.com" required>
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
                    
                    <!-- Sección de Dirección -->
                    <div class="section">
                        <h2 class="section-title">
                            <div><i class="fas fa-home"></i></div>
                             Dirección de Residencia
                        </h2>
                        
                        <div class="address-grid">
                            <div class="form-group">
                                <label for="estado" class="required">Estado</label>
                                <select id="estado" name="id_estado" class="form-control" required>
                                    <option value="">Seleccione un estado</option>
                                    <?php while($row = $estados->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="municipio" class="required">Municipio</label>
                                <select id="municipio" name="id_municipio" class="form-control" required>
                                    <option value="">Seleccione un municipio</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="parroquia" class="required">Parroquia</label>
                                <select id="parroquia" name="id_parroquia" class="form-control" required>
                                    <option value="">Seleccione una parroquia</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                            
                                <label for="avenida">Avenida</label>
                                <input type="text" id="avenida" name="avenida" class="form-control" placeholder="Ingrese avenida o avenida principal">
                            </div>
                            
                            <div class="form-group">
                                <label for="calle_sector">Calle/Sector</label>
                                <input type="text" id="calle_sector" name="calle_sector" class="form-control" placeholder="Ingrese calle o sector">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="casa_apartamento">No.Casa / Apartamento</label>
                                <input type="text" id="casa_apartamento" name="casa_apartamento" class="form-control" placeholder="Número de casa o apartamento">
                            </div>
                            
                            <div class="form-group">
                                <label for="punto_referencia">Punto de referencia</label>
                                <input type="text" id="punto_referencia" name="punto_referencia" class="form-control" placeholder="Ingrese punto de referencia">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección Socioeconómica -->
                    <div class="section">
                        <h2 class="section-title">
                            <div><i class="fas fa-chart-line"></i></div>
                             Datos Socioeconómicos
                        </h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="grupo_familiar">¿Cuántas personas conforman el grupo familiar?</label>
                                <input type="number" id="grupo_familiar" name="grupo_familiar" class="form-control" min="0">
                            </div>

                            <div class="form-group">
                                <label for="personas_a_cargo">Total de personas a cargo por usted</label>
                                <input type="number" id="personas_a_cargo" name="personas_a_cargo" class="form-control" min="0">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fuente_ingresos">Fuente de ingresos</label>
                                <input type="text" id="fuente_ingresos" name="fuente_ingresos" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="ingreso_mensual">Ingreso mensual familiar (Bs.)</label>
                                <input type="number" id="ingreso_mensual" name="ingreso_mensual" class="form-control" min="0" step="0.01">
                            </div>

                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_vivienda">Tipo de vivienda</label>
                                <input type="text" id="tipo_vivienda" name="tipo_vivienda" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="tenencia_vivienda">Tenencia de la vivienda</label>
                                <input type="text" id="tenencia_vivienda" name="tenencia_vivienda" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" value="Enviar" class="btn btn-block">
                        Guardar Datos
                    </button>
            
                    <div id="erroresForm" style="color: red; margin-top: 10px;"></div>
                    <p class="footer-note">Todos los campos marcados con <span class="required">*</span> son obligatorios</p>
                </form>
            </div>

            <div class="form-table-container">
                
                <form id="inscripcionLoteForm" action="procesar_datos_table.php" method="post" autocomplete="off">
                <div class="container-header-form-table">
                    <h2>Agregar estudiantes por lote</h2>
                    <div class="container-agregar-filas">
                        <h4>Agregar Filas</h4>
                        <input type="number" placeholder="numero de filas" id="input-agregar-filas">
                        <button type="button" class="btn-primary" id="addFilaPersonales"><i class="fa-solid fa-plus"></i></button>  
                        <div class="nota-agregar-filas">No puedes agregar +100</div>
                    </div>  
                    
                </div>
                                    
                    <!-- Tabla Datos Personales -->
                    <div class="section-table">
                        <h3>Datos Personales</h3>
                        <div class="container-table">
                        <table id="tablaPersonales" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cédula*</th>
                                    <th>Primer Nombre*</th>
                                    <th>Segundo Nombre</th>
                                    <th>Primer Apellido*</th>
                                    <th>Segundo Apellido</th>
                                    <th>Sexo*</th>
                                    <th>Estado Civil*</th>
                                    <th>Fecha de Nacimiento*</th>
                                    <th>Teléfono Celular*</th>
                                    <th>Correo Electrónico*</th>
                                    <th>Etnia Aborigen</th>
                                    <th>Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                            
                                    <td><input type="text" name="estudiantes[0][cedula]" required></td>
                                    <td><input id="primer_nombre_table" type="text" name="estudiantes[0][primer_nombre]" required></td>
                                    <td><input id="segundo_nombre_table" type="text" name="estudiantes[0][segundo_nombre]"></td>
                                    <td><input id="primer_apellido_table" type="text" name="estudiantes[0][primer_apellido]" required></td>
                                    <td><input id="segundo_apellido_table" type="text" name="estudiantes[0][segundo_apellido]"></td>
                                    <td>
                                        <select name="estudiantes[0][sexo]" required>
                                            <option value="">Seleccione</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="estudiantes[0][estado_civil]" required>
                                            <option value="">Seleccione</option>
                                            <option value="Soltero">Soltero(a)</option>
                                            <option value="Casado">Casado(a)</option>
                                            <option value="Divorciado">Divorciado(a)</option>
                                            <option value="Viudo">Viudo(a)</option>
                                        </select>
                                    </td>
                                    <td><input type="date" name="estudiantes[0][fecha_nacimiento]" required></td>
                                    <td><input type="tel" name="estudiantes[0][telefono_celular]" required></td>
                                   
                                    <td><input type="email" name="estudiantes[0][correo_electronico]" required></td>
                                    <td>
                                        <select name="estudiantes[0][etnia_aborigen]">
                                            <option value="">Seleccione</option>
                                            <option value="No">No</option>
                                            <option value="Sí">Sí</option>
                                        </select>
                                    </td>
                                    <td class="eliminar"><button type="button" class="btn-danger btn-eliminar">Eliminar</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                        
                
                    </div>

                    <!-- Tabla Dirección de Residencia -->
                    <div class="section-table">
                        <h3>Dirección de Residencia</h3>
                        <div class="container-table">
                        <table id="tablaDireccion" class="table table-bordered">
                            <thead>
                                <tr>

                                    <th>#</th>
                                    <th>Estado*</th>
                                    <th>Municipio*</th>
                                    <th>Parroquia*</th>
                                    <th>Avenida</th>
                                    <th>Calle/Sector</th>
                                    <th>No.Casa / Apartamento</th>
                                    <th>Punto de Referencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                        
                                    <td>
                                        <?php
                                            $estados = $conn->query("SELECT id, nombre FROM estados ORDER BY nombre");
                                            ?>
                                            <select name="estudiantes[0][id_estado]" class="estadoSelect" required>
                                                <option value="">Seleccione</option>
                                                <?php while($row = $estados->fetch_assoc()): ?>
                                                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                                                <?php endwhile; ?>
                                            </select>

                                    </td>
                                    <td>
                                        <select name="estudiantes[0][id_municipio]" class="municipioSelect" required>
                                            <option value="">Seleccione</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="estudiantes[0][id_parroquia]" class="parroquiaSelect" required>
                                            <option value="">Seleccione</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="estudiantes[0][avenida]"></td>
                                    <td><input type="text" name="estudiantes[0][calle_sector]"></td>
                                    <td><input type="text" name="estudiantes[0][casa_apartamento]"></td>
                                    <td><input type="text" name="estudiantes[0][punto_referencia]"></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        
                    </div>

                    <!-- Tabla Datos Socioeconómicos -->
                    <div class="section-table">
                        <h3>Datos Socioeconómicos</h3>
                        <div class="container-table">
                        <table id="tablaSocioeconomicos" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Grupo Familiar</th>
                                    <th>Personas a Cargo</th>
                                    <th>Fuente de Ingresos</th>
                                    <th>Ingreso Mensual</th>
                                    <th>Tipo de Vivienda</th>
                                    <th>Tenencia de Vivienda</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                            
                                    <td><input type="number" name="estudiantes[0][grupo_familiar]" ></td>
                                    <td><input type="number" name="estudiantes[0][personas_a_cargo]" ></td>
                                    <td><input type="text" name="estudiantes[0][fuente_ingresos]" ></td>
                                    <td><input type="number" name="estudiantes[0][ingreso_mensual]"  step="0.01"></td>
                                    <td><input type="text" name="estudiantes[0][tipo_vivienda]" ></td>
                                    <td><input type="text" name="estudiantes[0][tenencia_vivienda]" ></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        
                    </div>

                    <button type="submit" class="btn-success">Guardar todos los estudiantes</button>
                    <div id="erroresFormTable" style="color: red; margin-top: 10px;"></div>
                </form>


            </div>
            
        </div>

    </div>
   




     <script src="../script_bar_nav.js"></script>                                               
    <script src="script_form.js"></script>
    <script src="script_form_table.js"></script>

</body>
</html>




 
