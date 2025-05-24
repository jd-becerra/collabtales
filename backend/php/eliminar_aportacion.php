<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Si hay más de 1 parámetro
if (count($_GET) !== 1) {
    http_response_code(400);
    echo "Parámetros inválidos";
    exit();
}

$id_alumno = $user['id_alumno'];
$id_cuento = $_GET['id_cuento'];
if (empty($id_cuento) || empty($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

$stmt = $conn->prepare("CALL AbandonarCuento(?, ?)");
$stmt->bind_param("ii", $id_cuento, $id_alumno);

if ($stmt->execute()) {
    echo json_encode(["message" => "Aportación eliminada correctamente"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al eliminar la aportación: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
