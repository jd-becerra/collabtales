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

$sql = "SELECT * FROM Historial WHERE fk_cuento = '$id_cuento'";
$result = $conn->query($sql);

$rows = array(); // Initialize an array to store the results

if ($result->num_rows > 0) {
    // Loop through the result set and fetch all rows
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    // Return the array as JSON
    echo json_encode($rows);
} else {
    echo json_encode([]); // Return an empty array if no data is found
}

$conn->close();
?>
