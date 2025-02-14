<?php
include('connect_db.php');

$id_alumno = $_POST['id_alumno'];

$sql = "CALL EliminarAlumno('$id_alumno')";

if ($conn->query($sql) === TRUE) {
    echo "Tu cuenta ha sido eliminada correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>

