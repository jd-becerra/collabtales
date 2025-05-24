<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Si hay más de 1 parámetro
if (count($_GET) !== 1) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

$id_cuento = $_GET['id_cuento'];
$id_alumno = $user['id_alumno'];

if (empty($id_cuento) || empty($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit;
}

// Verificar si el alumno está bloqueado
$sql = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$sql->bind_param("ii", $id_cuento, $id_alumno);
$sql->execute();
$sql->store_result();
if ($sql->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

$sql = $conn->prepare("CALL ListarCuentoAportacionesConAlumnos(?)");
$sql->bind_param("i", $id_cuento);
$sql->execute();
$result = $sql->get_result();

// Almacenar los resultados en un array
$aportaciones = [];
while ($row = $result->fetch_assoc()) {
    $aportaciones[] = $row;
}

// Cerrar el statement para evitar el error de comandos fuera de sincronización
$sql->close();

// Obtener la id_aportacion del alumno
$sql = $conn->prepare("SELECT id_aportacion FROM Aportacion WHERE fk_cuento = ? AND fk_alumno = ?");
$sql->bind_param("ii", $id_cuento, $id_alumno);
$sql->execute();
$result = $sql->get_result();
$id_aportacion = $result->fetch_assoc()['id_aportacion'] ?? null;

// Cerrar el statement
$sql->close();

// Retornar JSON con los datos
echo json_encode([
    "aportaciones" => $aportaciones,
    "id_aportacion_alumno" => $id_aportacion
]);

$conn->close();
?>
