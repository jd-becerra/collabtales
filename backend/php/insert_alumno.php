<?php
include('connect_db.php');

$username = $_POST['nombre'];
$password = $_POST['contrasena'];

$sql = "CALL AñadirAlumno('$username', '$password')";

if ($conn->multi_query($sql)) {
    do {
        // Store result set
        if ($result = $conn->store_result()) {
            $row = $result->fetch_assoc();
            echo $row['result'];
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error: " . $sql . $conn->error;
}

$conn->close();


?>

