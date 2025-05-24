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

$id_cuento = $_GET['id_cuento'] ?? null;

$id_owner = $user['id_alumno'] ?? null;
if (empty($id_cuento) || empty($id_owner)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit;
}

// Verificar que el cuento existe
$sql = $conn->prepare("SELECT fk_owner FROM Cuento WHERE id_cuento = ?");
$sql->bind_param("i", $id_cuento);
$sql->execute();
$sql->store_result();
if ($sql->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado"]);
    exit;
}
// Verificar que el usuario autenticado es el dueño del cuento
$sql->bind_result($fk_owner);
$sql->fetch();
if ($fk_owner !== $user['id_alumno']) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para esta acción"]);
    exit;
}

// Ejecutar consulta
$sql = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ?");
$sql->bind_param("i", $id_cuento);
$sql->execute();
$sql->store_result();

// Verifica si hay resultados
if ($sql->num_rows > 0) {
    $sql->bind_result($contenido);
    $sql->fetch();  
    echo json_encode(["alumnos_bloqueados" => $contenido]);
} else {
    echo json_encode([]); // Regresar un array vacío si no hay bloqueados
}

$sql->close();
$conn->close();
?>
