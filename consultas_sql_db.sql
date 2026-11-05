-- CONSULTAS UNIFICADAS: ESTAS SON TODAS LAS CONSULTAS, PERO ESTAS VEZ UNIFICANDO MODIFCACIONES POSTERIORES, EL ORDEN DE EJECUCION IGUALEMNTE DE ARRIBA HASTA ABAJO.

-- SI VAS A CREAR LA DB DESDE CERO, LO MAS RECOMENDABLE ES USAR LAS CONSULTAS ORIGINALES

-- ============================================================
-- 1. DATOS PERSONALES
-- ============================================================

CREATE TABLE datos_personales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(15) NOT NULL UNIQUE,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50),
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50),
    sexo ENUM('M', 'F', 'O') NOT NULL,
    estado_civil ENUM(
        'Soltero',
        'Casado',
        'Divorciado',
        'Viudo',
        'Unión libre'
    ) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    telefono_celular VARCHAR(20),
    telefono_local VARCHAR(20),
    correo_electronico VARCHAR(150),
    etnia_aborigen VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM(
        'activo',
        'retirado',
        'congelado',
        'egresado'
    ) DEFAULT 'activo'
);

-- ============================================================
-- 2. UBICACIÓN GEOGRÁFICA
-- ============================================================

CREATE TABLE estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE municipios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    id_estado INT NOT NULL,
    FOREIGN KEY (id_estado)
        REFERENCES estados(id)
);

CREATE TABLE parroquias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    id_municipio INT NOT NULL,
    FOREIGN KEY (id_municipio)
        REFERENCES municipios(id)
);

CREATE TABLE direccion_residencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estado INT NOT NULL,
    id_municipio INT NOT NULL,
    id_parroquia INT NOT NULL,
    avenida VARCHAR(100),
    calle_sector VARCHAR(100),
    casa_apartamento VARCHAR(100),
    punto_referencia VARCHAR(150),

    FOREIGN KEY (id_estado)
        REFERENCES estados(id),

    FOREIGN KEY (id_municipio)
        REFERENCES municipios(id),

    FOREIGN KEY (id_parroquia)
        REFERENCES parroquias(id)
);

-- ============================================================
-- 3. DATOS SOCIOECONÓMICOS
-- ============================================================

CREATE TABLE datos_socioeconomicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    grupo_familiar INT NOT NULL,
    personas_a_cargo INT NOT NULL,
    fuente_ingresos VARCHAR(100) NOT NULL,
    ingreso_mensual DECIMAL(10,2) NOT NULL,
    tipo_vivienda VARCHAR(50) NOT NULL,
    tenencia_vivienda VARCHAR(50) NOT NULL,

    FOREIGN KEY (id_persona)
        REFERENCES datos_personales(id)
);

-- ============================================================
-- 4. CARRERAS
-- ============================================================

CREATE TABLE carreras (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre_carrera VARCHAR(255) NOT NULL,
    estado ENUM(
        'activa',
        'inhabilitada',
        'eliminada'
    ) DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 5. TRAYECTOS
-- ============================================================

CREATE TABLE trayectos (
    id_trayecto INT AUTO_INCREMENT PRIMARY KEY,
    id_carrera INT NOT NULL,
    numero_trayecto INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_carrera)
        REFERENCES carreras(id_carrera)
        ON DELETE CASCADE,

    UNIQUE KEY (id_carrera, numero_trayecto)
);

-- ============================================================
-- 6. PERÍODOS
-- ============================================================

CREATE TABLE periodos (
    id_periodo INT AUTO_INCREMENT PRIMARY KEY,
    id_trayecto INT NOT NULL,
    numero_periodo INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_trayecto)
        REFERENCES trayectos(id_trayecto)
        ON DELETE CASCADE,

    UNIQUE KEY (id_trayecto, numero_periodo)
);

-- ============================================================
-- 7. GRUPOS
-- ============================================================

CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    id_carrera INT NOT NULL,
    nombre_grupo VARCHAR(100) NOT NULL,
    id_trayecto INT NOT NULL,
    id_periodo INT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'activo',
    id_vocero INT DEFAULT NULL,

    FOREIGN KEY (id_carrera)
        REFERENCES carreras(id_carrera)
        ON DELETE CASCADE,

    FOREIGN KEY (id_trayecto)
        REFERENCES trayectos(id_trayecto)
        ON DELETE CASCADE,

    FOREIGN KEY (id_periodo)
        REFERENCES periodos(id_periodo)
        ON DELETE CASCADE
);

