<?php
include('cors_headers.php');
include('config.php');
include("jwt.php");

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['nombre']) || empty($data['contrasena'])) {
    echo json_encode(["error" => "Nombre y contraseña son obligatorios"]);
    exit;
}

$nombre = trim(htmlspecialchars($data['nombre'], ENT_QUOTES, 'UTF-8'));
$contrasena = trim($data['contrasena']);

if (strlen($nombre) > 50) {
    echo json_encode(["error" => "El nombre es demasiado largo"]);
    exit;
}
if (strlen($contrasena) > 72) {
    echo json_encode(["error" => "La contraseña no debe superar los 72 caracteres"]);
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
        $payload = ["id_alumno" => $id_alumno];
        $token = generate_jwt($payload);
        echo json_encode([
            "token" => $token,
            "id_alumno" => $id_alumno
        ]);
    } else {
        echo json_encode(["error" => "Credenciales incorrectas"]);
    }
} else {
    echo json_encode(["error" => "Error al iniciar sesión"]);
}

$stmt->close();
$conn->close();
?>