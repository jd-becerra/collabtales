<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);

if (count($data) !== 1) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit();
}

$id_cuento = isset($data['id_cuento']) ? filter_var($data['id_cuento'], FILTER_VALIDATE_INT) : null;
$id_alumno = isset($user['id_alumno']) ? filter_var($user['id_alumno'], FILTER_VALIDATE_INT) : null;

if (!$id_cuento || !$id_alumno) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

// Verificar si el cuento existe y pertenece al usuario
$verificar_sql = "SELECT id_cuento FROM Cuento WHERE id_cuento = ? AND fk_owner = ?";
$stmt_verificar = $conn->prepare($verificar_sql);
$stmt_verificar->bind_param("ii", $id_cuento, $id_alumno);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para modificar este elemento."]);
    $stmt_verificar->close();
    $conn->close();
    exit();
}
$stmt_verificar->close();

// Publicar el cuento
$sql = "UPDATE Cuento SET publicado = 1 WHERE id_cuento = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cuento);

if ($stmt->execute()) {
    echo json_encode(["success" => "El cuento ha sido publicado con éxito."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor"]);
}

$stmt->close();
$conn->close();
?>
