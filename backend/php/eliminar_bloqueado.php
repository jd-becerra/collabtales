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

$sql = "DELETE FROM ListaNegra WHERE id_cuento = '$id_cuento' AND id_alumno = '$id_alumno';";

if ($conn->query($sql) === TRUE) {
    echo "Alumno desbloqueado correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
