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
$verificar_sql = "SELECT id_cuento FROM cuento WHERE id_cuento = ?";
$stmt_verificar = $conn->prepare($verificar_sql);
$stmt_verificar->bind_param("i", $id_cuento);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    echo json_encode(["error" => "El cuento no existe."]);
    $stmt_verificar->close();
    $conn->close();
    exit();
}
$stmt_verificar->close();

$check_sql = "SELECT fk_alumno FROM relacion_alumno_cuento WHERE fk_cuento = ? AND fk_alumno = ?";
$stmt_check = $conn->prepare($check_sql);
$stmt_check->bind_param("ii", $id_cuento, $id_alumno);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(["error" => "Ya estás unido a este cuento."]);
    $stmt_check->close();
    $conn->close();
    exit();
}
$stmt_check->close();

// Si no está unido, proceder con la unión
$sql = "CALL UnirseCuento(?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_cuento, $id_alumno);

if ($stmt->execute()) {
    echo json_encode(["success" => "Te has unido al cuento con éxito."]);
} else {
    echo json_encode(["error" => "Error al unirse al cuento."]);
}

$stmt->close();
$conn->close();
?>
