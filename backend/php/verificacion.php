<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
include('cors_headers.php');
include('jwt_auth.php');
$user = authenticate();

include('config.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Formato inválido."]);
    exit();
}

$id_cuento = isset($data['id_cuento']) ? filter_var($data['id_cuento'], FILTER_VALIDATE_INT) : null;
$id_alumno = isset($data['id_alumno']) ? filter_var($data['id_alumno'], FILTER_VALIDATE_INT) : null;

if (is_null($id_cuento) || is_null($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

// Verificar si el alumno está unido al cuento
$query_union = "SELECT 1 FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?";
$stmt_union = $conn->prepare($query_union);
$stmt_union->bind_param("ii", $id_cuento, $id_alumno);
$stmt_union->execute();
$result_union = $stmt_union->get_result();

if ($result_union->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes acceso a este cuento."]);
    $stmt_union->close();
    $conn->close();
    exit();
}
$stmt_union->close();

// Verificar si el alumno es el dueño del cuento
$query_dueño = "SELECT 1 FROM Cuento WHERE id_cuento = ? AND fk_owner = ?";
$stmt_dueño = $conn->prepare($query_dueño);
$stmt_dueño->bind_param("ii", $id_cuento, $id_alumno);
$stmt_dueño->execute();
$result_dueño = $stmt_dueño->get_result();

if ($result_dueño->num_rows > 0) {
    echo json_encode(["success" => "Eres el dueño del cuento.", "es_dueno" => true]);
} else {
    echo json_encode(["success" => "Estás unido al cuento, pero no eres el dueño.", "es_dueno" => false]);
}

$stmt_dueño->close();
$conn->close();
?>
