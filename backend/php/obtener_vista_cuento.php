
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

if(empty($id_cuento)){
    echo json_encode(["error" => "id_cuento es obligatorio"]);
    exit;
}

$sql = $conn->prepare("SELECT id_cuento, nombre, descripcion FROM Cuento WHERE id_cuento = ?");
$sql->bind_param("i", $id_cuento);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    // Fetch data and return as JSON
    $row = $result->fetch_assoc();
    echo json_encode([$row]);
} else {
    echo json_encode([]);
}

$conn->close();
?>
