<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();
include('config.php');

// Read raw JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Si hay más de 2 parámetros
if (!is_array($data) || count($data) !== 2) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit();
}

// Validate input
if (!isset($data['id_aportacion']) || !isset($data['contenido'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$id_aportacion = $data['id_aportacion'];
$contenido = is_string($data['contenido']) ? $data['contenido'] : json_encode($data['contenido']);

// Validar que el usuario autenticado sea el dueño de la aportación
$id_alumno = $user['id_alumno'] ?? null;
$stmt = $conn->prepare("SELECT fk_alumno FROM Aportacion WHERE id_aportacion = ?");
$stmt->bind_param("i", $id_aportacion);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado"]);
    exit();
}
$row = $result->fetch_assoc();
if ($row['fk_alumno'] !== $id_alumno) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para esta acción"]);
    exit();
}
$stmt->close();

// Prepare the SQL query
$sql = $conn->prepare("CALL EditarAportacion(?, ?)");
$sql->bind_param("is", $id_aportacion, $contenido);

// Execute query
if ($sql->execute()) {
    echo json_encode(["success" => true, "message" => "Aportación actualizada"]);
} else {
    echo json_encode(["error" => "Error en el servidor"]);
}

// Close connection
$sql->close();
$conn->close();
?>