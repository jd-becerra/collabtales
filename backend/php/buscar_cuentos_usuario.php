<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('config.php');

// No permitir más de 100 peticiones por minuto
include("rate_limit.php");
$ip = $_SERVER['REMOTE_ADDR'];
$endpoint_name = "buscar_cuentos_globales";
$limit = 100;
$interval_seconds = 60;

if (is_rate_limited($conn, $endpoint_name, $ip, $limit, $interval_seconds)) {
    http_response_code(429);
    echo json_encode(["error" => "Demasiadas peticiones. Intenta de nuevo más tarde."]);
    exit;
}

include("jwt_auth.php");
$user = authenticate();

if (!isset($_GET['busqueda']) || count($_GET) !== 1) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

$busqueda = trim($_GET['busqueda']);
if (empty($busqueda)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

if (strlen($busqueda) > 100) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

// Sanitizar para evitar HTML inyectado, pero no romper el LIKE con símbolos especiales
$busqueda_safe = htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8');

$id_alumno = filter_var($user['id_alumno'], FILTER_VALIDATE_INT);
if ($id_alumno === false) {
    http_response_code(400);
    echo json_encode(["error" => "Sesión inválida"]);
    exit;
}

// Preparamos la consulta
$stmt = $conn->prepare("
    SELECT 
        c.id_cuento, 
        c.nombre, 
        c.descripcion, 
        GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
    FROM Cuento c
    JOIN Relacion_Alumno_Cuento rac ON c.id_cuento = rac.fk_cuento
    JOIN Alumno a ON rac.fk_alumno = a.id_alumno
    WHERE c.publicado = 1 
        AND (c.nombre LIKE ? OR c.descripcion LIKE ?)
        AND rac.fk_alumno = ?
    GROUP BY c.id_cuento, c.nombre, c.descripcion
");
$like_param = "%$busqueda_safe%";
$stmt->bind_param("ssi", $like_param, $like_param, $id_alumno);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $row['autores'] = $row['autores'] ? explode(', ', $row['autores']) : [];
    $rows[] = $row;
}

echo json_encode($rows);

$stmt->close();
$conn->close();
?>
