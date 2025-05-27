<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();
include('config.php');

// Read raw JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data) || count($data) !== 3) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit();
}

// Validate input
if (!isset($data['id_aportacion']) || !isset($data['contenido']) || !isset($data['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos 2"]);
    exit();
}

// Si el contenido no es JSON o excede el límite de caracteres
if (!is_string($data['contenido']) || mb_strlen($data['contenido'], 'UTF-8') > 8000) {
    http_response_code(400);
    echo json_encode(["error" => "Contenido inválido o demasiado largo"]);
    exit();
}

$id_aportacion = $data['id_aportacion'];
$id_cuento = $data['id_cuento'];
$contenido = $data['contenido'];

// Verificar que el alumno no esté bloqueado
$sql_bloqueado = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_alumno = ? AND fk_cuento = ?");
$sql_bloqueado->bind_param("ii", $user['id_alumno'], $id_cuento);
$sql_bloqueado->execute();
$result_bloqueado = $sql_bloqueado->get_result();
if ($result_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para realizar esta acción"]);
    exit();
}

// Verificar que el usuario está en el cuento
$stmt = $conn->prepare("SELECT fk_alumno FROM Relacion_Alumno_Cuento WHERE fk_alumno = ? AND fk_cuento = ?");
$stmt->bind_param("ii", $user['id_alumno'], $id_cuento);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para realizar esta acción"]);
    exit();
}

// Validar que el usuario autenticado sea el dueño de la aportación
$id_alumno = $user['id_alumno'] ?? null;
$stmt = $conn->prepare("SELECT fk_alumno FROM Aportacion WHERE id_aportacion = ? AND fk_cuento = ?");
$stmt->bind_param("ii", $id_aportacion, $id_cuento);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado"]);
    exit();
}
$row = $result->fetch_assoc();
if ($row['fk_alumno'] !== $id_alumno) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para esta acción"]);
    exit();
}
$stmt->close();

// Prepare the SQL query
$sql = $conn->prepare("CALL EditarAportacion(?, ?)");
$sql->bind_param("is", $id_aportacion, $contenido);

// Execute query
if ($sql->execute()) {
    echo json_encode(["success" => true, "message" => "Aportación actualizada"]);
} else {
    echo json_encode(["error" => "Error en el servidor"]);
}

// Close connections
$sql_bloqueado->close();

$sql->close();

$conn->close();
?>