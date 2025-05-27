<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Si hay más de 2 parámetros
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data) || count($data) !== 2 || !isset($data['id_cuento']) || !isset($data['id_alumno_desbloquear'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$id_alumno = $user['id_alumno'];

$id_cuento = $data['id_cuento'];
$id_alumno_bloqueado = $data['id_alumno_desbloquear'];

if (empty($id_cuento) || empty($id_alumno_bloqueado)) {
    http_response_code(400);
    echo "Faltan parámetros obligatorios";
    exit();
}
if (!is_numeric($id_cuento) || !is_numeric($id_alumno_bloqueado)) {
    http_response_code(400);
    echo "Parámetros inválidos";
    exit();
}

// Verificar que el usuario autenticado no esté bloquado
$stmt = $conn->prepare("SELECT 1 FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$stmt->bind_param("ii", $id_cuento, $id_alumno);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(403);
    echo "No tienes permiso para esta acción";
    exit();
}
$stmt->close();

// Validar que el usuario autenticado sea el dueño del cuento
$stmt = $conn->prepare("SELECT fk_owner FROM Cuento WHERE id_cuento = ?");
$stmt->bind_param("i", $id_cuento);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo "Recurso encontrado";
    exit();
}
$row = $result->fetch_assoc();
if ($row['fk_owner'] !== $id_alumno) {
    http_response_code(403);
    echo "No tienes permiso para esta acción";
    exit();
}
$stmt->close();

$stmt = $conn->prepare("DELETE FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$stmt->bind_param("ii", $id_cuento, $id_alumno_bloqueado);

if ($stmt->execute()) {
    echo "Alumno desbloqueado correctamente";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
