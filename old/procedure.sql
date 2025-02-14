DELIMITER //

CREATE FUNCTION CrearCuento(IN alumno_id INT, IN nombre_nuevo VARCHAR(255), descripcion_nuevo VARCHAR(511))
RETURNS INT
BEGIN
  DECLARE nuevo_cuento_id INT;

  INSERT INTO Cuento (nombre, descripcion) VALUES (nombre_nuevo, descripcion_nuevo);
  SET nuevo_cuento_id = LAST_INSERT_ID();
  INSERT INTO Relacion_Alumno_Cuento (fk_alumno, fk_cuento) VALUES (alumno_id, nuevo_cuento_id);

  RETURN nuevo_cuento_id;
END //

DELIMITER ;
