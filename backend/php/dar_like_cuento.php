<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("POST");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Sólo se puede mandar la id del cuento
$data = json_decode(file_get_contents('php://input'), true);
if (count($data) !== 1 || !isset($data['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}
$id_cuento = $data['id_cuento'] ?? null;
// Comprobar que el id_cuento no es nulo y que es un número entero
if (!ctype_digit($id_cuento)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}
$id_cuento = (int) $id_cuento;

// Verificar si el cuento es público
$sql_publicado = $conn->prepare("SELECT publicado FROM Cuento WHERE id_cuento = ?");
$sql_publicado->bind_param("i", $id_cuento);
$sql_publicado->execute();
$result = $sql_publicado->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado."]); // El cuento no existe
    exit;
}
$row = $result->fetch_assoc();
if ($row['publicado'] != 1) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar si el usuario está bloqueado
$sql_bloqueado = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_bloqueado->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_bloqueado->execute();
$sql_bloqueado->store_result();
if ($sql_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar que no haya registro previo del like
$sql_like = $conn->prepare("SELECT fk_alumno FROM Likes WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_like->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_like->execute();
$sql_like->store_result();
if ($sql_like->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["error" => "Recurso ya existe."]);
    exit;
}

// Insertar el like en la base de datos
$sql_like = $conn->prepare("INSERT INTO Likes (fk_cuento, fk_alumno) VALUES (?, ?)");
$sql_like->bind_param("ii", $id_cuento, $user['id_alumno']);
if (!$sql_like->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor."]);
    exit;
}

http_response_code(201);
echo json_encode(["message" => "Like añadido correctamente."]);
$conn->close();
?>