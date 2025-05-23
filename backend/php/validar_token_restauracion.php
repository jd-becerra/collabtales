<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
// ESTE ARCHIVO NO NECESITA JWT
include('config.php');
include("rate_limit.php");

// Primero, checar que no se exceda de 10 peticiones por minuto
$ip = $_SERVER['REMOTE_ADDR'];
$endpoint_name = "validar_token_restauracion";
$limit = 10; // 10 peticiones
$interval_seconds = 60; // 1 minuto
if (is_rate_limited($conn, $endpoint_name, $ip, $limit, $interval_seconds)) {
    echo json_encode(["error" => "Demasiadas peticiones. Intenta de nuevo más tarde."]);
    exit;
}

// Si hay más de 2 parámetros
if (count($_GET) !== 2) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

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