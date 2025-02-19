<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);

// Check if $data is valid
if (!$data || !isset($data['id_alumno'])) {
    echo json_encode(["error" => "Invalid request: id_alumno is missing"]);
    exit;
}

$id_alumno = $data['id_alumno'];

$sql = "SELECT * FROM Alumno WHERE id_alumno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_alumno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch data and return as JSON
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(["error" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
