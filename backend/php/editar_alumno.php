<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);

$id_alumno = $data['id_alumno'] ?? null;
$nombre = $data['nombre'] ?? null;
$contrasena = $data['contrasena'] ?? null;

if (!isset($id_alumno, $nombre, $contrasena)) {
    echo json_encode(["error" => "ID, nombre y contraseña son obligatorios"]);
    exit;
}

$sql = "CALL EditarAlumno('$id_alumno', '$nombre', '$contrasena');";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(["message" => $row['result'] ?? "Error en la actualización"]);
} else {
    echo json_encode(["error" => "Error en la ejecución: " . $conn->error]);
}

$conn->close();
?>