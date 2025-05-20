<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];
$id_alumno = $data['id_alumno'];

if (empty($id_cuento) || empty($id_alumno)) {
    echo "Error: id_cuento and id_alumno must be provided";
    exit();
}

$sql = "CALL AbandonarCuento('$id_cuento','$id_alumno');";

if ($conn->query($sql) === TRUE) {
    echo "Has abandonado el cuento correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
