<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("POST");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
// Si hay más de 2 parámetros
if (!is_array($data) || count($data) !== 2) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

if (!isset($data['nombre'], $data['descripcion'], $user['id_alumno'])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

// Si la id_alumno no es un número
if (!is_numeric($user['id_alumno'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$nombre = $data['nombre'];
$descripcion = $data['descripcion'];
$id_alumno = intval($user['id_alumno']);

$sql = "CALL CrearCuento(?, ?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssi", $nombre, $descripcion, $id_alumno);
    
    if ($stmt->execute()) {
        $stmt->store_result();
        $stmt->bind_result($cuento_id);
        $stmt->fetch();
        
        http_response_code(201);
        echo json_encode([
            "success" => true, 
            "id_cuento_creado" => $cuento_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error en el servidor."]);
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error en el servidor."]);
}

$conn->close();
?>