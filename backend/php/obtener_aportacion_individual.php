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

$id_aportacion = $_GET['id_aportacion'] ?? null;

if (!$id_aportacion) {
    echo json_encode(["error" => "id_aportacion es obligatorio"]);
    exit;
}

// Ejecutar consulta
$sql = $conn->prepare("SELECT contenido FROM Aportacion WHERE id_aportacion = ?");
$sql->bind_param("i", $id_aportacion);
$sql->execute();
$sql->store_result();

// Verifica si hay resultados
if ($sql->num_rows > 0) {
    $sql->bind_result($contenido);
    $sql->fetch();  
    echo json_encode(["contenido" => $contenido]);
} else {
    echo json_encode(["error" => "Aportación no encontrada"]);
}

$sql->close();
$conn->close();
?>
