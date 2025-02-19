<?php
include('cors_headers.php');
include('config.php');

$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$id_alumno = $_POST['id_alumno'];

//$sql = "SELECT CrearCuento(9, 'Nuevo Cuento', 'Descripción del nuevo cuento') AS nuevo_cuento_id;";

$sql = "SELECT CrearCuento($id_alumno, '$nombre', '$descripcion') AS nuevo_cuento_id;";

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

