<?php
include('connect_db.php');

$id_cuento = $_POST['id_cuento'];
$id_alumno = $_POST['id_alumno'];

$sql = "CALL AbandonarCuento('$id_cuento','$id_alumno');";

if ($conn->query($sql) === TRUE) {
    echo "Has abandonado el cuento correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
