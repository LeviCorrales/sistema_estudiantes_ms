ALTER TABLE datos_personales 
ADD COLUMN password VARCHAR(255) DEFAULT NULL AFTER correo_electronico;