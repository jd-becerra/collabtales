<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$nombre = $data['nombre'];
$descripcion = $data['descripcion'];
$id_alumno = $data['id_alumno'];

if (empty($nombre) || empty($descripcion) || empty($id_alumno)) {
    echo "Error: nombre, descripcion y id_alumno son obligatorios";
    exit();
}

//$sql = "SELECT CrearCuento(9, 'Nuevo Cuento', 'Descripción del nuevo cuento') AS nuevo_cuento_id;";

$sql = "CALL CrearCuento($id_alumno, '$nombre', '$descripcion') AS nuevo_cuento_id;";

if ($conn->multi_query($sql)) {
    do {
        // Store result set
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                echo $row['result'];
            }
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} 
$conn->close();
?>

