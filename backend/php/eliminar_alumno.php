<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();
include('config.php');

$id_alumno = isset($user['id_alumno']) ? filter_var($user['id_alumno'], FILTER_VALIDATE_INT) : null;

if (empty($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

$stmt = $conn->prepare("CALL EliminarAlumno(?)");
$stmt->bind_param("i", $id_alumno);

if ($stmt->execute()) {
    echo json_encode(["message" => "Usuario eliminado correctamente"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al eliminar el usuario: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

