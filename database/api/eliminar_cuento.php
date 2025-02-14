<?php
include('connect_db.php');

$id_cuento = $_POST['id_cuento'];

$sql = "DELETE FROM Cuento WHERE id_cuento = '$id_cuento';";

if ($conn->query($sql) === TRUE) {
    echo "Cuento eliminado correctamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();


?>
