<?php
include('cors_headers.php');
include('config.php');

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"), true);
$nombre = $data['nombre'] ?? '';
$contrasena = $data['contrasena'] ?? '';
error_log("nombre: $nombre, contrasena: $contrasena");


if (empty($nombre) || empty($contrasena)) {
    echo json_encode(["error" => "Nombre y contraseña son obligatorios"]);
    exit;
}

// Consulta sin preparación
$sql = "SELECT id_alumno, contrasena FROM Alumno WHERE nombre = '$nombre'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Verificar la contraseña con password_verify
    if (password_verify($contrasena, $row['contrasena'])) {
        echo json_encode(["id_alumno" => $row['id_alumno']]);
    } else {
        echo json_encode(["error" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["error" => "Usuario no encontrado"]);
}

$conn->close();
?>
