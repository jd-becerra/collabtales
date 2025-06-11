DROP DATABASE IF EXISTS `cuentosBD`; 
CREATE DATABASE `cuentosBD` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cuentosBD`;

-- Drop tables if they exist (in reverse dependency order)
DROP TABLE IF EXISTS `ListaNegra`;
DROP TABLE IF EXISTS `Historial`;
DROP TABLE IF EXISTS `Aportacion`;
DROP TABLE IF EXISTS `Relacion_Alumno_Cuento`;
DROP TABLE IF EXISTS `TokenRestauracion`;
DROP TABLE IF EXISTS `Cuento`;
DROP TABLE IF EXISTS `Alumno`;
DROP TABLE IF EXISTS `API_RATE_LIMIT`;

-- Drop procedures if they exist
DROP PROCEDURE IF EXISTS `AñadirAlumno`;
DROP PROCEDURE IF EXISTS `EliminarAlumno`;
DROP PROCEDURE IF EXISTS `ListarCuentosAlumno`;
DROP PROCEDURE IF EXISTS `UnirseCuento`;
DROP PROCEDURE IF EXISTS `EditarAlumno`;
DROP PROCEDURE IF EXISTS `CrearCuento`;
DROP PROCEDURE IF EXISTS `ListarCuentoAportacionesConAlumnos`;
DROP PROCEDURE IF EXISTS `EditarAportacion`;
DROP PROCEDURE IF EXISTS `EliminarAlumnoCuento`;
DROP PROCEDURE IF EXISTS `BloquearAlumno`;
DROP PROCEDURE IF EXISTS `ObtenerRateLimitRecientes`;
DROP PROCEDURE IF EXISTS `InsertarRateLimit`;
DROP PROCEDURE IF EXISTS `ResetearRateLimit`;

-- Drop triggers if they exist
DROP TRIGGER IF EXISTS `After_UnirseCuento`;
DROP TRIGGER IF EXISTS `After_Aportacion`;

-- Drop event if it exists
DROP EVENT IF EXISTS `LimpiezaRateLimit`;

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
        `codigo_compartir` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `publicado` TINYINT(1) DEFAULT 0,
        `admite_colaboradores` TINYINT(1) DEFAULT 1,
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
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`fk_alumno`, `fk_cuento`),
                FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno`(`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento`(`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Aportacion
CREATE TABLE `Aportacion` (
        `id_aportacion` INT(11) NOT NULL AUTO_INCREMENT,
        `contenido` VARCHAR(8000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
        `fk_cuento` INT(11) NOT NULL,
        `fk_alumno` INT(11) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
                PRIMARY KEY (`id_historial`),
                FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno`(`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento`(`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla que impide a algunos usuarios acceder a cuentos donde el creador los ha bloqueado
CREATE TABLE `ListaNegra` (
        `id_listanegra` INT(11) NOT NULL AUTO_INCREMENT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `fk_alumno` INT(11) NOT NULL,
        `fk_cuento` INT(11) NOT NULL,
        PRIMARY KEY (`id_listanegra`),
        FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno`(`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento`(`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE	
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE API_RATE_LIMIT (
    id_api_rate_limit INT AUTO_INCREMENT PRIMARY KEY,
    endpoint_name VARCHAR(50),
    ip_address VARCHAR(45),
    request_time INT
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
        SELECT 
            cuento.id_cuento, 
            cuento.nombre, 
            cuento.descripcion,
            (CASE WHEN id_alumno = cuento.fk_owner THEN 1 ELSE 0 END) AS es_dueño,
            GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
        FROM `Cuento` cuento
        JOIN `Relacion_Alumno_Cuento` relacion ON cuento.id_cuento = relacion.fk_cuento
        JOIN `Alumno` a ON relacion.fk_alumno = a.id_alumno
        WHERE relacion.fk_alumno = id_alumno
        GROUP BY cuento.id_cuento, cuento.nombre, cuento.descripcion
        ORDER BY cuento.nombre ASC;
    END$$
    DELIMITER ;

-- Procedure: UnirseCuento
DELIMITER $$
CREATE PROCEDURE `UnirseCuento`(IN cuento_id INT, IN alumno_id INT)
BEGIN
    DECLARE relacion_count INT;
    DECLARE admite_colaboradores_val TINYINT;

    -- Verificar si el cuento admite colaboradores
    SELECT admite_colaboradores INTO admite_colaboradores_val FROM Cuento WHERE id_cuento = cuento_id;
    IF admite_colaboradores_val IS NULL THEN
        SELECT 'El cuento no existe.' AS result;
    ELSEIF admite_colaboradores_val = 0 THEN
        SELECT 'Este cuento no admite colaboradores.' AS result;
    ELSE
        -- Verificar si ya existe la relación
        SELECT COUNT(*) INTO relacion_count FROM Relacion_Alumno_Cuento WHERE fk_cuento = cuento_id AND fk_alumno = alumno_id;
        IF relacion_count = 0 THEN
            INSERT INTO `Relacion_Alumno_Cuento` (fk_alumno, fk_cuento) VALUES (alumno_id, cuento_id);
            INSERT INTO `Aportacion` (contenido, fk_cuento, fk_alumno) VALUES ('[]', cuento_id, alumno_id);
            SELECT 'El alumno se ha unido al cuento correctamente' AS result;
        ELSE
            SELECT 'Error al unirse al cuento.' AS result;
        END IF;
    END IF;
END$$
DELIMITER ;

-- Procedure: EditarAlumno
DELIMITER $$

CREATE PROCEDURE `EditarAlumno`(
    IN id_alumno_param INT,
    IN nuevo_nombre VARCHAR(255),
    IN nuevo_correo VARCHAR(255)
)
BEGIN
    DECLARE nombre_existente INT DEFAULT 0;
    DECLARE correo_existente INT DEFAULT 0;

    -- Obtener valores actuales si no se enviaron
    SELECT nombre, correo INTO @actual_nombre, @actual_correo
    FROM Alumno WHERE id_alumno = id_alumno_param;

    SET @nombre_final = IF(TRIM(nuevo_nombre) <> '', nuevo_nombre, @actual_nombre);
    SET @correo_final = IF(TRIM(nuevo_correo) <> '', nuevo_correo, @actual_correo);

    -- Validar que al menos uno cambió
    IF @nombre_final = @actual_nombre AND @correo_final = @actual_correo THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se detectaron cambios.';
    END IF;

    -- Verificar unicidad del nombre
    SELECT COUNT(*) INTO nombre_existente
    FROM Alumno WHERE nombre = @nombre_final AND id_alumno <> id_alumno_param;

    -- Verificar unicidad del correo
    SELECT COUNT(*) INTO correo_existente
    FROM Alumno WHERE correo = @correo_final AND id_alumno <> id_alumno_param;

    -- Validar conflictos
    IF nombre_existente > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El nombre de usuario ya está en uso.';
    ELSEIF correo_existente > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El correo electrónico ya está en uso.';
    END IF;

    -- Actualizar
    UPDATE Alumno SET
        nombre = @nombre_final,
        correo = @correo_final
    WHERE id_alumno = id_alumno_param;

    SELECT 'Alumno actualizado correctamente' AS result;
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

                -- Crear un código único para compartir el cuento
                DECLARE codigo_compartir_nuevo VARCHAR(8);
                SET codigo_compartir_nuevo = CONCAT(SUBSTRING(MD5(RAND()), 1, 8));
                WHILE EXISTS (SELECT 1 FROM Cuento WHERE codigo_compartir = codigo_compartir_nuevo) DO
                SET codigo_compartir_nuevo = CONCAT(SUBSTRING(MD5(RAND()), 1, 8));
                END WHILE;

                -- Insertar el cuento en la tabla Cuento
                INSERT INTO Cuento (nombre, descripcion, codigo_compartir, fk_owner)
                VALUES (nombre_cuento, descripcion_cuento, codigo_compartir_nuevo, id_owner);
                
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

-- Procedure: EliminarAlumnoCuento (elimina a un alumno de un cuento, además de su aportación, pero NO lo bloquea)
DELIMITER $$
CREATE PROCEDURE `EliminarAlumnoCuento`(
                IN id_alumno_param INT,
                IN id_cuento_param INT
)
BEGIN
                DELETE FROM Relacion_Alumno_Cuento
                WHERE fk_alumno = id_alumno_param AND fk_cuento = id_cuento_param;
                DELETE FROM Aportacion
                WHERE fk_alumno = id_alumno_param AND fk_cuento = id_cuento_param;
END$$
DELIMITER ;

-- Procedure: BloquearAlumno (elimina a un alumno de un cuento y lo añade a la lista negra)
DELIMITER $$
CREATE PROCEDURE `BloquearAlumno`(
                IN id_alumno_param INT,
                IN id_cuento_param INT
)
BEGIN
                DELETE FROM Relacion_Alumno_Cuento
                WHERE fk_alumno = id_alumno_param AND fk_cuento = id_cuento_param;
                
                INSERT INTO ListaNegra (fk_alumno, fk_cuento)
                VALUES (id_alumno_param, id_cuento_param);
END$$
DELIMITER ;

-- Implementacion de rate limiting para evitar ataques de fuerza bruta
DELIMITER $$
CREATE PROCEDURE `ObtenerRateLimitRecientes` (
    IN p_endpoint VARCHAR(50),
    IN p_ip VARCHAR(45),
    IN p_time_limit INT
)
BEGIN
    SELECT COUNT(*) AS request_count
    FROM API_RATE_LIMIT
    WHERE endpoint_name = p_endpoint
      AND ip_address = p_ip
      AND request_time >= UNIX_TIMESTAMP(NOW()) - p_time_limit;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE `InsertarRateLimit`(
    IN p_endpoint VARCHAR(50),
    IN p_ip VARCHAR(45),
    IN p_request_time INT
)
BEGIN
    INSERT INTO API_RATE_LIMIT (endpoint_name, ip_address, request_time)
    VALUES (p_endpoint, p_ip, p_request_time);
END$$
DELIMITER ;

-- Si el usuario logra hacer login, se eliminan las entradas de rate limit para no bloquearlo
DELIMITER $$
CREATE PROCEDURE `ResetearRateLimit`(
    IN p_endpoint VARCHAR(50),
    IN p_ip VARCHAR(45)
)
BEGIN
    DELETE FROM API_RATE_LIMIT
    WHERE endpoint_name = p_endpoint
      AND ip_address = p_ip;
END$$
DELIMITER ;

-- Eliminar entradas viejas de la tabla API_RATE_LIMIT cada hora
DELIMITER $$
CREATE EVENT IF NOT EXISTS `LimpiezaRateLimit`
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM API_RATE_LIMIT
    WHERE request_time < UNIX_TIMESTAMP(NOW()) - 3600;
END$$
DELIMITER ;

-- Table: Likes ("me gusta" que pueden dar los usuarios a los cuentos donde no han colaborado)
CREATE TABLE `Likes` (
    `id_like` INT(11) NOT NULL AUTO_INCREMENT,
    `fk_alumno` INT(11) NOT NULL,
    `fk_cuento` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_like`),
    FOREIGN KEY (`fk_alumno`) REFERENCES `Alumno`(`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`fk_cuento`) REFERENCES `Cuento`(`id_cuento`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `unique_like` (`fk_alumno`, `fk_cuento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procedure: DarLikeCuento
DELIMITER $$

CREATE PROCEDURE `DarLikeCuento`(
    IN id_alumno_param INT,
    IN id_cuento_param INT
)
BEGIN
    DECLARE existe_like INT;
    DECLARE alumno_existe INT;
    DECLARE cuento_existe INT;

    -- Verificar si el alumno existe
    SELECT COUNT(*) INTO alumno_existe
    FROM Alumno
    WHERE id_alumno = id_alumno_param;

    -- Verificar si el cuento existe
    SELECT COUNT(*) INTO cuento_existe
    FROM Cuento
    WHERE id_cuento = id_cuento_param;

    IF alumno_existe = 0 THEN
        SELECT 'El alumno no existe' AS result;
    ELSEIF cuento_existe = 0 THEN
        SELECT 'El cuento no existe' AS result;
    ELSE
        -- Verificar si ya existe el like
        SELECT COUNT(*) INTO existe_like
        FROM Likes
        WHERE fk_alumno = id_alumno_param AND fk_cuento = id_cuento_param;

        IF existe_like = 0 THEN
            INSERT INTO Likes (fk_alumno, fk_cuento)
            VALUES (id_alumno_param, id_cuento_param);
            SELECT 'Like añadido' AS result;
        ELSE
            SELECT 'El alumno ya dio like a este cuento' AS result;
        END IF;
    END IF;
END$$
DELIMITER ;
