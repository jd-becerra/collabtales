<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents('php://input'), true);
if (count($data) !== 1 || !isset($data['id_cuento']) || !is_numeric($data['id_cuento'])) {
    http_response_code(400);
    echo "Parámetros inválidos";
    exit();
}

$id_alumno = $user['id_alumno'];
$id_cuento = $data['id_cuento'];

// Verificar que si el usuario está bloqueado
$sql_bloqueado = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_bloqueado->bind_param("ii", $id_cuento, $id_alumno);
$sql_bloqueado->execute();
$sql_bloqueado->store_result();
if ($sql_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar que el usuario es colaborador del cuento
$sql_colaborador = $conn->prepare("SELECT fk_alumno FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_colaborador->bind_param("ii", $id_cuento, $id_alumno);
$sql_colaborador->execute();
$colaborador_result = $sql_colaborador->get_result();
if ($colaborador_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar que el cuento existe
$sql_cuento = $conn->prepare("SELECT id_cuento FROM Cuento WHERE id_cuento = ?");
$sql_cuento->bind_param("i", $id_cuento);
$sql_cuento->execute();
$result = $sql_cuento->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Cuento no encontrado."]);
    exit;
}

// Eliminar las aportaciones del usuario en el cuento
$stmt_aportacion = $conn->prepare("DELETE FROM Aportacion WHERE fk_cuento = ? AND fk_alumno = ?");
if (!$stmt_aportacion) {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor"]);
    exit;
}
$stmt_aportacion->bind_param("ii", $id_cuento, $id_alumno);

if (!$stmt_aportacion->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor"]);
    $stmt_aportacion->close();
    exit;
}
$stmt_aportacion->close();

// Finalmente, eliminar la relación del usuario con el cuento
$stmt = $conn->prepare("DELETE FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor"]);
    exit;
}
$stmt->bind_param("ii", $id_cuento, $id_alumno);

if ($stmt->execute()) {
    echo json_encode(["message" => "Aportación y relación eliminadas correctamente"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al eliminar la relación: " . $stmt->error]);
}


// Cerrar las conexiones
$sql_bloqueado->close();
$sql_colaborador->close();
$sql_cuento->close();
$stmt->close();

$conn->close();
?>
