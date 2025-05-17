
<?php
include('cors_headers.php');
include('jwt_auth.php');
$user = authenticate();

include('config.php');

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
