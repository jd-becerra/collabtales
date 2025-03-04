<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];

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
