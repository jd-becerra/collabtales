<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();
include('config.php');

$id_alumno = $user['id_alumno'] ?? null;

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_cuento'], $data['nombre_cuento'], $data['descripcion_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$id_cuento = $data['id_cuento'] ?? null;
$nombre_cuento = trim($data['nombre_cuento'] ?? '');
$descripcion_cuento = trim($data['descripcion_cuento'] ?? '');

// Validar que los parámetros excedan el límite de caracteres
if (strlen($nombre_cuento) > 255 || strlen($descripcion_cuento) > 511) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

// Validar que el usuario autenticado sea el dueño del cuento
$stmt = $conn->prepare("SELECT fk_owner FROM Cuento WHERE id_cuento = ?");
$stmt->bind_param("i", $id_cuento);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Cuento no encontrado."]);
    exit();
}
$row = $result->fetch_assoc();
if ($row['fk_owner'] !== $id_alumno) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para esta acción."]);
    exit();
}
$stmt->close();

// Use prepared statement to prevent injection
$stmt = $conn->prepare("
    UPDATE Cuento 
    SET nombre = ?, descripcion = ? 
    WHERE id_cuento = ? AND fk_owner = ?
");
$stmt->bind_param("ssii", $nombre_cuento, $descripcion_cuento, $id_cuento, $id_alumno);
$success = $stmt->execute();

if ($success) {
    echo json_encode(["success" => "Cuento actualizado correctamente."]);
} else {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para realizar esta acción."]);
}

$stmt->close();
$conn->close();
?>