<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Respaldo Base de Datos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../styles_bar_nav.css">
  <link rel="stylesheet" href="respaldo.css">
</head>
<body>
<script src="../bar_nav.js"></script>
<div class="container-slidebar"></div>

<div class="main-content">
    <div class="container-topbar"></div>
    <div class="content">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-database icon-main"></i>
            </div>
            <h2>Respaldo de Datos</h2>
            <p>Haz clic en el botón de abajo para exportar toda la información del sistema en un archivo .sql.</p>
            
            <div class="alerta-info">
                <i class="fas fa-info-circle"></i>
                <span>Se descargará un archivo que podrás restaurar en cualquier servidor MySQL.</span>
            </div>

            <a href="api_respaldar.php" class="btn-respaldo" id="btnDescargar">
                <i class="fas fa-download"></i> Generar y Descargar SQL
            </a>
        </div>
    </div>
</div>

<script src="../script_bar_nav.js"></script>
<script>
    document.getElementById('btnDescargar').addEventListener('click', function(e) {
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    btn.classList.add('disabled'); // Añade una clase CSS en lugar de pointer-events si puedes

    // Simplemente dejamos que el enlace siga su curso natural
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('disabled');
    }, 4000);
});
</script>
</body>
</html>