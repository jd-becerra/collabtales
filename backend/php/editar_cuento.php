<?php
include('cors_headers.php');
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];
$nombre_cuento = $data['nombre_cuento'];
$descripcion_cuento = $data['descripcion_cuento'];

if (empty($id_cuento) || empty($nombre_cuento) || empty($descripcion_cuento)) {
    echo "Error: id_cuento, nombre_cuento and descripcion_cuento must be provided";
    exit();
}

$sql = "UPDATE Cuento SET nombre = '$nombre_cuento', descripcion = '$descripcion_cuento' WHERE id_cuento = '$id_cuento';";

if ($conn->query($sql) === TRUE) {
    echo "Los datos de tu cuento se han actualizado correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
