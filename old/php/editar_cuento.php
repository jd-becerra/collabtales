<?php
include('connect_db.php');

$id_cuento = $_POST['id_cuento'];
$nombre_cuento = $_POST['nombre_cuento'];
$descripcion_cuento = $_POST['descripcion_cuento'];

$sql = "UPDATE Cuento SET nombre = '$nombre_cuento', descripcion = '$descripcion_cuento' WHERE id_cuento = '$id_cuento';";

if ($conn->query($sql) === TRUE) {
    echo "Los datos de tu cuento se han actualizado correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
