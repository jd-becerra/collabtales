<?php
include('connect_db.php');

$id_cuento = $_POST['id_cuento'];
$id_alumno = $_POST['id_alumno'];
$aportacion = $_POST['aportacion'];

$sql = "CALL UpdateContenido($id_alumno, $id_cuento, '$aportacion')";

if ($conn->query($sql) === TRUE) {
  echo "Aportación actualizada correctamente";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
