-- CONSULTAS ORIGINALES: ESTAS SON TODAS LAS CONSULTAS QUE FUERON USADAS PARA CREAR LAS BASE DE DATOS, EL ORDEN DE EJECUCION DE ARRIBA HASTA ABAJO

CREATE TABLE datos_personales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(15) NOT NULL UNIQUE,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50),
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50),
    sexo ENUM('Masculino', 'Femenino', 'Otro') NOT NULL,
    estado_civil ENUM('Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión libre') NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    telefono_celular VARCHAR(20),
    telefono_local VARCHAR(20),
    etnia_aborigen VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE municipios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    id_estado INT NOT NULL,
    FOREIGN KEY (id_estado) REFERENCES estado(id)
);
CREATE TABLE parroquias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    id_municipio INT NOT NULL,
    FOREIGN KEY (id_municipio) REFERENCES municipios(id)
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
    FOREIGN KEY (id_estado) REFERENCES estados(id),
    FOREIGN KEY (id_municipio) REFERENCES municipios(id),
    FOREIGN KEY (id_parroquia) REFERENCES parroquias(id)
);

CREATE TABLE datos_socioeconomicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL, -- Relacionado con datos_personales.id
    grupo_familiar INT NOT NULL,
    personas_a_cargo INT NOT NULL,
    fuente_ingresos VARCHAR(100) NOT NULL,
    ingreso_mensual DECIMAL(10,2) NOT NULL,
    tipo_vivienda VARCHAR(50) NOT NULL,
    tenencia_vivienda VARCHAR(50) NOT NULL,
    
    FOREIGN KEY (id_persona) REFERENCES datos_personales(id)
);



DELETE FROM nombre_de_tu_tabla;
ALTER TABLE nombre_de_tu_tabla AUTO_INCREMENT = 1;







CREATE TABLE carreras (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre_carrera VARCHAR(255) NOT NULL,
    estado ENUM('activa', 'eliminada') DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- Tabla para trayectos (cada uno pertenece a una carrera)
CREATE TABLE IF NOT EXISTS trayectos (
  id_trayecto INT AUTO_INCREMENT PRIMARY KEY,
  id_carrera INT NOT NULL,
  numero_trayecto INT NOT NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera) ON DELETE CASCADE,
  UNIQUE KEY (id_carrera, numero_trayecto)
);

-- Tabla para periodos (cada uno pertenece a un trayecto)
CREATE TABLE IF NOT EXISTS periodos (
  id_periodo INT AUTO_INCREMENT PRIMARY KEY,
  id_trayecto INT NOT NULL,
  numero_periodo INT NOT NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_trayecto) REFERENCES trayectos(id_trayecto) ON DELETE CASCADE,
  UNIQUE KEY (id_trayecto, numero_periodo)
);



CREATE TABLE grupos (
  id_grupo INT AUTO_INCREMENT PRIMARY KEY,
  id_carrera INT NOT NULL,
  nombre_grupo VARCHAR(100) NOT NULL,
  id_trayecto INT NOT NULL,
  id_periodo INT NOT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado VARCHAR(20) DEFAULT 'activo',
  FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera) ON DELETE CASCADE,
  FOREIGN KEY (id_trayecto) REFERENCES trayectos(id_trayecto) ON DELETE CASCADE,
  FOREIGN KEY (id_periodo) REFERENCES periodos(id_periodo) ON DELETE CASCADE
);

CREATE TABLE grupo_estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_estudiante INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_estudiante) REFERENCES datos_personales(id) ON DELETE CASCADE,
    UNIQUE (id_grupo, id_estudiante) -- Evita que un estudiante se repita en el mismo grupo
);






CREATE TABLE IF NOT EXISTS materias_carrera (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    id_carrera INT NOT NULL,
    id_trayecto INT NOT NULL,
    id_periodo INT NOT NULL,
    nombre_materia VARCHAR(255) NOT NULL,
    codigo_materia VARCHAR(50),
    creditos INT DEFAULT 0,
    horas_semanales INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera) ON DELETE CASCADE,
    FOREIGN KEY (id_trayecto) REFERENCES trayectos(id_trayecto) ON DELETE CASCADE,
    FOREIGN KEY (id_periodo) REFERENCES periodos(id_periodo) ON DELETE CASCADE
);





CREATE TABLE IF NOT EXISTS historial_grupo_materia (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    
    id_grupo INT NOT NULL,            -- referencia a grupos.id_grupo
    id_estudiante INT NOT NULL,       -- referencia a datos_personales.id
    id_materia INT NOT NULL,          -- referencia a materias_carrera.id_materia
    id_carrera INT NOT NULL,          -- referencia a carreras.id_carrera
    id_trayecto INT NOT NULL,         -- referencia a trayectos.id_trayecto
    id_periodo INT NOT NULL,          -- referencia a periodos.id_periodo
    profesor VARCHAR(255) DEFAULT NULL, -- profesor asignado (texto por ahora)

    nota DECIMAL(5,2) DEFAULT NULL,
    estado ENUM('pendiente','aprobada','reprobada','congelada') DEFAULT 'pendiente',

    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Claves foráneas
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_estudiante) REFERENCES datos_personales(id) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materias_carrera(id_materia) ON DELETE CASCADE,
    FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera) ON DELETE CASCADE,
    FOREIGN KEY (id_trayecto) REFERENCES trayectos(id_trayecto) ON DELETE CASCADE,
    FOREIGN KEY (id_periodo) REFERENCES periodos(id_periodo) ON DELETE CASCADE,

    UNIQUE KEY unica_materia_grupo_estudiante (id_grupo, id_estudiante, id_materia)
);