-- ============================================================
-- 8. ESTUDIANTES ASIGNADOS A GRUPOS
-- ============================================================

CREATE TABLE grupo_estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_estudiante INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE,

    FOREIGN KEY (id_estudiante)
        REFERENCES datos_personales(id)
        ON DELETE CASCADE,

    UNIQUE (id_grupo, id_estudiante)
);

-- ============================================================
-- 9. MATERIAS DE LAS CARRERAS
-- ============================================================

CREATE TABLE materias_carrera (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    id_carrera INT NOT NULL,
    id_trayecto INT NOT NULL,
    id_periodo INT NOT NULL,
    nombre_materia VARCHAR(255) NOT NULL,
    codigo_materia VARCHAR(50),
    creditos INT DEFAULT 0,
    horas_semanales INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activa', 'eliminada') DEFAULT 'activa',

    FOREIGN KEY (id_carrera)
        REFERENCES carreras(id_carrera)
        ON DELETE CASCADE,

    FOREIGN KEY (id_trayecto)
        REFERENCES trayectos(id_trayecto)
        ON DELETE CASCADE,

    FOREIGN KEY (id_periodo)
        REFERENCES periodos(id_periodo)
        ON DELETE CASCADE
);

-- ============================================================
-- 10. HISTORIAL ACADÉMICO DE CARRERAS
-- ============================================================

CREATE TABLE historial_grupo_materia (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,

    id_grupo INT NOT NULL,
    id_estudiante INT NOT NULL,
    id_materia INT NOT NULL,
    id_carrera INT NOT NULL,
    id_trayecto INT NOT NULL,
    id_periodo INT NOT NULL,

    profesor VARCHAR(255) DEFAULT NULL,

    nota INT DEFAULT NULL,

    estado ENUM(
        'Pendiente',
        'Aprobada',
        'Reprobada',
        'Congelada'
    ) DEFAULT 'Pendiente',

    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    consolidado TINYINT(1) DEFAULT 0,
    nombre_materia_snapshot VARCHAR(255) DEFAULT NULL,
    codigo_materia_snapshot VARCHAR(50) DEFAULT NULL,
    cedula_profesor_snapshot VARCHAR(20) DEFAULT NULL,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE,

    FOREIGN KEY (id_estudiante)
        REFERENCES datos_personales(id)
        ON DELETE CASCADE,

    FOREIGN KEY (id_materia)
        REFERENCES materias_carrera(id_materia)
        ON DELETE CASCADE,

    FOREIGN KEY (id_carrera)
        REFERENCES carreras(id_carrera)
        ON DELETE CASCADE,

    FOREIGN KEY (id_trayecto)
        REFERENCES trayectos(id_trayecto)
        ON DELETE CASCADE,

    FOREIGN KEY (id_periodo)
        REFERENCES periodos(id_periodo)
        ON DELETE CASCADE,

    UNIQUE KEY unica_materia_grupo_estudiante (
        id_grupo,
        id_estudiante,
        id_materia
    )
);

-- ============================================================
-- 11. TRAYECTO INICIAL
-- ============================================================

CREATE TABLE trayecto_inicial (
    id_trayecto_inicial INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo_inicial VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM(
        'activa',
        'inhabilitada',
        'eliminada'
    ) DEFAULT 'activa'
);

-- ============================================================
-- 12. ESTUDIANTES DEL TRAYECTO INICIAL
-- ============================================================

CREATE TABLE trayecto_inicial_estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_trayecto_inicial INT NOT NULL,
    id_estudiante INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_trayecto_inicial)
        REFERENCES trayecto_inicial(id_trayecto_inicial)
        ON DELETE CASCADE,

    FOREIGN KEY (id_estudiante)
        REFERENCES datos_personales(id)
        ON DELETE CASCADE,

    UNIQUE (id_trayecto_inicial, id_estudiante)
);

-- ============================================================
-- 13. PENSUM DEL TRAYECTO INICIAL
-- ============================================================

