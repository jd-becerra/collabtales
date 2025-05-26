<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();
include('config.php');

$data = json_decode(file_get_contents('php://input'), true);
if (count($data) !== 1 || !isset($data['id_cuento']) || !is_numeric($data['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$id_cuento = (int) $data['id_cuento'];
$id_alumno = $user['id_alumno'] ?? null;

// Validar que el cuento existe y que el usuario autenticado es el dueño del cuento
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

// Finalmete, eliminar el cuento
$stmt = $conn->prepare("DELETE FROM Cuento WHERE id_cuento = ? AND fk_owner = ?");
$stmt->bind_param("ii", $id_cuento, $id_alumno);
$success = $stmt->execute();

if ($success && $stmt->affected_rows > 0) {
    http_response_code(200);
    echo json_encode(["success" => "Cuento eliminado correctamente"]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encotrado."]);
}

$stmt->close();
$conn->close();
?>