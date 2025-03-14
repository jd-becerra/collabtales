<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? null;
$id_usuario = $data['id_usuario'] ?? null;
$new_password = $data['nueva_contrasena'] ?? null;

if (empty($token) || empty($id_usuario) || empty($new_password)) {
    echo json_encode(["error" => "Campos inválidos"]);
    exit;
}

// Si el token o la nueva exceden la longitud máxima, se rechaza la petición
if (strlen($token) > 100 || strlen($new_password) > 72) {
    echo json_encode(["error" => "Token o contraseña demasiado largos"]);
    exit;
}

// Validar y sanitizar id_alumno
if (!is_numeric($id_usuario)) {
    echo json_encode(["error" => "ID de alumno inválido"]);
    exit;
}

// Obtener token y expiración más recientes
$stmt = $conn->prepare("SELECT token, expiracion FROM TokenRestauracion WHERE fk_alumno = ? AND expiracion > ? ORDER BY expiracion DESC LIMIT 1");
$current_time = time();
$stmt->bind_param("ii", $id_usuario, $current_time);
$stmt->execute();
$stmt->bind_result($hashed_token, $expiration);
$stmt->fetch();
$stmt->close();

if (!$hashed_token || time() > $expiration) {
    echo json_encode(["error" => "Token inválido o expirado. Solicita un nuevo enlace"]);
    exit;
}

// Verificar token
if (!password_verify($token, $hashed_token)) {
    echo json_encode(["error" => "Token inválido"]);
    exit;
}

// Obtener la contraseña actual del usuario
$stmt = $conn->prepare("SELECT contrasena FROM Alumno WHERE id_alumno = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($current_password_hash);
$stmt->fetch();
$stmt->close();

// Verificar si la nueva contraseña es la misma que la actual
if (password_verify($new_password, $current_password_hash)) {
    echo json_encode(["error" => "No se puede repetir la contraseña que tienes actualmente"]);
    exit;
}

// Hashear nueva contraseña
$options = ['cost' => 12];
$hash_password = password_hash($new_password, PASSWORD_BCRYPT, $options);

$stmt = $conn->prepare("UPDATE Alumno SET contrasena = ? WHERE id_alumno = ?");
$stmt->bind_param("si", $hash_password, $id_usuario);
$stmt->execute();
$stmt->close();

// Eliminar tokens de restauración para que no se pueda reutilizar
$stmt = $conn->prepare("DELETE FROM TokenRestauracion WHERE fk_alumno = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->close();

echo json_encode(["success" => "Contraseña actualizada con éxito"]);

$conn->close();
?>