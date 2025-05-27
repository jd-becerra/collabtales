<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

if (count($_GET) !== 1 || !isset($_GET['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

if (!ctype_digit($_GET['id_cuento'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

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

// Verificar si el usuario es creador del cuento
$sql_creador = $conn->prepare("SELECT fk_owner FROM Cuento WHERE id_cuento = ? AND fk_owner = ?");
$sql_creador->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_creador->execute();
$sql_creador->store_result();
if ($sql_creador->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permiso para acceder a este recurso."]);
    exit;
}

// Verificar que el cuento existe
// Obtener datos del cuento (nombre, codigo_compartir, admite_colaboradores)
$sql_cuento = $conn->prepare("SELECT nombre, codigo_compartir, admite_colaboradores FROM Cuento WHERE id_cuento = ?");
$sql_cuento->bind_param("i", $id_cuento);
$sql_cuento->execute();
$sql_cuento->store_result();
if ($sql_cuento->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Recurso no encontrado."]);
    exit;
}
$sql_cuento->bind_result($nombre_cuento, $codigo_compartir, $admite_colaboradores);
$sql_cuento->fetch();
$response_cuento = [
    "nombre" => $nombre_cuento,
    "codigo_compartir" => $codigo_compartir,
    "admite_colaboradores" => $admite_colaboradores
];

// Obtener los colaboradores del cuento
$sql_colaboradores = $conn->prepare("
    SELECT 
        a.id_alumno, 
        a.nombre,
        rac.created_at
    FROM Relacion_Alumno_Cuento rac 
    JOIN Alumno a ON rac.fk_alumno = a.id_alumno 
    WHERE rac.fk_cuento = ?
    AND rac.fk_alumno != ?
");
$sql_colaboradores->bind_param("ii", $id_cuento, $user['id_alumno']);
$sql_colaboradores->execute();
$sql_colaboradores->store_result();
$colaboradores = [];
if ($sql_colaboradores->num_rows > 0) {
    $sql_colaboradores->bind_result($id_alumno, $nombre, $created_at);
    while ($sql_colaboradores->fetch()) {
        $colaboradores[] = [
            "id_alumno" => $id_alumno,
            "nombre" => $nombre,
            "fecha_registro" => $created_at
        ];
    }
}

// Obtener los usuarios bloqueados
$sql_bloqueados = $conn->prepare("
    SELECT 
        a.id_alumno, 
        a.nombre,
        ln.created_at
    FROM ListaNegra ln 
    JOIN Alumno a ON ln.fk_alumno = a.id_alumno 
    WHERE ln.fk_cuento = ?
");
$sql_bloqueados->bind_param("i", $id_cuento);
$sql_bloqueados->execute();
$sql_bloqueados->store_result();
$sql_bloqueados->bind_result($id_alumno, $nombre, $created_at);
$bloqueados = [];
while ($sql_bloqueados->fetch()) {
    $bloqueados[] = [
        "id_alumno" => $id_alumno,
        "nombre" => $nombre,
        "fecha_bloqueo" => $created_at
    ];
}

http_response_code(200);
$response = [
    "colaboradores" => $colaboradores,
    "bloqueados" => $bloqueados,
    "cuento" => $response_cuento
];

echo json_encode($response);
$conn->close();
?>