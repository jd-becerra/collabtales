<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("DELETE");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$id_cuento = $_GET['id_cuento'];
$id_alumno = $user['id_alumno'];
$stmt = $conn->prepare("DELETE FROM ListaNegra WHERE id_cuento = ? AND id_alumno = ?");
$stmt->bind_param("ii", $id_cuento, $id_alumno);

if ($stmt->execute()) {
    echo "Alumno desbloqueado correctamente";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
