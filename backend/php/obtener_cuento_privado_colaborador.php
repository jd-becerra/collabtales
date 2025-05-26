<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Sólo se puede mandar la id del cuento
if (count($_GET) !== 1) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}
$id_cuento = $_GET['id_cuento'] ?? null;
// Comprobar que el id_cuento no es nulo y que es un número entero
if (!isset($_GET['id_cuento']) || !ctype_digit($_GET['id_cuento'])){
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}
$id_cuento = (int) $id_cuento;
$id_alumno = $user['id_alumno'];

// Verificar si el usuario está bloqueado
$sql_bloqueado = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_bloqueado->bind_param("ii", $id_cuento, $id_alumno);
$sql_bloqueado->execute();
$sql_bloqueado->store_result();
if ($sql_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar si el usuario es colaborador del cuento
$sql_colaborador = $conn->prepare("SELECT fk_alumno FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_colaborador->bind_param("ii", $id_cuento, $id_alumno);
$sql_colaborador->execute();
$colaborador_result = $sql_colaborador->get_result();
if ($colaborador_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Obtener los datos del cuento
$sql_cuento = $conn->prepare("
    SELECT 
        c.nombre, 
        c.descripcion
    FROM Cuento c
    WHERE c.id_cuento = ?
");
$sql_cuento->bind_param("i", $id_cuento);
$sql_cuento->execute();
$result = $sql_cuento->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado."]);
    exit;
}
$cuento = $result->fetch_assoc();

// Ahora obtenemos las aportaciones del cuento
$sql_aportaciones = $conn->prepare("
    SELECT
        al.nombre AS autor,
        ap.contenido AS contenido,
        IF(ap.fk_alumno = ?, 1, 0) AS es_autor
    FROM Aportacion ap
    JOIN Alumno al ON ap.fk_alumno = al.id_alumno
    WHERE fk_cuento = ?
");
$sql_aportaciones->bind_param("ii", $id_alumno, $id_cuento);
$sql_aportaciones->execute();
$result_aportaciones = $sql_aportaciones->get_result();
$aportaciones = [];
while ($row_aportacion = $result_aportaciones->fetch_assoc()) {
    $aportaciones[] = [
        "autor" => $row_aportacion['autor'],
        "contenido" => $row_aportacion['contenido'],
        "es_autor" => (bool)$row_aportacion['es_autor']
    ];
}
// Si no hay aportaciones, retornar un array vacío
if (empty($aportaciones)) {
    http_response_code(404);
    echo json_encode(["error" => "No se han encontrado aportaciones para este cuento"]);
    exit;
}

// Por último, obtener la id_aportacion del usuario
$sql_id_aportacion = $conn->prepare("
    SELECT id_aportacion
    FROM Aportacion 
    WHERE fk_cuento = ? AND fk_alumno = ?
");
$sql_id_aportacion->bind_param("ii", $id_cuento, $id_alumno);
$sql_id_aportacion->execute();
$result_id_aportacion = $sql_id_aportacion->get_result();
if ($result_id_aportacion->num_rows > 0) {
    $row_id_aportacion = $result_id_aportacion->fetch_assoc();
    $id_aportacion = $row_id_aportacion['id_aportacion'];
} else {
    http_response_code(404);
    echo json_encode(["error" => "No se ha encontrado una aportación"]);
    exit;
}

// Cerrar los statements
$sql_bloqueado->close();
$sql_colaborador->close();
$sql_cuento->close();
$sql_aportaciones->close();
$sql_id_aportacion->close();

// Retornar JSON con los datos del cuento y sus aportaciones
http_response_code(200);
echo json_encode([
    "cuento" => $cuento,
    "aportaciones" => $aportaciones,
    "id_aportacion" => $id_aportacion
]);

$conn->close();
?>