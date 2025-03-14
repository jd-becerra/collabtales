<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);

$id_alumno = $data['id_alumno'] ?? null;
$nombre = $data['nombre'] ?? null;
$contrasena = $data['contrasena'] ?? null;
$correo = $data['correo'] ?? null;

if (empty($id_alumno) || empty($nombre) || empty($contrasena) || empty($correo)) {
    echo json_encode(["error" => "ID, nombre, contraseña y correo son obligatorios"]);
    exit;
}

$id_alumno = intval($id_alumno);
if ($id_alumno <= 0) {
    echo json_encode(["error" => "ID inválido"]);
    exit;
}

$nombre = trim(htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'));
$contrasena = trim($contrasena);
$correo = trim(htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'));

if (strlen($nombre) > 50) {
    echo json_encode(["error" => "El nombre es demasiado largo"]);
    exit;
}
if (strlen($contrasena) > 72) {
    echo json_encode(["error" => "La contraseña no debe superar los 72 caracteres"]);
    exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Correo electrónico inválido"]);
    exit;
}

$options = ['cost' => 12];
$hash_contraseña = password_hash($contrasena, PASSWORD_BCRYPT, $options);

$stmt = $conn->prepare("CALL EditarAlumno(?, ?, ?, ?)");
$stmt->bind_param("isss", $id_alumno, $nombre, $hash_contraseña, $correo);
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