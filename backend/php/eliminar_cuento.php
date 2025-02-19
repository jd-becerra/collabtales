<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];

$sql = "DELETE FROM Cuento WHERE id_cuento = '$id_cuento';";

if ($conn->query($sql) === TRUE) {
    echo "Cuento eliminado correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
