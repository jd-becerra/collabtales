<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("POST");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$data = json_decode(file_get_contents("php://input"), true);
if (count($data) !== 1) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$codigo = $data['codigo'];
$id_alumno = $user['id_alumno'];

if (empty($codigo) || empty($id_alumno)) {
    echo json_encode(["error" => "Faltan parámetros obligatorios."]);
    exit();
}

// Si el código no es una string
if (!is_string($codigo)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

// Verificar si el cuento existe y obtener su ID
$verificar_sql = "SELECT id_cuento FROM Cuento WHERE codigo_compartir = ? LIMIT 1";
$stmt_verificar = $conn->prepare($verificar_sql);
$stmt_verificar->bind_param("s", $codigo);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado."]);
    $stmt_verificar->close();
    $conn->close();
    exit();
}
$row = $result_verificar->fetch_assoc();
$id_cuento = $row['id_cuento'];
$stmt_verificar->close();

// Verificar que el cuento acepte colaboradores
$cuentos_sql = "SELECT admite_colaboradores FROM Cuento WHERE id_cuento = ?";
$stmt_cuentos = $conn->prepare($cuentos_sql);
$stmt_cuentos->bind_param("i", $id_cuento);
$stmt_cuentos->execute();
$result_cuentos = $stmt_cuentos->get_result();
if ($result_cuentos->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado."]);
    $stmt_cuentos->close();
    $conn->close();
    exit();
}
$row_cuentos = $result_cuentos->fetch_assoc();
if ($row_cuentos['admite_colaboradores'] !== 1) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para realizar esta acción."]);
    $stmt_cuentos->close();
    $conn->close();
    exit();
}
$stmt_cuentos->close();

// Verificar que el alumno no esté bloqueado
$bloqueado_sql = "SELECT 1 FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?";
$stmt_bloqueado = $conn->prepare($bloqueado_sql);
$stmt_bloqueado->bind_param("ii", $id_cuento, $id_alumno);
$stmt_bloqueado->execute();
$result_bloqueado = $stmt_bloqueado->get_result();

if ($result_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    $stmt_bloqueado->close();
    $conn->close();
    exit();
}
$stmt_bloqueado->close();

// Verificar si el alumno ya está unido al cuento
$union_sql = "SELECT 1 FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?";
$stmt_union = $conn->prepare($union_sql);
$stmt_union->bind_param("ii", $id_cuento, $id_alumno);
$stmt_union->execute();
$result_union = $stmt_union->get_result();
if ($result_union->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["error" => "Recurso ya existente."]);
    $stmt_union->close();
    $conn->close();
    exit();
}
$stmt_union->close();

// Si no está unido, proceder con la unión
$sql = "CALL UnirseCuento(?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_cuento, $id_alumno);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        "success" => "Te has unido al cuento con éxito.",
        "id_cuento" => $id_cuento,
    ]);

} else {
    echo json_encode(["error" => "Error al unirse al cuento."]);
}

$stmt->close();
$conn->close();
?>
