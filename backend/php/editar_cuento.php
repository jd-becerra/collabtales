<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'] ?? null;
$id_alumno = $user['id_alumno'] ?? null; // We already have the user authenticated (don't trust client-side data)
$nombre_cuento = trim($data['nombre_cuento'] ?? '');
$descripcion_cuento = trim($data['descripcion_cuento'] ?? '');

if (!$id_cuento || !$nombre_cuento || !$descripcion_cuento || !$id_alumno) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros requeridos."]);
    exit();
}

// Optional: Validate max lengths
if (strlen($nombre_cuento) > 255 || strlen($descripcion_cuento) > 511) {
    http_response_code(400);
    echo json_encode(["error" => "El nombre o descripción exceden el tamaño permitido."]);
    exit();
}

// Use prepared statement to prevent injection
$stmt = $conn->prepare("
    UPDATE Cuento 
    SET nombre = ?, descripcion = ? 
    WHERE id_cuento = ? AND fk_owner = ?
");
$stmt->bind_param("ssii", $nombre_cuento, $descripcion_cuento, $id_cuento, $id_alumno);
$success = $stmt->execute();

if ($success && $stmt->affected_rows > 0) {
    echo json_encode(["success" => "Cuento actualizado correctamente."]);
} else {
    http_response_code(403);
    echo json_encode(["error" => "No se pudo actualizar el cuento. Asegúrate de tener permisos."]);
}

$stmt->close();
$conn->close();
?>