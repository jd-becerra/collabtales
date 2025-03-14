<?php
include('cors_headers.php');
include('config.php');

if (!isset($_GET['id_alumno'])) {
    echo json_encode(["error" => "Invalid request: id_alumno is missing"]);
    exit;
}

$id_alumno = intval($_GET['id_alumno']);

if ($id_alumno <= 0) {
    echo json_encode(["error" => "Invalid id_alumno"]);
    exit;
}

$stmt = $conn->prepare("SELECT nombre, correo FROM Alumno WHERE id_alumno = ?");
$stmt->bind_param("i", $id_alumno);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($nombre, $correo);
$stmt->fetch();

if ($stmt->num_rows > 0) {
    echo json_encode(["nombre" => $nombre, "correo" => $correo]);
} else {
    echo json_encode(["error" => "Alumno not found"]);
}

$stmt->close();
$conn->close();
?>