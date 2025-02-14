<?php
include('connect_db.php');

// Assuming you're using the 'nombre' parameter in your query
$nombre = $_POST['nombre'];
$contrasena = $_POST['contrasena'];

$sql = "SELECT id_alumno FROM Alumno WHERE nombre = '$nombre' AND contrasena = '$contrasena'";
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