CREATE TABLE materias_pensum_inicial (
    id_materia_inicial INT AUTO_INCREMENT PRIMARY KEY,
    nombre_materia VARCHAR(255) NOT NULL,
    codigo_materia VARCHAR(50),
    creditos INT DEFAULT 0,
    horas_semanales INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

-- ============================================================
-- 14. MATERIAS ASIGNADAS A GRUPOS DEL TRAYECTO INICIAL
-- ============================================================

CREATE TABLE grupo_inicial_materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_trayecto_inicial INT NOT NULL,
    id_materia_inicial INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_trayecto_inicial)
        REFERENCES trayecto_inicial(id_trayecto_inicial)
        ON DELETE CASCADE,

    FOREIGN KEY (id_materia_inicial)
        REFERENCES materias_pensum_inicial(id_materia_inicial)
        ON DELETE CASCADE,

    UNIQUE KEY unica_materia_grupo (
        id_trayecto_inicial,
        id_materia_inicial
    )
);

-- ============================================================
-- 15. HISTORIAL ACADÉMICO DEL TRAYECTO INICIAL
-- ============================================================

CREATE TABLE historial_academico_inicial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,

    id_trayecto_inicial INT NOT NULL,
    id_grupo_estudiante INT DEFAULT NULL,
    id_estudiante INT NOT NULL,
    id_materia_inicial INT NOT NULL,

    nota INT DEFAULT NULL,

    estado ENUM(
        'Pendiente',
        'Aprobada',
        'Reprobada',
        'Congelada'
    ) DEFAULT 'Pendiente',

    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_trayecto_inicial)
        REFERENCES trayecto_inicial(id_trayecto_inicial)
        ON DELETE CASCADE,

    FOREIGN KEY (id_grupo_estudiante)
        REFERENCES trayecto_inicial_estudiantes(id)
        ON DELETE SET NULL,

    FOREIGN KEY (id_estudiante)
        REFERENCES datos_personales(id)
        ON DELETE CASCADE,

    FOREIGN KEY (id_materia_inicial)
        REFERENCES materias_pensum_inicial(id_materia_inicial)
        ON DELETE CASCADE,

    UNIQUE KEY unica_materia_estudiante (
        id_estudiante,
        id_materia_inicial
    )
);

-- ============================================================
-- 16. FECHAS POR GRUPO Y PERÍODO
-- ============================================================

CREATE TABLE grupo_periodo_fechas (
    id_grupo INT NOT NULL,
    id_periodo INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,

    PRIMARY KEY (id_grupo, id_periodo),

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
        ON DELETE CASCADE,

    FOREIGN KEY (id_periodo)
        REFERENCES periodos(id_periodo)
        ON DELETE CASCADE
);

-- ============================================================
-- 17. TRIGGERS DEL HISTORIAL ACADÉMICO INICIAL
-- ============================================================

DELIMITER //

CREATE TRIGGER trg_historial_estado_insert
BEFORE INSERT ON historial_academico_inicial
FOR EACH ROW
BEGIN
    IF NEW.nota IS NULL THEN
        SET NEW.estado = 'Pendiente';
    ELSEIF NEW.nota < 12 THEN
        SET NEW.estado = 'Reprobada';
    ELSE
        SET NEW.estado = 'Aprobada';
    END IF;
END;
//

CREATE TRIGGER trg_historial_estado_update
BEFORE UPDATE ON historial_academico_inicial
FOR EACH ROW
BEGIN
    IF NEW.nota IS NULL THEN
        SET NEW.estado = 'Pendiente';
    ELSEIF NEW.nota < 12 THEN
        SET NEW.estado = 'Reprobada';
    ELSE
        SET NEW.estado = 'Aprobada';
    END IF;
END;
//

-- ============================================================
-- 18. TRIGGERS DEL HISTORIAL DE CARRERAS
-- ============================================================

CREATE TRIGGER trg_historial_grupo_estado_insert
BEFORE INSERT ON historial_grupo_materia
FOR EACH ROW
BEGIN
    IF NEW.nota IS NULL THEN
        SET NEW.estado = 'Pendiente';
    ELSEIF NEW.nota < 12 THEN
        SET NEW.estado = 'Reprobada';
    ELSE
        SET NEW.estado = 'Aprobada';
    END IF;
END;
//

CREATE TRIGGER trg_historial_grupo_estado_update
BEFORE UPDATE ON historial_grupo_materia
FOR EACH ROW
BEGIN
    IF NEW.nota IS NULL THEN
        SET NEW.estado = 'Pendiente';
    ELSEIF NEW.nota < 12 THEN
        SET NEW.estado = 'Reprobada';
    ELSE
        SET NEW.estado = 'Aprobada';
    END IF;
END;
//

DELIMITER ;