<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("POST");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
// Si hay más de 2 parámetros
if (!is_array($data) || count($data) !== 2) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

if (!isset($data['id_cuento']) || !isset($data['id_alumno_bloquear'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$id_cuento = $data['id_cuento'];
$id_alumno_bloquear = $data['id_alumno_bloquear'];

if (!is_numeric($id_cuento) || !is_numeric($id_alumno_bloquear)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$id_owner = $user['id_alumno'];

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
if ($row['fk_owner'] !== $id_owner) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para realizar esta acción."]);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Verificar que el usuario autenticado no sea el mismo que el alumno a bloquear
if ($id_owner === $id_alumno_bloquear) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para realizar esta acción."]);
    $conn->close();
    exit();
}


try {
    $stmt = $conn->prepare("DELETE FROM Aportacion WHERE fk_cuento = ? AND fk_alumno = ?");
    $stmt->bind_param("ii", $id_cuento, $id_alumno_bloquear);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?");
    $stmt->bind_param("ii", $id_cuento, $id_alumno_bloquear);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => "Error with exception: " . $e->getMessage()]);
    $conn->close();
    exit();
}
// Insertar el bloqueo
$stmt = $conn->prepare("INSERT INTO ListaNegra (fk_cuento, fk_alumno) VALUES (?, ?)");
$stmt->bind_param("ii", $id_cuento, $id_alumno_bloquear);

if ($stmt->execute()) {
    echo json_encode(["success" => "Alumno bloqueado correctamente."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor."]);
}

$stmt->close();
$conn->close();
?>