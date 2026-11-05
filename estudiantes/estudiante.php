<?php
$id_estudiante = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_carrera = 0; // Puedes cargarla dinámicamente si tienes una consulta previa
$estado_actual = "activo"; // Valor por defecto si no hay registro previo
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ficha Estudiante</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="ficha_estudiante.css">
<link rel="stylesheet" type="text/css" href="../styles_bar_nav.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>

</style>
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
     
        
        <div class="student-card">
            <div class="left-column">
                <div class="student-avatar" id="studentAvatarContainer">
                    <div class="avatar" id="studentAvatar">
                        <img id="avatar" src="" alt="Avatar del estudiante">
                    </div>
                    <input type="file" id="studentAvatarInput" accept="image/*" style="display:none;">
                </div>
            



                
            
                
                <h2><span id="nombre_completo"></span></h2>
                <div>
                     <span id="estado-actual" class="bg-success bg-info bg-danger bg-warning"></span>
                </div>
               

              

        


    <div class="cambiar-estado-container">
        <label for="nuevo-estado" class="fw-bold">Cambiar estado a:</label>
        <div class="cambiar-estado">
            <select id="nuevo-estado" class="form-select mt-2">
                <option value="activo">Seleccione</option>
                <option value="activo">✅ Activo</option>
                <option value="congelado">🧊 Congelado</option>
                <option value="retirado">🚫 Retirado</option>
                <option value="egresado">🎓 Egresado</option>
            </select>
            <button class="btn-aceptar-estado" onclick="guardarNuevoEstado()">Aceptar</button>
        </div>
        
 
    </div>

    <script>
        const btn_estado = document.getElementById("estado-actual")
const aceptar_btn_estado = document.querySelector(".btn-aceptar-estado")
const container_cambiar_estado = document.querySelector(".cambiar-estado-container")

let estadoOpen = false
btn_estado.addEventListener("click", ()=>{
    if(estadoOpen == false){
        container_cambiar_estado.classList.add("active-cambiar-estado")
   btn_estado.classList.add("active-cambiar-estado")
   estadoOpen = true
    }

    else{
        container_cambiar_estado.classList.remove("active-cambiar-estado")
        btn_estado.classList.remove("active-cambiar-estado")
        estadoOpen = false
    }
   
})

