<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];
$id_alumno = $data['id_alumno'];
$aportacion = $data['aportacion'];

if (empty($id_cuento) || empty($id_alumno) || empty($aportacion)) {
  echo "Error: id_cuento, id_alumno and aportacion must be provided";
  exit();
}

$sql = "CALL EditarAportacion($id_alumno, $id_cuento, '$aportacion')";

if ($conn->query($sql) === TRUE) {
  echo "Aportación actualizada correctamente";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
