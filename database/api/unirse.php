<?php
include('connect_db.php');


$id_cuento = $_POST['id_cuento'];
$id_alumno = $_POST['id_alumno'];

$sql = "CALL UnirseCuento($id_cuento, $id_alumno);";

if ($conn->multi_query($sql)) {
    do {
        // Store result set
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                echo $row['result'];
            }
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} 
$conn->close();

?>

