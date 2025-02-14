
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include('connect_db.php');

$id_cuento = $_POST['id_cuento'];

$sql = "SELECT * FROM Cuento WHERE id_cuento = '$id_cuento'";
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
