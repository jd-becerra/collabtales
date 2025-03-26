<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['nombre'], $data['descripcion'], $data['id_alumno'])) {
    echo json_encode(["error" => "Error: nombre, descripcion y id_alumno son obligatorios"]);
    exit();
}

$nombre = $data['nombre'];
$descripcion = $data['descripcion'];
$id_alumno = intval($data['id_alumno']);

$sql = "CALL CrearCuento(?, ?, ?)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssi", $nombre, $descripcion, $id_alumno);
    
    if ($stmt->execute()) {
        $stmt->store_result();
        $stmt->bind_result($cuento_id);
        $stmt->fetch();

        echo json_encode([
            "success" => true, 
            "id_cuento_creado" => $cuento_id
        ]);
    } else {
        echo json_encode(["error" => "Error al ejecutar la consulta"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Error al preparar la consulta"]);
}

$conn->close();
?>