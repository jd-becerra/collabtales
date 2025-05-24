<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

if ($user['id_alumno'] <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Sesión inválida"]);
    exit;
}

include('config.php');
$id_alumno = $user['id_alumno'];
if (!isset($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Sesión inválida"]);
    exit;
}
$id_alumno = intval($id_alumno);

if ($id_alumno <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Sesión inválida"]);
    exit;
}

$stmt = $conn->prepare("SELECT id_alumno, nombre, correo FROM Alumno WHERE id_alumno = ?");
$stmt->bind_param("i", $id_alumno);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id_alumno, $nombre, $correo);
$stmt->fetch();

if ($stmt->num_rows > 0) {
    http_response_code(200);
    echo json_encode(["id_alumno" => $id_alumno, "nombre" => $nombre, "correo" => $correo]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado"]);
}

$stmt->close();
$conn->close();
?>