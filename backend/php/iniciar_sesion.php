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

$sql = "SELECT id_alumno, contrasena FROM Alumno WHERE nombre = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($contrasena, $row['contrasena'])) {
        unset($row['contrasena']); // Remove password hash before returning
        echo json_encode([$row]);
    } else {
        echo json_encode([]); // Return an empty array if password is incorrect
    }
} else {
    echo json_encode([]); // Return an empty array if no data is found
}

$stmt->close();
$conn->close();
?>
