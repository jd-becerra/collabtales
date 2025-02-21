<?php
// NO HE PROBADO ESTA API, PUEDE QUE TENGA ERRORES

include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_alumno'])) {
    echo json_encode(["error" => "Invalid request: id_alumno is missing"]);
    exit;
}

$id_alumno = $data['id_alumno'];

$sql = "SELECT nombre, contrasena FROM Alumno WHERE id_alumno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_alumno);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($nombre, $contrasena);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
    echo json_encode(["nombre" => $nombre, "contrasena" => $contrasena]);
} else {
    echo json_encode(["error" => "No data found"]);
}

$stmt->close();
$conn->close();
?>
