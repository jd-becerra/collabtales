<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

// Si hay más de 1 parámetro
if (count($_GET) !== 2 || !isset($_GET['id_aportacion']) || !isset($_GET['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

if (!ctype_digit($_GET['id_aportacion']) || !ctype_digit($_GET['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

// Convertir a entero
$id_aportacion = (int) $_GET['id_aportacion'];
$id_cuento = (int) $_GET['id_cuento'];

// Verificar que el usuario no esté bloqueado
$sql_bloqueado = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_bloqueado->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_bloqueado->execute();
$sql_bloqueado->store_result();
if ($sql_bloqueado->num_rows > 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar si el usuario es colaborador del cuento
$sql_colaborador = $conn->prepare("SELECT fk_alumno FROM Relacion_Alumno_Cuento WHERE fk_cuento = ? AND fk_alumno = ?");
$sql_colaborador->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_colaborador->execute();
$colaborador_result = $sql_colaborador->get_result();
if ($colaborador_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar si la aportación pertenece al cuento
$sql_aportacion = $conn->prepare("SELECT id_aportacion FROM Aportacion WHERE id_aportacion = ? AND fk_cuento = ?");
$sql_aportacion->bind_param("ii", $id_aportacion, $id_cuento);
$sql_aportacion->execute();
$sql_aportacion->store_result();
if ($sql_aportacion->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado"]);
    exit;
}

// Verificar que la aportación sea del colaborador
$sql_colaborador_aportacion = $conn->prepare("SELECT fk_alumno FROM Aportacion WHERE id_aportacion = ? AND fk_alumno = ?");
$sql_colaborador_aportacion->bind_param("ii", $id_aportacion, $user['id_alumno']);
$sql_colaborador_aportacion->execute();
$sql_colaborador_aportacion->store_result();
if ($sql_colaborador_aportacion->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Si llegamos aquí, el usuario es colaborador y la aportación es válida
// Obtenemos las aportaciones del cuento (para visualización)
$sql_aportaciones = $conn->prepare("
    SELECT 
        contenido, 
        IF(fk_alumno = ?, 1, 0) AS es_autor
    FROM Aportacion 
    WHERE fk_cuento = ?
");
$sql_aportaciones->bind_param("ii", $user['id_alumno'], $id_cuento);
$sql_aportaciones->execute();
$sql_aportaciones->store_result();
if ($sql_aportaciones->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado"]);
    exit;
}

// Ahora checamos si el usuario si es creador del cuento o no
$sql_es_creador = $conn->prepare("
    SELECT IF(fk_owner = ?, 1, 0) AS es_creador
    FROM Cuento 
    WHERE id_cuento = ?
");
$sql_es_creador->bind_param("ii", $user['id_alumno'], $id_cuento);
$sql_es_creador->execute();
$sql_es_creador->store_result();
$sql_es_creador->bind_result($es_creador);
$sql_es_creador->fetch();
$sql_aportaciones->bind_result($contenido, $es_autor);
$aportaciones = [];
$contenido_autor = null;
while ($sql_aportaciones->fetch()) {
    $aportaciones[] = [
        "contenido" => $contenido,
        "es_autor" => (bool) $es_autor
    ];

    if ($es_autor) {
        $contenido_autor = $contenido; // Guardamos el contenido del autor
    }
}
// Preparar la respuesta
$response = [
    "aportaciones" => $aportaciones,
    "es_creador" => (bool) $es_creador,
    "contenido_autor" => $contenido_autor
];
http_response_code(200);
echo json_encode($response);

// Cerrar las conexiones
$sql_bloqueado->close();
$sql_colaborador->close();
$sql_aportacion->close();
$sql_colaborador_aportacion->close();
$sql_aportaciones->close();
$sql_es_creador->close();

$conn->close();
?>
