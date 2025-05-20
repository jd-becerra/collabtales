<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Obtener todos los cuentos globales
$sql = "SELECT 
        c.id_cuento, 
        c.nombre, 
        c.descripcion,
        GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
    FROM Cuento c
    JOIN Relacion_Alumno_Cuento rac ON c.id_cuento = rac.fk_cuento
    JOIN Alumno a ON rac.fk_alumno = a.id_alumno
    WHERE c.publicado = 1
    GROUP BY c.id_cuento, c.nombre, c.descripcion
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);
} else {
    echo json_encode([]);
}

$conn->close();
?>
