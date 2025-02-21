DROP SCHEMA IF EXISTS `cuentosBD`;
CREATE SCHEMA `cuentosBD` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cuentosBD`;

-- Table: Alumno
CREATE TABLE `Alumno` (
  `id_alumno` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `contrasena` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_alumno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Cuento
CREATE TABLE `Cuento` (
  `id_cuento` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descripcion` VARCHAR(511) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fk_owner` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_cuento`),
  CONSTRAINT `fk_owner` FOREIGN KEY (`fk_owner`) REFERENCES `Alumno` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Aportacion
CREATE TABLE `Aportacion` (
  `id_aportacion` INT(11) NOT NULL AUTO_INCREMENT,
  `contenido` VARCHAR(2047) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `fk_cuento` INT(11) NULL DEFAULT NULL,
  `fk_alumno` INT(11) NULL DEFAULT NULL,
  `fecha_creacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_aportacion`),
  CONSTRAINT `fk_cuento_aportacion` FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento` (`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_alumno_aportacion` FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procedure: AñadirAlumno
DELIMITER $$
CREATE PROCEDURE `AñadirAlumno`(IN username VARCHAR(255), IN pass VARCHAR(255))
BEGIN
    DECLARE username_count INT;
    SELECT COUNT(*) INTO username_count FROM Alumno WHERE nombre = username;
    IF username_count = 0 THEN
        INSERT INTO Alumno (nombre, contrasena) VALUES (username, pass);
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
    DELETE FROM Cuento WHERE fk_owner = id_alumno_param;
    DELETE FROM Alumno WHERE id_alumno = id_alumno_param;
END$$
DELIMITER ;

-- Procedure: ListarCuentosAlumno
DELIMITER $$
CREATE PROCEDURE `ListarCuentosAlumno`(IN id_alumno INT)
BEGIN
    SELECT cuento.id_cuento, cuento.nombre
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
        INSERT INTO `Aportacion` (fk_cuento, fk_alumno) VALUES (cuento_id, alumno_id);
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
    IN nueva_contrasena VARCHAR(255)
)
BEGIN
    DECLARE nombre_existente INT;
    
    -- Verificar si el nuevo nombre ya existe en otro usuario
    SELECT COUNT(*) INTO nombre_existente FROM Alumno 
    WHERE nombre = nuevo_nombre AND id_alumno <> id_alumno_param;
    
    IF nombre_existente = 0 THEN
        UPDATE Alumno 
        SET nombre = nuevo_nombre, contrasena = nueva_contrasena
        WHERE id_alumno = id_alumno_param;
        
        SELECT 'Alumno actualizado correctamente' AS result;
    ELSE
        SELECT 'El nombre de usuario ya existe' AS result;
    END IF;
END$$
DELIMITER ;