<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Si hay más de 1 parámetros
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data) || count($data) !== 1 || !isset($data['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$id_cuento = $data['id_cuento'];
if (empty($id_cuento)) {
    http_response_code(400);
    echo "Faltan parámetros obligatorios";
    exit();
}

if (!is_numeric($id_cuento)) {
    http_response_code(400);
    echo "Parámetros inválidos";
    exit();
}

// Verificar que el cuento existe y pertenece al usuario autenticado
$stmt = $conn->prepare("SELECT fk_owner FROM Cuento WHERE id_cuento = ?");
$stmt->bind_param("i", $id_cuento);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "El cuento no existe."]);
    $stmt->close();
    $conn->close();
    exit();
}
$row = $result->fetch_assoc();
if ($row['fk_owner'] !== $user['id_alumno']) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para realizar esta acción."]);
    $stmt->close();
    $conn->close();
    exit();
}

$stmt->close();
// Verificar que el usuario autenticado no esté bloqueado
$stmt = $conn->prepare("SELECT 1 FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$stmt->bind_param("ii", $id_cuento, $user['id_alumno']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para esta acción."]);
    exit();
}
$stmt->close();

// Actualizar admite_colaboradores a 1
$stmt = $conn->prepare("UPDATE Cuento SET admite_colaboradores = 1 WHERE id_cuento = ?");
$stmt->bind_param("i", $id_cuento);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Error al actualizar el cuento."]);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();
$conn->close();
http_response_code(200);
echo json_encode(["message" => "Colaboradores permitidos correctamente."]);

?>