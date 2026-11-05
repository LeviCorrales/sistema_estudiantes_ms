<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Guía de uso del Sistema de Gestión Estudiantil.">
    <title>Guía de uso | Sistema de Gestión Estudiantil</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../styles_bar_nav.css">
    <link rel="stylesheet" href="instrucciones.css">
</head>
<body>

<script src="../bar_nav.js"></script>
<div class="container-slidebar"></div>

<div class="main-content">
    <div class="container-topbar"></div>

    <main class="content">
        <div class="guide-container">

            <!-- ENCABEZADO -->
            <header class="guide-header">
                <div class="guide-header-icon">
                    <i class="fa-solid fa-book-open-reader"></i>
                </div>

                <div class="guide-header-text">
                    <span class="guide-eyebrow">CENTRO DE AYUDA</span>
                    <h1>Guía de uso del sistema</h1>
                    <p>
                        Instrucciones esenciales para administrar estudiantes,
                        organizar la información académica y proteger los datos.
                    </p>
                </div>
            </header>

            <!-- AVISO PRINCIPAL -->
            <section class="guide-notice">
                <div class="notice-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <h2>Antes de comenzar</h2>
                    <p>
                        Verifica que estés trabajando en el sistema correcto y que
                        la información que registres sea exacta. Antes de realizar
                        cambios importantes, asegúrate de contar con un respaldo
                        reciente de la base de datos. Recuerda que las fotos de perfil
                        no forman parte de ese respaldo: se conservan aparte.
                    </p>
                </div>
            </section>

            <!-- NAVEGACIÓN RÁPIDA -->
            <section class="guide-section" id="inicio">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">CONSULTA RÁPIDA</span>
                        <h2>¿Qué necesitas hacer?</h2>
                        <p>Selecciona una categoría para ir directamente a sus instrucciones.</p>
                    </div>
                </div>

                <div class="guide-navigation">
                    <a href="#estudiantes" class="guide-nav-card">
                        <span class="nav-icon nav-purple">
                            <i class="fa-solid fa-user-graduate"></i>
                        </span>
                        <span>
                            <strong>Estudiantes</strong>
                            <small>Registro y actualización</small>
                        </span>
                        <i class="fa-solid fa-arrow-right nav-arrow"></i>
                    </a>

                    <a href="#estructura" class="guide-nav-card">
                        <span class="nav-icon nav-blue">
                            <i class="fa-solid fa-sitemap"></i>
                        </span>
                        <span>
                            <strong>Estructura académica</strong>
                            <small>Carreras, trayectos y períodos</small>
                        </span>
                        <i class="fa-solid fa-arrow-right nav-arrow"></i>
                    </a>

                    <a href="#grupos" class="guide-nav-card">
                        <span class="nav-icon nav-green">
                            <i class="fa-solid fa-people-group"></i>
                        </span>
                        <span>
                            <strong>Grupos y asignaciones</strong>
                            <small>Organización estudiantil</small>
                        </span>
                        <i class="fa-solid fa-arrow-right nav-arrow"></i>
                    </a>

                    <a href="#notas" class="guide-nav-card">
                        <span class="nav-icon nav-orange">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </span>
                        <span>
                            <strong>Notas e historial</strong>
                            <small>Registro y seguimiento académico</small>
                        </span>
                        <i class="fa-solid fa-arrow-right nav-arrow"></i>
                    </a>

                    <a href="#estados" class="guide-nav-card">
                        <span class="nav-icon nav-pink">
                            <i class="fa-solid fa-user-gear"></i>
                        </span>
                        <span>
                            <strong>Estados del estudiante</strong>
                            <small>Activos, congelados, retirados y egresados</small>
                        </span>
                        <i class="fa-solid fa-arrow-right nav-arrow"></i>
                    </a>

                    <a href="#respaldos" class="guide-nav-card">
                        <span class="nav-icon nav-teal">
                            <i class="fa-solid fa-database"></i>
                        </span>
                        <span>
                            <strong>Respaldos y seguridad</strong>
                            <small>Copias, cambio de equipo y fotos</small>
                        </span>
                        <i class="fa-solid fa-arrow-right nav-arrow"></i>
                    </a>

                    <a href="#acceso" class="guide-nav-card">
                        <span class="nav-icon nav-blue">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <span>
                            <strong>Acceso y contraseña</strong>
                            <small>Inicio de sesión y códigos de respaldo</small>
                        </span>
                        <i class="fa-solid fa-arrow-right nav-arrow"></i>
                    </a>
                </div>
            </section>

            <!-- ESTUDIANTES -->
            <section class="guide-section" id="estudiantes">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">01 · REGISTROS</span>
                        <h2><i class="fa-solid fa-user-graduate"></i> Gestión de estudiantes</h2>
                        <p>Cómo registrar, consultar y mantener actualizada la información personal.</p>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon purple">
                            <i class="fa-solid fa-user-plus"></i>
                        </span>
                        <div>
                            <h3>Registrar un estudiante</h3>
                            <p>Sigue este orden antes de guardar la información.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>Abre el formulario de registro de estudiantes. Puedes seleccionar la carga masiva para registrar un máximo de 50 estudiantes a la vez (se recomiendan 25).</li>
                        <li>Introduce la cédula y verifica que corresponda a la persona correcta. El sistema no permite cédulas duplicadas.</li>
                        <li>Completa los nombres, apellidos, datos de contacto y demás campos solicitados. Los campos marcados con asterisco son obligatorios; los demás pueden completarse después.</li>
                        <li>Revisa la información antes de guardar, especialmente la cédula, el correo y los teléfonos. Los errores se muestran junto al campo o debajo del botón de guardar.</li>
                        <li>Confirma que el registro se haya guardado correctamente.</li>
                    </ol>

                    <a class="guide-action"
                       href="../formulario_estudiantes/index_form.php">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Ir al registro de estudiantes
                    </a>
                </div>

                <div class="guide-tip">
                    <i class="fa-solid fa-table-cells"></i>
                    <div>
                        <strong>Cómo funciona la carga masiva</strong>
                        <p>
                            Se parece a una hoja de cálculo y está dividida en tres tablas:
                            datos personales, residenciales y socioeconómicos. Puedes sombrear
                            y autorrellenar celdas, moverte entre ellas y usar Ctrl + Z y
                            Ctrl + Y para deshacer y rehacer. Puedes indicar cuántas filas
                            agregar (máximo 50) o añadirlas una a una. Al guardar se vuelven
                            a validar las cédulas duplicadas y los formatos.
                        </p>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon blue">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <div>
                            <h3>Buscar y consultar un estudiante</h3>
                            <p>Antes de crear un registro nuevo, comprueba si ya existe.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>Entra al listado de estudiantes. Por defecto muestra solo los estudiantes activos.</li>
                        <li>Escribe una cédula, nombre o apellido, o usa los filtros por carrera, grupo, trayecto, período y estado. El listado se actualiza mientras escribes.</li>
                        <li>Comprueba su cédula y sus datos para evitar confundirla con otro estudiante.</li>
                        <li>Abre su ficha para revisar la información y realizar las acciones permitidas.</li>
                    </ol>

                    <a class="guide-action"
                       href="../listado/listado.php">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Ir al listado de estudiantes
                    </a>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon green">
                            <i class="fa-solid fa-id-card"></i>
                        </span>
                        <div>
                            <h3>Qué puedes hacer desde la ficha</h3>
                            <p>La ficha reúne los datos y el seguimiento académico de la persona.</p>
                        </div>
                    </div>

                    <ul class="guide-list">
                        <li>Consultar sus datos personales, las carreras en que está inscrito y su trayecto y período.</li>
                        <li>Revisar el historial académico de las carreras y, por separado, el del trayecto inicial. Cada uno se puede exportar a PDF.</li>
                        <li>Ver estadísticas de progreso y rendimiento. Se calculan solo con las carreras; el trayecto inicial no se incluye.</li>
                        <li>Agregar una fotografía. Si no hay foto, se muestra la inicial del nombre.</li>
                        <li>Cambiar el estado del estudiante con el botón de estado.</li>
                        <li>Exportar un PDF con sus datos (sin historial académico) o editar su registro en un formulario similar al de inscripción.</li>
                    </ul>
                </div>
            </section>

            <!-- ESTRUCTURA ACADÉMICA -->
            <section class="guide-section" id="estructura">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">02 · CONFIGURACIÓN</span>
                        <h2><i class="fa-solid fa-sitemap"></i> Estructura académica</h2>
                        <p>Mantén organizados los elementos que utiliza el sistema para gestionar la actividad académica.</p>
                    </div>
                </div>

                <div class="workflow-card">
                    <div class="workflow-heading">
                        <i class="fa-solid fa-list-ol"></i>
                        <h3>Orden recomendado de configuración</h3>
                    </div>

                    <div class="workflow-list">
                        <div class="workflow-step">
                            <span class="workflow-number">1</span>
                            <div>
                                <strong>Carreras</strong>
                                <p>Crea la carrera y verifica que esté activa antes de utilizarla. Con clic derecho sobre ella puedes cambiar su nombre, inhabilitarla o eliminarla.</p>
                            </div>
                        </div>

                        <div class="workflow-step">
                            <span class="workflow-number">2</span>
                            <div>
                                <strong>Trayectos y períodos</strong>
                                <p>Define las etapas académicas y los períodos de la carrera. Esta configuración se hace una sola vez: después de confirmarla, la opción desaparece.</p>
                            </div>
                        </div>

                        <div class="workflow-step">
                            <span class="workflow-number">3</span>
                            <div>
                                <strong>Materias</strong>
                                <p>Registra cada materia con su nombre, código, horas y créditos. Con clic derecho puedes cambiar su nombre o eliminarla. En el pensum del trayecto inicial las materias no se renombran: si necesitas otro nombre, elimínala y regístrala de nuevo.</p>
                            </div>
                        </div>

                        <div class="workflow-step">
                            <span class="workflow-number">4</span>
                            <div>
                                <strong>Grupos</strong>
                                <p>Crea los grupos de la carrera o del trayecto inicial y comprueba su información antes de incorporar estudiantes.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="guide-tip">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Consejo importante</strong>
                        <p>
                            Configura primero la estructura académica y después
                            realiza las asignaciones. Si quieres dejar de usar una
                            carrera, inhabilítala en lugar de eliminarla. Evita eliminar
                            elementos que ya tengan grupos, estudiantes o historiales
                            asociados sin comprobar antes las consecuencias.
                        </p>
                    </div>
                </div>

                <div class="guide-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Revisa antes de confirmar trayectos y períodos</strong>
                        <p>
                            Una vez definidos, no se pueden modificar desde la misma
                            función. Eliminar una carrera o una materia es una operación
                            definitiva: antes de hacerlo, revisa si tiene registros asociados.
                        </p>
                    </div>
                </div>

                <a class="guide-action"
                   href="../carreras/index_carrera.php">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    Ir a gestión de carreras
                </a>
            </section>

            <!-- GRUPOS -->
            <section class="guide-section" id="grupos">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">03 · ORGANIZACIÓN</span>
                        <h2><i class="fa-solid fa-people-group"></i> Grupos y asignaciones</h2>
                        <p>Buenas prácticas para organizar a los estudiantes en la estructura académica.</p>
                    </div>
                </div>

                <div class="guide-grid-two">
                    <article class="instruction-card compact-card">
                        <span class="step-icon green">
                            <i class="fa-solid fa-user-check"></i>
                        </span>
                        <h3>Antes de asignar</h3>
                        <ul class="guide-list">
                            <li>Confirma que el estudiante exista en el sistema.</li>
                            <li>Verifica que el grupo corresponda a la carrera y al período adecuados.</li>
                            <li>Comprueba que la asignación no esté duplicada.</li>
                            <li>Revisa que el estudiante sea el correcto antes de confirmar.</li>
                        </ul>
                    </article>

                    <article class="instruction-card compact-card">
                        <span class="step-icon orange">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </span>
                        <h3>Después de asignar</h3>
                        <ul class="guide-list">
                            <li>Verifica que el estudiante aparezca en el grupo correspondiente.</li>
                            <li>Comprueba la información académica relacionada con la asignación.</li>
                            <li>Revisa que las consultas y fichas reflejen los cambios esperados.</li>
                            <li>Corrige las asignaciones erróneas mediante las opciones disponibles en el sistema.</li>
                        </ul>
                    </article>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon blue">
                            <i class="fa-solid fa-user-plus"></i>
                        </span>
                        <div>
                            <h3>Incorporar y quitar estudiantes de un grupo</h3>
                            <p>Ambas acciones se hacen desde el grupo.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>Entra al grupo y pulsa el botón de nuevo estudiante (en el trayecto inicial, usa el campo de búsqueda del grupo).</li>
                        <li>Busca por cédula, nombre o apellido. Los resultados se filtran mientras escribes.</li>
                        <li>Selecciona al estudiante previamente registrado para incorporarlo.</li>
                        <li>Para quitarlo, haz clic derecho sobre el estudiante en el listado del grupo y elige el botón de eliminar.</li>
                    </ol>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon teal">
                            <i class="fa-solid fa-gears"></i>
                        </span>
                        <div>
                            <h3>Administrar un grupo</h3>
                            <p>Opciones disponibles dentro de cada carrera.</p>
                        </div>
                    </div>

                    <ul class="guide-list">
                        <li>Con clic derecho sobre la tarjeta del grupo puedes cambiar su nombre, congelarlo o eliminarlo. Si ya tiene registros asociados, congélalo en lugar de eliminarlo.</li>
                        <li>Define un vocero y avanza o retrocede entre los períodos configurados.</li>
                        <li>Establece las fechas de inicio y finalización del período cursado: de ellas depende la generación de los reportes de notas.</li>
                    </ul>
                </div>

                <div class="guide-tip">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Restricciones de inscripción</strong>
                        <p>
                            El sistema no permite que un estudiante esté en grupos
                            incompatibles de una misma carrera, ni que se inscriba en una
                            carrera o en otro grupo mientras cursa el trayecto inicial. Sí
                            permite que un estudiante ya registrado ingrese a otra carrera.
                        </p>
                    </div>
                </div>

                <div class="guide-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>No confundas una asignación con el historial académico</strong>
                        <p>
                            La relación de un estudiante con un grupo y sus calificaciones
                            son datos distintos. Al quitar a un estudiante de un grupo se
                            conserva su historial, pero antes verifica cómo afectará las
                            consultas y los registros históricos.
                        </p>
                    </div>
                </div>
            </section>

            <!-- NOTAS -->
            <section class="guide-section" id="notas">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">04 · SEGUIMIENTO</span>
                        <h2><i class="fa-solid fa-clipboard-check"></i> Notas e historial académico</h2>
                        <p>Registra los resultados con precisión y conserva la trazabilidad académica.</p>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon orange">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>
                        <div>
                            <h3>Al registrar o modificar una nota</h3>
                            <p>Utiliza esta lista de comprobación.</p>
                        </div>
                    </div>

                    <ul class="checklist">
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Confirma la identidad del estudiante y la materia correspondiente.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Comprueba que el grupo, el trayecto y el período seleccionados sean los correctos.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Comprueba que la nota coincida con el registro académico autorizado.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Verifica que el resultado se haya guardado y que el estado académico sea coherente.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Si corriges una nota anterior, comprueba que el promedio y el historial se actualicen como corresponde.
                        </li>
                    </ul>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon purple">
                            <i class="fa-solid fa-floppy-disk"></i>
                        </span>
                        <div>
                            <h3>Guardar el historial y generar reportes</h3>
                            <p>Cada grupo tiene su sección de calificaciones.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>Dentro del grupo, selecciona el trayecto y el período cuyas notas vas a gestionar.</li>
                        <li>Registra el profesor de cada materia y las calificaciones. En el trayecto inicial, las notas se guardan al presionar Enter o al cambiar de campo.</li>
                        <li>Pulsa «guardar historial» y verifica el mensaje de confirmación.</li>
                        <li>Genera los reportes en PDF con los formatos de la Misión Sucre: uno general del grupo y uno por asignatura para el período seleccionado.</li>
                        <li>Cuando las notas del período estén revisadas y finalizadas, usa la función de consolidación para preservar ese historial, incluso si después cambian las materias del pensum.</li>
                    </ol>
                </div>

                <div class="guide-tip">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Sobre los estados académicos</strong>
                        <p>
                            El sistema utiliza estados como pendiente, aprobada,
                            reprobada y congelada. Comprueba la nota y el estado
                            resultante después de cada modificación. No des por
                            hecho que un registro cambió correctamente hasta verificarlo.
                        </p>
                    </div>
                </div>

                <div class="guide-tip">
                    <i class="fa-solid fa-flag-checkered"></i>
                    <div>
                        <strong>Culminar el trayecto inicial y egresar</strong>
                        <p>
                            Cuando termine el trayecto inicial, usa el botón de culminación
                            e indica el mes y el año de finalización. En el último período
                            configurado de una carrera aparece la opción de egresar al grupo
                            y a sus estudiantes; hazlo solo cuando la trayectoria académica
                            esté completa.
                        </p>
                    </div>
                </div>

                <div class="guide-warning">
                    <i class="fa-solid fa-shield-halved"></i>
                    <div>
                        <strong>Protege el historial</strong>
                        <p>
                            No borres registros académicos solo para corregir un error
                            sin verificar primero el procedimiento adecuado. Los
                            historiales pueden ser necesarios para consultar notas y
                            calcular promedios posteriormente. Si trasladaste a un
                            estudiante, comprueba el grupo seleccionado antes de guardar
                            o consolidar notas de un período ya consolidado.
                        </p>
                    </div>
                </div>
            </section>

            <!-- ESTADOS -->
            <section class="guide-section" id="estados">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">05 · ESTADO DEL ESTUDIANTE</span>
                        <h2><i class="fa-solid fa-user-gear"></i> ¿Qué significa cada estado?</h2>
                        <p>Utiliza el estado que refleje la situación real de la persona.</p>
                    </div>
                </div>

                <div class="status-grid">
                    <article class="status-card">
                        <span class="status-badge status-active">Activo</span>
                        <h3>Continúa sus estudios</h3>
                        <p>Es el estado predeterminado. Se utiliza cuando el estudiante mantiene su condición activa en el sistema.</p>
                    </article>

                    <article class="status-card">
                        <span class="status-badge status-frozen">Congelado</span>
                        <h3>Estudios pausados</h3>
                        <p>Identifica a quien tiene su actividad académica temporalmente suspendida. Al aplicarlo, el estudiante sale automáticamente de todos sus grupos y conserva su historial.</p>
                    </article>

                    <article class="status-card">
                        <span class="status-badge status-retired">Retirado</span>
                        <h3>Se retiró de los estudios</h3>
                        <p>Identifica a quien dejó de continuar sus estudios. Al aplicarlo, sale automáticamente de todos sus grupos y conserva su historial.</p>
                    </article>

                    <article class="status-card">
                        <span class="status-badge status-graduated">Egresado</span>
                        <h3>Finalizó su formación</h3>
                        <p>Identifica a quien culminó su formación según los criterios de la institución.</p>
                    </article>
                </div>

                <div class="guide-tip">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Buena práctica</strong>
                        <p>
                            Cambia el estado desde el botón de estado de la ficha del
                            estudiante, conforme a su situación real, y conserva su
                            registro. No utilices la eliminación como sustituto de
                            un cambio de estado.
                        </p>
                    </div>
                </div>

                <div class="guide-warning">
                    <i class="fa-solid fa-rotate"></i>
                    <div>
                        <strong>Qué pasa al reincorporar a un estudiante</strong>
                        <p>
                            Si lo vuelves a incorporar al mismo grupo, recupera sus notas.
                            Si lo incorporas a otro grupo de la misma carrera y vuelves a
                            escribir las notas de un período que ya cursó, esas notas se
                            reescriben, porque el sistema toma las últimas como las más
                            actuales. Revisa el grupo y el período antes de guardar.
                        </p>
                    </div>
                </div>
            </section>

            <!-- RESPALDOS -->
            <section class="guide-section" id="respaldos">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">06 · SEGURIDAD</span>
                        <h2><i class="fa-solid fa-database"></i> Respaldos y protección de datos</h2>
                        <p>Una copia de seguridad es esencial para recuperar la información ante fallos, errores o un cambio de equipo.</p>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon teal">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </span>
                        <div>
                            <h3>Generar un respaldo</h3>
                            <p>Realiza estas comprobaciones cada vez que generes un respaldo.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>Abre el módulo de respaldos.</li>
                        <li>Ejecuta la opción para generar la copia. Se descarga un archivo SQL con la fecha correspondiente.</li>
                        <li>Espera la confirmación de que el proceso terminó correctamente.</li>
                        <li>Comprueba que el archivo exista y que no esté vacío o incompleto.</li>
                        <li>Guárdalo en un medio seguro o en el servicio de almacenamiento en la nube de la institución, separado de los archivos originales. Descargarlo no significa que ya esté protegido fuera del equipo.</li>
                    </ol>

                    <a class="guide-action" href="../respaldo/respaldo.php">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Ir al módulo de respaldos
                    </a>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon blue">
                            <i class="fa-solid fa-computer"></i>
                        </span>
                        <div>
                            <h3>Cambiar de equipo o reinstalar el sistema</h3>
                            <p>El sistema se instala desde un archivo .exe y la base de datos queda vacía en el equipo nuevo. Hay que restaurarla con tu último respaldo.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>Antes de desinstalar el sistema en el equipo anterior, genera un respaldo y copia la carpeta de fotos (consulta la advertencia más abajo).</li>
                        <li>Instala el sistema en el equipo nuevo e inicia la aplicación web.</li>
                        <li>Abre <code>http://localhost/phpmyadmin/</code> en el navegador.</li>
                        <li>Elimina la base de datos llamada <strong>estudiantes</strong>.</li>
                        <li>Crea de nuevo una base de datos con el mismo nombre, <strong>estudiantes</strong>. Quedará vacía.</li>
                        <li>Selecciona la base de datos <strong>estudiantes</strong>, entra a la pestaña «Importar», elige el archivo SQL del último respaldo y ejecuta la importación.</li>
                        <li>Espera el mensaje de que la importación terminó correctamente.</li>
                        <li>Abre el sistema y comprueba que aparezcan los estudiantes, grupos y notas.</li>
                    </ol>
                </div>

                <div class="guide-warning">
                    <i class="fa-solid fa-image"></i>
                    <div>
                        <strong>Las fotos de perfil no se incluyen en el respaldo</strong>
                        <p>
                            Si no las copias antes de eliminar o desinstalar el sistema, se
                            perderán. Antes de hacerlo, entra a la carpeta
                            <code>C:\sistema_estudiantes_ms\xampp\htdocs\sistema_gestion_aldea\estudiantes</code>
                            y copia la carpeta <strong>uploads</strong>, que contiene las imágenes.
                            Después de instalar el sistema, entra a esa misma ruta y coloca
                            allí la carpeta <strong>uploads</strong> copiada.
                        </p>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon orange">
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <div>
                            <h3>Lista de verificación para cambiar de equipo</h3>
                            <p>Márcala en orden para no dejar nada atrás.</p>
                        </div>
                    </div>

                    <ul class="checklist">
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Generé un respaldo reciente de la base de datos (archivo SQL).
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Copié la carpeta <strong>uploads</strong> con las fotos de perfil.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Guardé ambos en un medio externo o en la nube, y comprobé que se abren.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Instalé el sistema en el equipo nuevo e inicié la aplicación web.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Restauré la base de datos <strong>estudiantes</strong> desde phpMyAdmin.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Coloqué la carpeta <strong>uploads</strong> en su ruta original.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Verifiqué en el sistema los estudiantes, las notas y las fotos.
                        </li>
                    </ul>
                </div>

                <div class="guide-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Un respaldo guardado no garantiza que pueda restaurarse</strong>
                        <p>
                            Comprueba periódicamente que el archivo sea legible y que el
                            procedimiento de restauración funcione. Haz esas pruebas en otro
                            equipo o entorno de prueba, no sobre la base de datos principal
                            en uso.
                        </p>
                    </div>
                </div>
            </section>

            <!-- ACCESO Y CONTRASEÑA -->
            <section class="guide-section" id="acceso">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">07 · ACCESO</span>
                        <h2><i class="fa-solid fa-key"></i> Acceso y cambio de contraseña</h2>
                        <p>Cómo iniciar sesión, cambiar tu clave y qué hacer si la olvidas.</p>
                    </div>
                </div>

                <div class="guide-tip">
                    <i class="fa-solid fa-ticket"></i>
                    <div>
                        <strong>Tus 7 códigos de respaldo</strong>
                        <p>
                            Al instalar el sistema se te entregan 7 códigos de respaldo.
                            Sirven para recuperar el acceso si olvidas tu contraseña.
                            Guárdalos en un lugar seguro desde el primer día.
                        </p>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon blue">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <div>
                            <h3>Cambiar la clave cuando ya iniciaste sesión</h3>
                            <p>Es el método recomendado: no consume ningún código de respaldo.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>Inicia sesión con tu contraseña actual.</li>
                        <li>Abre la página de cambio de clave dentro del sistema.</li>
                        <li>Introduce tu contraseña actual. Solo se puede cambiar si la conoces, algo lógico si lograste entrar.</li>
                        <li>Escribe la nueva clave y confírmala.</li>
                        <li>Guarda el cambio y usa la nueva clave la próxima vez que inicies sesión.</li>
                    </ol>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon orange">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <div>
                            <h3>Recuperar el acceso si olvidaste la contraseña</h3>
                            <p>Úsalo solo cuando no puedas entrar con tu clave.</p>
                        </div>
                    </div>

                    <ol class="instruction-steps">
                        <li>En la pantalla de inicio de sesión, elige la opción de cambiar la clave porque no la recuerdas.</li>
                        <li>Ten a mano tus códigos de respaldo e introduce <strong>uno</strong> de ellos.</li>
                        <li>Define tu nueva contraseña y confírmala.</li>
                        <li>Anota que ese código ya fue usado y quedó inutilizable; ya no podrás volver a emplearlo.</li>
                        <li>Inicia sesión con tu nueva clave y guárdala en un lugar seguro.</li>
                    </ol>
                </div>

                <div class="guide-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Cada código sirve una sola vez</strong>
                        <p>
                            Cuando usas un código de respaldo, queda inutilizable. No tomes
                            a la ligera el cambio de clave por olvido: si se agotan los 7
                            códigos y no recuerdas tu contraseña, la única salida es
                            reinstalar el sistema. Eso deja la base de datos vacía, y tendrás
                            que restaurar el último respaldo y la carpeta de fotos como se
                            explica en la sección <a href="#respaldos">Respaldos y protección de datos</a>.
                        </p>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-title">
                        <span class="step-icon teal">
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <div>
                            <h3>Cuida tu acceso</h3>
                            <p>Pequeños hábitos que evitan una reinstalación.</p>
                        </div>
                    </div>

                    <ul class="checklist">
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Guardé mis 7 códigos de respaldo en un lugar seguro y fuera del sistema.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Llevo la cuenta de cuántos códigos me quedan sin usar.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Elegí una contraseña que puedo recordar y la guardé de forma segura.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            Si quiero cambiar mi clave y la recuerdo, uso la página de cambio de clave dentro del sistema.
                        </li>
                        <li>
                            <i class="fa-regular fa-square-check"></i>
                            No comparto mi contraseña ni mis códigos con personas no autorizadas.
                        </li>
                    </ul>
                </div>
            </section>

            <!-- BUENAS PRÁCTICAS -->
            <section class="guide-section" id="buenas-practicas">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">RECOMENDACIONES FINALES</span>
                        <h2><i class="fa-solid fa-list-check"></i> Reglas de oro</h2>
                        <p>Hábitos sencillos que ayudan a mantener la información confiable.</p>
                    </div>
                </div>

                <div class="rules-grid">
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Verifica los datos antes de guardar.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Busca al estudiante antes de registrarlo otra vez.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Revisa las asignaciones antes de modificar grupos.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Inhabilita o congela en lugar de eliminar.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Verifica notas e historiales después de cada cambio.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Consolida solo períodos con notas revisadas.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Realiza respaldos antes de operaciones importantes y al cambiar de equipo.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Copia la carpeta uploads antes de desinstalar: las fotos no van en el respaldo.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>Guarda tus 7 códigos de respaldo: cada uno sirve una sola vez.</p>
                    </div>
                    <div class="rule-item">
                        <span><i class="fa-solid fa-check"></i></span>
                        <p>No compartas copias de datos personales sin autorización.</p>
                    </div>
                </div>
            </section>

            <footer class="guide-footer">
                <div class="footer-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <strong>Sistema de Gestión Estudiantil</strong>
                    <p>Una administración ordenada comienza con información confiable.</p>
                </div>
                <a href="#inicio" class="back-to-top" aria-label="Volver al inicio" title="Volver al inicio">
                    <i class="fa-solid fa-arrow-up"></i>
                </a>
            </footer>

        </div>
    </main>
</div>

<script src="../script_bar_nav.js"></script>
</body>
</html>