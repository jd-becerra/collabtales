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
if (!ctype_digit($id_cuento)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}
$id_cuento = (int) $id_cuento;

// Verificar si el usuario está bloqueado
$sql_bloqueado = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_bloqueado->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_bloqueado->execute();
$sql_bloqueado->store_result();
if ($sql_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar si el usuario es parte del cuento
$sql_colaborador = $conn->prepare("SELECT fk_alumno FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_colaborador->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_colaborador->execute();
$result = $sql_colaborador->get_result();
if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Si todas las comprobaciones son correctas, obtenemos el cuento y sus aportaciones
// Primero, obtenemos los datos del cuento
$sql_cuento = $conn->prepare("
    SELECT 
        c.nombre, 
        c.descripcion,
        GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores,
        COUNT(DISTINCT lk.fk_alumno) AS likes,
    FROM Cuento c
    JOIN Relacion_Alumno_Cuento rac ON c.id_cuento = rac.fk_cuento
    JOIN Alumno a ON rac.fk_alumno = a.id_alumno
    LEFT JOIN Likes lk ON c.id_cuento = lk.fk_cuento
    WHERE c.id_cuento = ?
    GROUP BY c.id_cuento, c.nombre, c.descripcion
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
// Convertir autores a array
$cuento['autores'] = $cuento['autores'] ? explode(', ', $cuento['autores']) : [];

// Ahora obtenemos las aportaciones del cuento
$sql_aportaciones = $conn->prepare("
    SELECT contenido FROM Aportacion 
    WHERE fk_cuento = ?
");
$sql_aportaciones->bind_param("i", $id_cuento);
$sql_aportaciones->execute();
$result_aportaciones = $sql_aportaciones->get_result();
$aportaciones = [];
while ($row_aportacion = $result_aportaciones->fetch_assoc()) {
    $aportaciones[] = $row_aportacion;
}

// Cerrar los statements
$sql_colaborador->close();
$sql_bloqueado->close();
$sql_cuento->close();
$sql_aportaciones->close();

// Retornar JSON con los datos del cuento y sus aportaciones
http_response_code(200);
echo json_encode([
    "cuento" => $cuento,
    "aportaciones" => $aportaciones
]);

$conn->close();
?>