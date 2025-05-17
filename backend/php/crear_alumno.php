<?php
include('cors_headers.php');
include('config.php');
include("jwt.php");

$data = json_decode(file_get_contents("php://input"), true);

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
    echo json_encode(["error" => "El nombre es demasiado largo"]);
    exit;
}
if (strlen($contrasena) > 72) { // BCRYPT sólo toma en cuenta los primeros 72 caracteres
    echo json_encode(["error" => "La contraseña no debe superar los 72 caracteres"]);
    exit;
}

// Validar longitud mínima de la nueva contraseña
if (strlen($contrasena) < 8) {
    echo json_encode(["error" => "La contraseña debe tener mínimo 8 caracteres"]);
    exit;
}

// Validar que la nueva contraseña tenga al menos un carácter especial
if (!preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
    echo json_encode(["error" => "La contraseña debe tener al menos un carácter especial"]);
    exit;
}

if (strlen($correo) > 100 or !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Correo no válido"]);
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
    echo json_encode(["error" => "El usuario ya existe"]);
    exit;
} elseif ($result > 0) {
    // El usuario ha creado una cuenta correctamente, permitir iniciar sesión
    $payload = ["id_alumno" => $result];
    $token = generate_jwt($payload);
    echo json_encode([
        "token" => $token,
        "id_alumno" => $result
    ]);
    exit;
} else {
    echo json_encode(["error" => "Error al crear el usuario"]);
    exit;
}
$stmt->close();
?>
