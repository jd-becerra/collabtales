<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Parse PUT data into $_PUT
parse_str(file_get_contents("php://input"), $_PUT);

$id_alumno = $user['id_alumno'] ?? null;
$nombre = $_PUT['nombre'] ?? null;

if (empty($id_alumno) || empty($nombre)) {
    echo json_encode(["error" => "ID y nombre son obligatorios"]);
    exit;
}

$id_alumno = intval($id_alumno);
if ($id_alumno <= 0) {
    echo json_encode(["error" => "ID inválido"]);
    exit;
}

$nombre = trim(htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'));

if (strlen($nombre) > 50) {
    echo json_encode(["error" => "El nombre es demasiado largo"]);
    exit;
}

$stmt = $conn->prepare("CALL EditarAlumno(?, ?)");
$stmt->bind_param("is", $id_alumno, $nombre);
$stmt->execute();
$stmt->store_result();

if ($stmt->affected_rows > 0) {
    echo json_encode(["message" => "Usuario actualizado correctamente"]);
} else {
    echo json_encode(["error" => "No se pudo actualizar el usuario"]);
}

$stmt->close();
$conn->close();
?>
