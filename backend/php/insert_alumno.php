<?php
include('cors_headers.php');
include('config.php');

// Assuming you're using the 'nombre' parameter in your query
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['nombre']) || empty($data['contrasena'])) {
    echo json_encode(["error" => "Nombre y contraseña son obligatorios"]);
    exit; // Stop script execution if data is missing
}

$nombre = $data['nombre'];
$contrasena = $data['contrasena'];

$sql = "CALL AñadirAlumno('$nombre', '$contrasena')";

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
