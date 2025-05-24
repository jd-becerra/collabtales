<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("POST"); // Validar que el método sea POST

include('config.php');
include("jwt.php");
include("rate_limit.php");

// Primero, checar que no se exceda de 10 peticiones por minuto
$ip = $_SERVER['REMOTE_ADDR'];
$endpoint_name = "registrar_alumno";
$limit = 10; // 10 peticiones
$interval_seconds = 60; // 1 minuto
if (is_rate_limited($conn, $endpoint_name, $ip, $limit, $interval_seconds)) {
    echo json_encode(["error" => "Demasiadas peticiones. Intenta de nuevo más tarde."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
// Si hay más de 3 parámetros
if (!is_array($data) || count($data) !== 3) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

if (empty($data['nombre']) || empty($data['contrasena']) || empty($data['correo'])) {
    echo json_encode(["error" => "Nombre, contraseña y correo son obligatorios"]);
    exit;
}

// Eliminar espacios en blanco y caracteres especiales HTML, para evitar inyección XSS
$nombre = trim(htmlspecialchars($data['nombre']));
$contrasena = trim($data['contrasena']); // No se aplica htmlspecialchars porque la contraseña puede contener caracteres especiales
$correo = trim(htmlspecialchars($data['correo']));

// Validar longitud de nombre y contraseña para evitar ataques DoS, entre otros
if (strlen($nombre) > 50) {
    http_response_code(400);
    echo json_encode(["error" => "Campos no válidos"]);
    exit;
}

if (strlen($contrasena) > 72) { // BCRYPT sólo toma en cuenta los primeros 72 caracteres
    http_response_code(400);
    echo json_encode(["error" => "Campos no válidos"]);
    exit;
}

// Validar longitud mínima de la nueva contraseña (min 8 caracteres)
if (strlen($contrasena) < 8) {
    http_response_code(400);
    echo json_encode(["error" => "Campos no válidos"]);
    exit;
}

// Validar que la nueva contraseña tenga al menos un carácter especial
if (!preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
    http_response_code(400);
    echo json_encode(["error" => "Campos no válidos"]);
    exit;
}

if (strlen($correo) > 100 or !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Campos no válidos"]);
    exit;
}

// Hasheo de la contraseña
$options = ['cost' => 12];  // Incrementar el costo de iteración para hacer más lenta la generación del hash, pero procurando no hacerlo demasiado lento para el servidor
$hash_contraseña = password_hash($contrasena, PASSWORD_BCRYPT, $options);

// Preparar query para evitar inyección SQL
$stmt = $conn->prepare("CALL AñadirAlumno(?, ?, ?)");
$stmt->bind_param("sss", $nombre, $hash_contraseña, $correo);
$stmt->execute();

$stmt->bind_result($result);
$stmt->fetch();

if ($result == "El usuario ya existe") {
    http_response_code(409);
    echo json_encode(["error" => "Registro ya existente"]);
    exit;
} elseif ($result > 0) {
    // El usuario ha creado una cuenta correctamente, permitir iniciar sesión
    $payload = ["id_alumno" => $result];
    $token = generate_jwt($payload);

    http_response_code(201);
    echo json_encode([
        "token" => $token,
        "id_alumno" => $result
    ]);
    exit;
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor"]);
    exit;
}
$stmt->close();
?>
