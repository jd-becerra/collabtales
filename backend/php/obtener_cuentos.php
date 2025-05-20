<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$id_alumno = $user['id_alumno'];
$id_alumno = intval($id_alumno);
if (!isset($id_alumno) || $id_alumno <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Usuario no autorizado"]);
    exit;
}

$sql = $conn->prepare("CALL ListarCuentosAlumno(?)");
$sql->bind_param("i", $id_alumno);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    // Fetch all rows and store in an array
    $rows = array();
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
