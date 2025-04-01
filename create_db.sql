DROP DATABASE IF EXISTS `cuentosBD`; 
CREATE DATABASE `cuentosBD` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cuentosBD`;

-- Table: Alumno
CREATE TABLE `Alumno` (
        `id_alumno` INT(11) NOT NULL AUTO_INCREMENT,
        `nombre` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL UNIQUE,
        `contrasena` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `correo` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL UNIQUE,
        PRIMARY KEY (`id_alumno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Cuento
CREATE TABLE `Cuento` (
        `id_cuento` INT(11) NOT NULL AUTO_INCREMENT,
        `nombre` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `descripcion` VARCHAR(511) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `publicado` TINYINT(1) DEFAULT 0,
        `fk_owner` INT(11) NULL DEFAULT NULL,
        PRIMARY KEY (`id_cuento`),
        CONSTRAINT `fk_cuento_owner` FOREIGN KEY (`fk_owner`) REFERENCES `Alumno` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Restauracion, para guardar tokens de restauración de contraseña
CREATE TABLE `TokenRestauracion` (
        `id_restauracion` INT(11) NOT NULL AUTO_INCREMENT,
        `token` VARCHAR(64) NOT NULL UNIQUE,
        `expiracion` INT NOT NULL,
        `fk_alumno` INT(11) NOT NULL,
        `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_restauracion`),
        FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno`(`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Relacion_Alumno_Cuento
CREATE TABLE `Relacion_Alumno_Cuento` (
                `fk_alumno` INT(11) NOT NULL,
                `fk_cuento` INT(11) NOT NULL,
                PRIMARY KEY (`fk_alumno`, `fk_cuento`),
                FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno`(`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento`(`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Aportacion
CREATE TABLE `Aportacion` (
        `id_aportacion` INT(11) NOT NULL AUTO_INCREMENT,
        `contenido` VARCHAR(2047) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
        `fk_cuento` INT(11) NULL DEFAULT NULL,
        `fk_alumno` INT(11) NULL DEFAULT NULL,
        `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_aportacion`),
        CONSTRAINT `fk_cuento_aportacion` FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento` (`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_alumno_aportacion` FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Historial
CREATE TABLE `Historial` (
                `id_historial` INT(11) NOT NULL AUTO_INCREMENT,
                `fk_alumno` INT(11) NOT NULL,
                `fk_cuento` INT(11) NOT NULL,
                `accion` VARCHAR(255) NOT NULL,
                `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
                PRIMARY KEY (`id_historial`),
                FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno`(`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento`(`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procedure: AñadirAlumno
DELIMITER $$
CREATE PROCEDURE `AñadirAlumno`(IN username VARCHAR(255), IN pass VARCHAR(255), IN email VARCHAR(255))
BEGIN
                DECLARE username_count INT;
                DECLARE email_count INT;
                SELECT COUNT(*) INTO username_count FROM Alumno WHERE nombre = username;
                SELECT COUNT(*) INTO email_count FROM Alumno WHERE correo = email;
                IF username_count = 0 AND email_count = 0 THEN
                                INSERT INTO Alumno (nombre, contrasena, correo) VALUES (username, pass, email);
                                SELECT id_alumno FROM `Alumno` WHERE nombre = username;
                ELSE
                                SELECT 'El usuario ya existe' AS result;
                END IF;
END$$
DELIMITER ;

-- Procedure: EliminarAlumno
DELIMITER $$
CREATE PROCEDURE `EliminarAlumno`(IN id_alumno_param INT)
BEGIN
                DELETE FROM Alumno WHERE id_alumno = id_alumno_param;
                SELECT 'Alumno eliminado correctamente' AS result;
END$$
DELIMITER ;

-- Procedure: ListarCuentosAlumno
DELIMITER $$
CREATE PROCEDURE `ListarCuentosAlumno`(IN id_alumno INT)
BEGIN
                SELECT cuento.id_cuento, cuento.nombre, cuento.descripcion
                FROM `Cuento` cuento
                JOIN `Relacion_Alumno_Cuento` relacion ON cuento.id_cuento = relacion.fk_cuento
                WHERE relacion.fk_alumno = id_alumno
                ORDER BY cuento.nombre ASC;
END$$
DELIMITER ;

-- Procedure: UnirseCuento
DELIMITER $$
CREATE PROCEDURE `UnirseCuento`(IN cuento_id INT, IN alumno_id INT)
BEGIN
                DECLARE relacion_count INT;
                SELECT COUNT(*) INTO relacion_count FROM Relacion_Alumno_Cuento WHERE fk_cuento = cuento_id AND fk_alumno = alumno_id;
                IF relacion_count = 0 THEN
                                INSERT INTO `Relacion_Alumno_Cuento` (fk_cuento, fk_alumno) VALUES (cuento_id, alumno_id);
                                INSERT INTO `Aportacion` (contenido, fk_cuento, fk_alumno) VALUES ('[]', cuento_id, alumno_id);
                                SELECT 'El alumno se ha unido al cuento correctamente' AS result;
                ELSE
                                SELECT 'Error al unirse al cuento.' AS result;
                END IF;
END$$
DELIMITER ;

-- Procedure: EditarAlumno
DELIMITER $$
CREATE PROCEDURE `EditarAlumno`(
                IN id_alumno_param INT,
                IN nuevo_nombre VARCHAR(255),
                IN nueva_contrasena VARCHAR(255),
                IN nuevo_correo VARCHAR(255)
)
BEGIN
                DECLARE nombre_existente INT;
                DECLARE correo_existente INT;
                
                -- Verificar si el nuevo nombre ya existe en otro usuario
                SELECT COUNT(*) INTO nombre_existente FROM Alumno 
                WHERE nombre = nuevo_nombre AND id_alumno <> id_alumno_param;
                
                -- Verificar si el nuevo correo ya existe en otro usuario
                SELECT COUNT(*) INTO correo_existente FROM Alumno 
                WHERE correo = nuevo_correo AND id_alumno <> id_alumno_param;
                
                IF nombre_existente = 0 AND correo_existente = 0 THEN
                                UPDATE Alumno 
                                SET nombre = nuevo_nombre, contrasena = nueva_contrasena, correo = nuevo_correo
                                WHERE id_alumno = id_alumno_param;
                                
                                SELECT 'Alumno actualizado correctamente' AS result;
                ELSE
                                SELECT 'El nombre de usuario o correo ya existe' AS result;
                END IF;
END$$
DELIMITER ;

-- Procedure: CrearCuento
DELIMITER $$
CREATE PROCEDURE `CrearCuento`(
                IN nombre_cuento VARCHAR(255),
                IN descripcion_cuento VARCHAR(511),
                IN id_owner INT
)
BEGIN
                DECLARE cuento_id INT;

                -- Insertar el cuento en la tabla Cuento
                INSERT INTO Cuento (nombre, descripcion, fk_owner) 
                VALUES (nombre_cuento, descripcion_cuento, id_owner);
                
                -- Obtener el ID del cuento recién creado
                SET cuento_id = LAST_INSERT_ID();

                -- Insertar al dueño en la relación de alumnos y cuentos
                INSERT INTO Relacion_Alumno_Cuento (fk_alumno, fk_cuento) 
                VALUES (id_owner, cuento_id);

                -- Registrar la acción en el historial
                INSERT INTO Historial (fk_alumno, fk_cuento, accion) 
                VALUES (id_owner, cuento_id, 'Creó el cuento');

                -- Crear una aportación inicial vacía para el cuento y el alumno
                INSERT INTO Aportacion (contenido, fk_cuento, fk_alumno) 
                VALUES ('[]', cuento_id, id_owner);

                -- Devolver el ID del cuento creado
                SELECT cuento_id AS id_cuento_creado;
END$$
DELIMITER ;

-- Procedure: ListarCuentoAportacionesConAlumnos
DELIMITER $$
CREATE PROCEDURE `ListarCuentoAportacionesConAlumnos`(
                IN id_cuento_param INT
)
BEGIN
                SELECT Aportacion.id_aportacion, Aportacion.contenido, Aportacion.fecha_creacion, 
                                         Alumno.id_alumno, Alumno.nombre AS nombre_alumno
                FROM Aportacion
                JOIN Alumno ON Aportacion.fk_alumno = Alumno.id_alumno
                WHERE Aportacion.fk_cuento = id_cuento_param
                ORDER BY Aportacion.fecha_creacion ASC;
END$$
DELIMITER ;

-- Trigger: After_UnirseCuento
DELIMITER $$
CREATE TRIGGER `After_UnirseCuento`
AFTER INSERT ON `Relacion_Alumno_Cuento`
FOR EACH ROW
BEGIN
                INSERT INTO Historial (fk_alumno, fk_cuento, accion)
                VALUES (NEW.fk_alumno, NEW.fk_cuento, 'Se unió al cuento');
END$$
DELIMITER ;

-- Trigger: After_Aportacion
DELIMITER $$
CREATE TRIGGER `After_Aportacion`
AFTER INSERT ON `Aportacion`
FOR EACH ROW
BEGIN
                INSERT INTO Historial (fk_alumno, fk_cuento, accion)
                VALUES (NEW.fk_alumno, NEW.fk_cuento, 'Realizó una aportación');
END$$
DELIMITER ;

-- Procedure: EditarAportacion
DELIMITER $$
CREATE PROCEDURE `EditarAportacion`(
                IN id_aportacion_param INT,
                IN nuevo_contenido VARCHAR(2047)
)
BEGIN
                DECLARE aportacion_existente INT;

                -- Verificar si la aportación existe
                SELECT COUNT(*) INTO aportacion_existente 
                FROM Aportacion 
                WHERE id_aportacion = id_aportacion_param;

                IF aportacion_existente > 0 THEN
                                -- Si existe, actualizar la aportación
                                UPDATE Aportacion
                                SET contenido = nuevo_contenido
                                WHERE id_aportacion = id_aportacion_param;
                                
                                -- Registrar la acción en el historial
                                INSERT INTO Historial (fk_alumno, fk_cuento, accion) 
                                SELECT fk_alumno, fk_cuento, 'Actualizó una aportación'
                                FROM Aportacion
                                WHERE id_aportacion = id_aportacion_param;
                                
                                SELECT 'Aportación actualizada correctamente' AS result;
                ELSE
                                -- Si no existe la aportación, mostrar error
                                SELECT 'La aportación no existe para el ID especificado' AS result;
                END IF;
END$$
DELIMITER ;