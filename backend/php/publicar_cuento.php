<?php
include('cors_headers.php');
include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$id_cuento = $data['id_cuento'];
$id_alumno = $data['id_alumno'];

if (empty($id_cuento) || empty($id_alumno)) {
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

// Verificar si el cuento existe
$verificar_sql = "SELECT id_cuento FROM cuentos WHERE id_cuento = ? AND id_alumno = ?";
$stmt_verificar = $conn->prepare($verificar_sql);
$stmt_verificar->bind_param("ii", $id_cuento, $id_alumno);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    echo json_encode(["error" => "El cuento no existe o no es tuyo."]);
    $stmt_verificar->close();
    $conn->close();
    exit();
}
$stmt_verificar->close();

$sql = "UPDATE cuentos SET publicado = 1 WHERE id_cuento = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cuento);

if ($stmt->execute()) {
    echo json_encode(["success" => "El cuento ha sido publicado con éxito."]);
} else {
    echo json_encode(["error" => "Error al publicar el cuento."]);
}

$stmt->close();
$conn->close();
?>
