<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

header('Content-Type: application/json');

if (count($_GET) !== 1) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$id_cuento = isset($_GET['id_cuento']) ? filter_var($_GET['id_cuento'], FILTER_VALIDATE_INT) : null;
$id_alumno = isset($user['id_alumno']) ? filter_var($user['id_alumno'], FILTER_VALIDATE_INT) : null;

if (is_null($id_cuento) || is_null($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

// Verificar si existe el cuento
$query_cuento = "SELECT 1 FROM Cuento WHERE id_cuento = ?";
$stmt_cuento = $conn->prepare($query_cuento);
$stmt_cuento->bind_param("i", $id_cuento);
$stmt_cuento->execute();
$result_cuento = $stmt_cuento->get_result();
if ($result_cuento->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Cuento no encontrado."]);
    $stmt_cuento->close();
    $conn->close();
    exit();
}
$stmt_cuento->close();

// Verificar si el alumno no está bloqueado
$query_bloqueado = "SELECT 1 FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?";
$stmt_bloqueado = $conn->prepare($query_bloqueado);
$stmt_bloqueado->bind_param("ii", $id_cuento, $id_alumno);
$stmt_bloqueado->execute();
$result_bloqueado = $stmt_bloqueado->get_result();
if ($result_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    $stmt_bloqueado->close();
    $conn->close();
    exit();
}
$stmt_bloqueado->close();

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
