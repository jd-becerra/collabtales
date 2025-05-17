<?php
include('cors_headers.php');
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = isset($data['id_cuento']) ? filter_var($data['id_cuento'], FILTER_VALIDATE_INT) : null;
$id_alumno = isset($data['id_alumno']) ? filter_var($data['id_alumno'], FILTER_VALIDATE_INT) : null;

if (!$id_cuento || !$id_alumno) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

// Verificar si el cuento existe y pertenece al usuario
$verificar_sql = "SELECT id_cuento FROM cuento WHERE id_cuento = ? AND fk_owner = ?";
$stmt_verificar = $conn->prepare($verificar_sql);
$stmt_verificar->bind_param("ii", $id_cuento, $id_alumno);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "El cuento no existe o no tienes permisos para publicarlo."]);
    $stmt_verificar->close();
    $conn->close();
    exit();
}
$stmt_verificar->close();

// Publicar el cuento
$sql = "UPDATE cuento SET publicado = 1 WHERE id_cuento = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cuento);

if ($stmt->execute()) {
    echo json_encode(["success" => "El cuento ha sido publicado con éxito."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al publicar el cuento.", "detalle" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
