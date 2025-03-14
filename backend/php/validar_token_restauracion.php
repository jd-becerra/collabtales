<?php
include('cors_headers.php');
include('config.php');

$token = $_GET['token'] ?? null;
$correo = $_GET['correo'] ?? null;

if (empty($token) || empty($correo)) {
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

// Obtener usuario con el correo proporcionado
$stmt = $conn->prepare("SELECT id_alumno FROM Alumno WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($id_usuario);
$stmt->fetch();
$stmt->close();

if (!$id_usuario) {
    echo json_encode(["error" => "No se encontró un usuario con ese correo"]);
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

// Mandar id_usuario para que el frontend pueda redirigir a la página de restablecimiento
echo json_encode(["id_usuario" => $id_usuario]);

$conn->close();
?>