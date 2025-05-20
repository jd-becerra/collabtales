<?php
include('cors_headers.php');
include('config.php');
include("jwt.php");
include("rate_limit.php");

// Primero, checar que no se exceda de 10 peticiones por minuto
$ip = $_SERVER['REMOTE_ADDR'];
$endpoint_name = "iniciar_sesion";
$limit = 10; // 10 peticiones
$interval_seconds = 60; // 1 minuto
if (is_rate_limited($conn, $endpoint_name, $ip, $limit, $interval_seconds)) {
    http_response_code(429);
    echo json_encode(["error" => "Demasiadas peticiones. Intenta de nuevo más tarde."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['nombre']) || empty($data['contrasena'])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit;
}

$nombre = trim(htmlspecialchars($data['nombre'], ENT_QUOTES, 'UTF-8'));
$contrasena = trim($data['contrasena']);

if (strlen($nombre) > 50 || strlen($contrasena) > 72) {
    http_response_code(400);
    echo json_encode(["error" => "Campos exceden el tamaño permitido"]);
    exit;
}

$stmt = $conn->prepare("SELECT id_alumno, contrasena FROM Alumno WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id_alumno, $hash_contrasena);
    $stmt->fetch();

    if (password_verify($contrasena, $hash_contrasena)) {
        // El usuario ha iniciado sesión correctamente
        // Antes que nada, limpiamos la tabla de rate limit para no bloquear al usuario
        $stmt_clear = $conn->prepare("CALL ResetearRateLimit(?, ?)");
        $stmt_clear->bind_param("si", $endpoint_name, $ip);
        $stmt_clear->execute();
        $stmt_clear->close();
        
        // Generar el token JWT para su sesión
        $payload = ["id_alumno" => $id_alumno];
        $token = generate_jwt($payload);
        echo json_encode([
            "token" => $token,
            "id_alumno" => $id_alumno
        ]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Credenciales incorrectas"]);
        exit;
    }
} else {
    http_response_code(401);
    echo json_encode(["error" => "Error al iniciar sesión"]);
    exit;
}

$stmt->close();
$conn->close();
?>