ALTER TABLE datos_personales
ADD COLUMN estado ENUM('activo', 'retirado', 'congelado', 'egresado') DEFAULT 'activo' AFTER fecha_registro;

ALTER TABLE grupos ADD COLUMN id_vocero INT DEFAULT NULL;



CREATE TABLE trayecto_inicial (
    id_trayecto_inicial INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo_inicial VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE trayecto_inicial_estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_trayecto_inicial INT NOT NULL,
    id_estudiante INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_trayecto_inicial) REFERENCES trayecto_inicial(id_trayecto_inicial) ON DELETE CASCADE,
    FOREIGN KEY (id_estudiante) REFERENCES datos_personales(id) ON DELETE CASCADE,
    UNIQUE (id_trayecto_inicial, id_estudiante)
);


CREATE TABLE IF NOT EXISTS materias_pensum_inicial (
    id_materia_inicial INT AUTO_INCREMENT PRIMARY KEY,
    nombre_materia VARCHAR(255) NOT NULL,
    codigo_materia VARCHAR(50),
    creditos INT DEFAULT 0,
    horas_semanales INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS grupo_inicial_materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_trayecto_inicial INT NOT NULL,
    id_materia_inicial INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_trayecto_inicial) REFERENCES trayecto_inicial(id_trayecto_inicial) ON DELETE CASCADE,
    FOREIGN KEY (id_materia_inicial) REFERENCES materias_pensum_inicial(id_materia_inicial) ON DELETE CASCADE,
    UNIQUE KEY unica_materia_grupo (id_trayecto_inicial, id_materia_inicial)
);


CREATE TABLE IF NOT EXISTS historial_academico_inicial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_trayecto_inicial INT NOT NULL,
    id_grupo_estudiante INT NULL, 
    id_estudiante INT NOT NULL,    
    id_materia_inicial INT NOT NULL,
    nota DECIMAL(5,2) DEFAULT NULL,
    estado ENUM('pendiente','aprobada','reprobada','congelada') DEFAULT 'pendiente',
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_trayecto_inicial) REFERENCES trayecto_inicial(id_trayecto_inicial) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo_estudiante) REFERENCES trayecto_inicial_estudiantes(id) ON DELETE SET NULL,
    FOREIGN KEY (id_estudiante) REFERENCES datos_personales(id) ON DELETE CASCADE,
    FOREIGN KEY (id_materia_inicial) REFERENCES materias_pensum_inicial(id_materia_inicial) ON DELETE CASCADE,
    UNIQUE KEY unica_materia_estudiante (id_estudiante, id_materia_inicial)
);



ALTER TABLE datos_personales
MODIFY COLUMN sexo ENUM('M','F','O') NOT NULL;


ALTER TABLE datos_personales 
ADD COLUMN correo_electronico VARCHAR(150) 
AFTER telefono_local;



ALTER TABLE periodos
ADD COLUMN fecha_inicio DATE DEFAULT NULL,
ADD COLUMN fecha_fin DATE DEFAULT NULL;




ALTER TABLE historial_academico_inicial
MODIFY COLUMN nota INT DEFAULT NULL;

ALTER TABLE historial_academico_inicial
MODIFY COLUMN estado ENUM('Pendiente','Aprobada','Reprobada','Congelada') DEFAULT 'Pendiente';


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
DELIMITER ;


DELIMITER //
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
DELIMITER ;


UPDATE historial_academico_inicial
SET estado = CASE 
    WHEN nota IS NULL THEN 'Pendiente'
    WHEN nota < 12 THEN 'Reprobada'
    ELSE 'Aprobada'
END;



ALTER TABLE historial_grupo_materia
MODIFY COLUMN nota INT DEFAULT NULL;


ALTER TABLE historial_grupo_materia
MODIFY COLUMN estado ENUM('Pendiente','Aprobada','Reprobada','Congelada') DEFAULT 'Pendiente';


DELIMITER //

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

DELIMITER ;


DELIMITER //

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


UPDATE historial_grupo_materia
SET estado = CASE 
    WHEN nota IS NULL THEN 'Pendiente'
    WHEN nota < 12 THEN 'Reprobada'
    ELSE 'Aprobada'
END;

ALTER TABLE carreras MODIFY COLUMN estado ENUM('activa', 'inhabilitada') DEFAULT 'activa';




ALTER TABLE historial_grupo_materia 
ADD COLUMN consolidado TINYINT(1) DEFAULT 0,
ADD COLUMN nombre_materia_snapshot VARCHAR(255) DEFAULT NULL,
ADD COLUMN codigo_materia_snapshot VARCHAR(50) DEFAULT NULL;


ALTER TABLE historial_grupo_materia 
ADD COLUMN cedula_profesor_snapshot VARCHAR(20) DEFAULT NULL;





ALTER TABLE materias_carrera ADD COLUMN estado ENUM('activa', 'eliminada') DEFAULT 'activa';


ALTER TABLE periodos
DROP COLUMN fecha_inicio,
DROP COLUMN fecha_fin;


CREATE TABLE IF NOT EXISTS grupo_periodo_fechas (
  id_grupo INT NOT NULL,
  id_periodo INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  PRIMARY KEY (id_grupo, id_periodo),
  FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
  FOREIGN KEY (id_periodo) REFERENCES periodos(id_periodo) ON DELETE CASCADE
);


ALTER TABLE materias_pensum_inicial 
ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1;


ALTER TABLE carreras MODIFY COLUMN estado ENUM('activa', 'inhabilitada', 'eliminada') DEFAULT 'activa';


ALTER TABLE trayecto_inicial ADD COLUMN estado ENUM('activa', 'inhabilitada', 'eliminada') DEFAULT 'activa';