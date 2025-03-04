<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];
$id_alumno = $data['id_alumno'];

if (empty($id_cuento) || empty($id_alumno)) {
    echo "Error: id_cuento and id_alumno must be provided";
    exit();
}

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

