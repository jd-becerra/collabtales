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
    echo json_encode(["error" => "Parámetros inválidos"]);
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
$sql_bloqueado->close();

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

// Checar si la aportación ya existe (no importa si no se afecta el contenido)
$sql = $conn->prepare("UPDATE Aportacion SET contenido = ? WHERE id_aportacion = ? AND fk_cuento = ?");
$sql->bind_param("sii", $contenido, $id_aportacion, $id_cuento);
if (!$sql->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Error de servidor."]);
    exit();
}
// Si la actualización fue exitosa
http_response_code(200);
echo json_encode(["message" => "Aportación actualizada correctamente"]);

// Cerrar conexiones
$sql->close();
$conn->close();
?>