<?php
include('connect_db.php');

$id_alumno = $_POST['id_alumno'];
$nombre = $_POST['nombre'];
$contrasena = $_POST['contrasena'];

$sql = "Select EditarAlumno('$id_alumno', '$nombre', '$contrasena') AS result;";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  echo $row['result']; // Output the result as a string
} else {
  echo 'Error: ' . $sql . '<br>' . $conn->error;
}
$conn->close();


?>
