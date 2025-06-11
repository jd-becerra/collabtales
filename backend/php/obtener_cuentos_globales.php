<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Obtener todos los cuentos globales de forma segura usando prepared statements
$sql = "
    SELECT 
        c.id_cuento, 
        c.nombre, 
        c.descripcion, 
        GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
        COUNT(DISTINCT like.fk_alumno) AS likes
    FROM Cuento c
    JOIN Relacion_Alumno_Cuento rac ON c.id_cuento = rac.fk_cuento
    JOIN Alumno a ON rac.fk_alumno = a.id_alumno
    LEFT JOIN Likes like ON c.id_cuento = like.fk_cuento
    WHERE c.publicado = 1
      AND NOT EXISTS (
          SELECT 1 FROM ListaNegra ln 
          WHERE ln.fk_cuento = c.id_cuento AND ln.fk_alumno = ?
      )
    GROUP BY c.id_cuento, c.nombre, c.descripcion
";

// Prepara la consulta
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id_alumno']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        // Convertir autores a array
        $row['autores'] = $row['autores'] ? explode(', ', $row['autores']) : [];
        $rows[] = $row;
    }
    echo json_encode($rows);
} else {
    echo json_encode([]);
}

$stmt->close();
$conn->close();
?>
