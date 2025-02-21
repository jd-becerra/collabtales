<?php
include('cors_headers.php');
include('config.php');

// Assuming you're using the 'nombre' parameter in your query
$data = json_decode(file_get_contents("php://input"), true);
$nombre = $data['nombre'];
$contrasena = $data['contrasena'];

if (empty($nombre) || empty($contrasena)) {
    echo json_encode(["error" => "Nombre y contraseña son obligatorios"]);
    exit; // Stop script execution if data is missing
}

$sql = "SELECT id_alumno FROM Alumno WHERE nombre = '$nombre' AND contrasena = '$contrasena'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch data and return as JSON
    $row = $result->fetch_assoc();
    echo json_encode([$row]);
} else {
    echo json_encode([]); // Return an empty array if no data is found
}

$conn->close();
?>
