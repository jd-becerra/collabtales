<?php
include('cors_headers.php');
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Read raw JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['id_aportacion']) || !isset($data['contenido'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$id_aportacion = $data['id_aportacion'];
$contenido = is_string($data['contenido']) ? $data['contenido'] : json_encode($data['contenido']);

// Prepare the SQL query
$sql = $conn->prepare("CALL EditarAportacion(?, ?)");
$sql->bind_param("is", $id_aportacion, $contenido);

// Execute query
if ($sql->execute()) {
    echo json_encode(["success" => true, "message" => "Aportación actualizada"]);
} else {
    echo json_encode(["error" => "Error al guardar en la BD"]);
}

// Close connection
$sql->close();
$conn->close();
?>