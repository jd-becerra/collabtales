<?php
include('cors_headers.php');
include('config.php');

// Assuming you're using the 'nombre' parameter in your query
$data = json_decode(file_get_contents("php://input"), true);
$nombre = $data['nombre'];
$contrasena = $data['contrasena'];

$sql = "CALL AñadirAlumno('$username', '$password')";

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $row = $result->fetch_assoc();
            echo json_encode(["result" => $row['result']]);
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo json_encode(["error" => "Database error: " . $conn->error]);
}

$conn->close();
?>
