
<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];

if(empty($id_cuento)){
    echo json_encode(["error" => "id_cuento es obligatorio"]);
    exit;
}

$sql = "SELECT id_cuento, nombre, descripcion FROM Cuento WHERE id_cuento = '$id_cuento'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch data and return as JSON
    $row = $result->fetch_assoc();
    echo json_encode([$row]);
} else {
    echo json_encode([]); // Return an empty array if no data is found
}

$conn->close();
?>
