-- Active: 1714144756101@@127.0.0.1@3306@cuentosbd

-- Insertar Alumnos
INSERT INTO Alumno (nombre, contrasena) VALUES 
('Juan Perez', 'password123'),
('Maria Lopez', 'securepass'),
('Carlos Gomez', 'mypassword'),
('Ana Martinez', 'anapass'),
('Luis Rodriguez', 'luis123');
('dev, dev')

-- Insertar Cuentos
INSERT INTO Cuento (nombre, descripcion, fk_owner) VALUES 
('El Bosque Encantado', 'Un cuento sobre un bosque mágico lleno de criaturas fantásticas.', 1),
('La Ciudad Perdida', 'Relata la aventura de un explorador en busca de una ciudad olvidada.', 2),
('El Tesoro Escondido', 'Historia de piratas en busca de un tesoro legendario.', 3),
('Viaje a las Estrellas', 'Ciencia ficción sobre un astronauta explorando nuevos mundos.', 4),
('El Reino de los Sueños', 'Un cuento donde los sueños cobran vida.', 5);

-- Relacionar alumnos con cuentos
INSERT INTO Relacion_Alumno_Cuento (fk_alumno, fk_cuento) VALUES 
(1, 1),
(2, 1),
(3, 2),
(4, 2),
(5, 3),
(1, 3),
(2, 4),
(3, 4),
(4, 5),
(5, 5);

-- Insertar Aportaciones
INSERT INTO Aportacion (contenido, fk_cuento, fk_alumno) VALUES 
('Había una vez un bosque encantado...', 1, 1),
('Un explorador valiente descubrió una pista en la selva...', 2, 3),
('Los piratas zarparon con un mapa en busca del tesoro...', 3, 5),
('El astronauta encontró un planeta con formas de vida desconocidas...', 4, 2),
('En el Reino de los Sueños, todo era posible...', 5, 4),
('El hada del bosque tenía un secreto que solo algunos conocían...', 1, 2),
('La ciudad perdida estaba protegida por un enigma milenario...', 2, 4);

-- Insertar registros en Historial
INSERT INTO Historial (fk_alumno, fk_cuento, accion) VALUES 
(1, 1, 'Creó el cuento'),
(2, 1, 'Se unió al cuento'),
(3, 2, 'Creó el cuento'),
(4, 2, 'Se unió al cuento'),
(5, 3, 'Creó el cuento'),
(1, 3, 'Se unió al cuento'),
(2, 4, 'Se unió al cuento'),
(3, 4, 'Se unió al cuento'),
(4, 5, 'Creó el cuento'),
(5, 5, 'Se unió al cuento'),
(1, 1, 'Realizó una aportación'),
(2, 1, 'Realizó una aportación'),
(3, 2, 'Realizó una aportación'),
(4, 2, 'Realizó una aportación'),
(5, 3, 'Realizó una aportación'),
(2, 4, 'Realizó una aportación'),
(4, 5, 'Realizó una aportación');

