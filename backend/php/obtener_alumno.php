<?php
include('cors_headers.php');
include('config.php');

if (!isset($_GET['id_alumno'])) {
    echo json_encode(["error" => "Invalid request: id_alumno is missing"]);
    exit;
}

$id_alumno = intval($_GET['id_alumno']);

$sql = "SELECT nombre, contrasena FROM Alumno WHERE id_alumno = ?";
$result = $conn->prepare($sql);
$result->bind_param("i", $id_alumno);
$result->execute();
$result->store_result();
$result->bind_result($nombre, $contrasena);
$result->fetch();

if ($result->num_rows > 0) {
    echo json_encode(["nombre" => $nombre, "contrasena" => $contrasena]);
} else {
    echo json_encode(["error" => "Alumno not found"]);
}

$result->close();
$conn->close();
?>