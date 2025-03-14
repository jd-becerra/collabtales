<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? null;
$correo = $data['correo'] ?? null;
$new_password = $data['nueva_contrasena'] ?? null;

if (empty($token) || empty($correo) || empty($new_password)) {
    echo json_encode(["error" => "Campos inválidos"]);
    exit;
}

// Validar y sanitizar correo
if (strlen($correo) > 100) {
    echo json_encode(["error" => "Correo demasiado largo"]);
    exit;
}
$correo = filter_var(trim($correo), FILTER_VALIDATE_EMAIL);
if (!$correo) {
    echo json_encode(["error" => "Correo inválido"]);
    exit;
}

// Si el token o la nueva exceden la longitud máxima, se rechaza la petición
if (strlen($token) > 100 || strlen($new_password) > 72) {
    echo json_encode(["error" => "Token o contraseña demasiado largos"]);
    exit;
}

// Obtener usuario con el correo proporcionado
$stmt = $conn->prepare("SELECT id_alumno FROM Alumno WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($id_alumno);
$stmt->fetch();
$stmt->close();

if (!$id_alumno) {
    echo json_encode(["error" => "No se encontró un usuario con ese correo"]);
    exit;
}

// Obtener token y expiración más recientes
$stmt = $conn->prepare("SELECT token, expiracion FROM TokenRestauracion WHERE fk_alumno = ? AND expiracion > ? ORDER BY expiracion DESC LIMIT 1");
$current_time = time();
$stmt->bind_param("ii", $id_alumno, $current_time);
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

// Hashear nueva contraseña
$options = ['cost' => 12];
$hash_password = password_hash($new_password, PASSWORD_BCRYPT, $options);

$stmt = $conn->prepare("UPDATE Alumno SET contrasena = ? WHERE id_alumno = ?");
$stmt->bind_param("si", $hash_password, $id_alumno);
$stmt->execute();
$stmt->close();

// Eliminar tokens de restauración para que no se pueda reutilizar
$stmt = $conn->prepare("DELETE FROM TokenRestauracion WHERE fk_alumno = ?");
$stmt->bind_param("i", $id_alumno);
$stmt->execute();
$stmt->close();

echo json_encode(["success" => "Contraseña actualizada con éxito"]);

$conn->close();
?>