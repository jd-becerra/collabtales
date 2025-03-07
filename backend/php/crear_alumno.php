<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['nombre']) || empty($data['contrasena'])) {
    echo json_encode(["error" => "Nombre y contraseña son obligatorios"]);
    exit;
}

$nombre = $data['nombre'];
$contrasena = $data['contrasena'];
$hash_contraseña = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "CALL AñadirAlumno('$nombre', '$hash_contraseña')";

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $row = $result->fetch_assoc();
            if (isset($row['result']) && $row['result'] === 'El usuario ya existe') {
                echo json_encode(["result" => "El usuario ya existe"]);
            } elseif (isset($row['id_alumno'])) {
                echo json_encode(["id_alumno" => $row['id_alumno']]);
            } else {
                echo json_encode(["error" => "No se pudo obtener el ID del usuario"]);
            }
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo json_encode(["error" => "Error en la base de datos: " . $conn->error]);
}

$conn->close();
?>
