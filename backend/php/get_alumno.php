<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_alumno = $data['id_alumno'];

$sql = "SELECT * FROM Alumno WHERE id_alumno = '$id_alumno'";
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
