<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();
include('config.php');

$id_cuento = $_GET['id_cuento'] ?? null;
$id_alumno = $user['id_alumno'] ?? null;

// Validate input
if (empty($id_cuento) || empty($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}
$id_cuento = intval($id_cuento);

// Optional: Check if the user owns the cuento before deleting
$stmt = $conn->prepare("DELETE FROM Cuento WHERE id_cuento = ? AND fk_owner = ?");
$stmt->bind_param("ii", $id_cuento, $id_alumno);
$success = $stmt->execute();

if ($success && $stmt->affected_rows > 0) {
    echo json_encode(["success" => "Cuento eliminado correctamente"]);
} else {
    http_response_code(403);
    echo json_encode(["error" => "No se pudo eliminar el cuento. Asegúrate de tener permisos."]);
}

$stmt->close();
$conn->close();
?>