aceptar_btn_estado.addEventListener("click", ()=>{
   container_cambiar_estado.classList.remove("active-cambiar-estado")
   btn_estado.classList.remove("active-cambiar-estado")
})
    </script>
   

    

                
                <div class="student-info">
                    <h3>Datos Personales</h3>
                    <div class="info-item">
                        <i class="fas fa-id-card"></i>
                        <div><strong>Cedula de identidad:</strong> <span id="cedula"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-birthday-cake"></i>
                        <div><strong>Fecha Nac.:</strong> <span id="fecha_nacimiento"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-venus-mars"></i>
                        <div><strong>Genero:</strong> <span id="sexo"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-ring"></i>
                        <div><strong>Estado Civil:</strong> <span id="estado_civil"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div><strong>Teléfono Celular:</strong> <span id="telefono_celular"></span></div>
                    </div>
                 
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div><strong>Email:</strong> <span id="correo_electronico"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-earth-americas"></i>
                        <div><strong>Etnia Aborigen:</strong> <span id="etnia_aborigen"></span></div>
                    </div>
               
                </div>
                
                <div class="student-info academic-info">
                    <h3><i class="fas fa-university"></i> Información Académica</h3>
                    
                    <!-- Contenedor donde JS inyectará dinámicamente cada carrera -->
                    <div id="contenedor-carreras" class="carreras-container">
                        <!-- JS llenará los bloques .carrera-bloque aquí -->
                    </div>

                    <!-- Promedio General Fijo -->
                    <div class="info-item promedio-item">
                   
                        <div>
                            <strong>Promedio General:</strong> 
                            <span id="promedio-estudiante" class="promedio-badge">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="student-info">
                    <button class="btn-more-date" id="btn-more-residence">
                        <h3>Datos de Residencia</h3>
                        <i class="fa-solid fa-angle-down"></i>
                    </button>
                    <div id="info-residence" class="info-residence">
                        <div class="info-item">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <div><strong>Estado:</strong> <span id="estado"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <div><strong>Municipio:</strong> <span id="municipio"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <div><strong>Parroquia:</strong> <span id="parroquia"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-road"></i>
                            <div><strong>Avenida:</strong> <span id="avenida"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-road"></i>
                            <div><strong>Calle:</strong> <span id="calle_sector"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-house-chimney-user"></i>
                            <div><strong>Casa/Apto:</strong> <span id="casa_apartamento"></span></div>
                        </div>
                   
                        <div class="info-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <div><strong>Punto de referencia:</strong> <span id="punto_referencia"></span></div>
                        </div>
                    </div>
                    
                    
                </div>

                <div class="student-info">
                     <button class="btn-more-date" id="btn-more-socioeconomic">
                        <h3>Datos Socioeconomicos</h3>
                        <i class="fa-solid fa-angle-down"></i>
                    </button>
                    
                    <div id="info-socioeconomic" class="info-socioeconomic">
                        <div class="info-item">
                            <i class="fa-solid fa-users"></i>
                            <div><strong>Grupo familiar:</strong> <span id="grupo_familiar"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-users"></i>
                            <div><strong>Personas a cargo:</strong>  <span id="personas_a_cargo"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-money-bill-1-wave"></i>
                            <div><strong>Fuente de ingresos:</strong>  <span id="fuente_ingresos"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-chart-line"></i>
                            <div><strong>Ingresos mensuales del grupo familiar:</strong> <span id="ingreso_mensual"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-house"></i>
                            <div><strong>Tipo de vivienda:</strong> <span id="tipo_vivienda"></span></div>
                        </div>
                        <div class="info-item">
                            <i class="fa-solid fa-house"></i>
                            <div><strong>Tenencia de la vivienda:</strong> <span id="tenencia_vivienda"></span></div>
                        </div>
                     
                    </div>
                    
               

                </div>

               
             
               
            </div>
            
            <div class="right-column">

            <!-- 1. jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- 2. autoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>




            <div class="academic-history">
    <h3 class="section-title">Historial Académico</h3>

    <!-- Contenedor de periodos -->
    <div class="nav-buttons-container" id="nav-periodos">
    </div>

<div id="historial-contenido" class="container-table">
    <p class="text-muted">Seleccione un periodo para ver el detalle.</p>
</div>

<div class="btn-download-history">
    <button id="btn-export-general">Exportar Historial General PDF</button>

</div>



</div>


<div class="academic-history">
    <h3 class="section-title">Historial Académico Inicial</h3>

    <!-- Contenedor de periodos -->
    <div id="historial-inicial-periodos" class="container-table">
        <!-- JS agregará aquí cada trayecto/periodo -->
    </div>



   
<div class="btn-download-history">
    <button id="btn-export-inicial">Exportar Historial Inicial PDF</button>

</div>

</div>

<!-- 3. Tu script que exporta PDF -->
<script src="exportar_historial.js"></script>
              
                
                <div class="progress-section">
    <div class="progress-item">
        <h4>Progreso de Carrera (No incluye Trayecto Inicial)</h4>
        <div class="progress-bar">
            <div id="bar-progreso" class="progress-fill" style="width: 0%"></div>
        </div>
        <p id="txt-progreso">0% completado</p>
    </div>
    
    <div class="progress-item">
        <h4>Rendimiento Académico (No incluye Trayecto Inicial)</h4>
        <div class="progress-bar">
            <div id="bar-rendimiento" class="progress-fill" style="width: 0%"></div>
        </div>
        <p id="txt-promedio">Promedio: 0</p>
    </div>
</div>


                
                
            
                
                <div class="action-buttons">
                    <button onclick="generarPDF()" class="btn btn-primary">
                        <i class="fas fa-download"></i> Exportar PDF
                    </button>
                    <button id="btnEditar" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Editar Información
                    </button>
                   
                </div>
            </div>


             
        </div>













      

    </div>
    
    </div>

    

<script src="../script_bar_nav.js"></script>

<script src="ficha_estudiante.js"></script>
<script src="../mensajes.js"></script>

</body>
</htm
