<?php
include('cors_headers.php');
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_alumno = $data['id_alumno'];

if (empty($id_alumno)) {
    echo "Error: id_alumno must be provided";
    exit();
}

$sql = "CALL EliminarAlumno('$id_alumno')";

if ($conn->query($sql) === TRUE) {
    echo "Tu cuenta ha sido eliminada correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>

