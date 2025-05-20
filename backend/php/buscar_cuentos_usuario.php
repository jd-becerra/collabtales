<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("GET");
include('config.php');

// No permitir más de 100 peticiones por minuto
include("rate_limit.php");
$ip = $_SERVER['REMOTE_ADDR'];
$endpoint_name = "buscar_cuentos_globales";
$limit = 100; // 100 peticiones
$interval_seconds = 60; // 1 minuto
if (is_rate_limited($conn, $endpoint_name, $ip, $limit, $interval_seconds)) {
    http_response_code(429);
    echo json_encode(["error" => "Demasiadas peticiones. Intenta de nuevo más tarde."]);
    exit;
}

include("jwt_auth.php");
$user = authenticate();

$id_alumno = $user['id_alumno'];
if (empty($_GET['busqueda']) || empty($id_alumno)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan parámetros"]);
    exit;
}

// Sanitizamos la búsqueda
$busqueda = trim(htmlspecialchars($_GET['busqueda']));
if (strlen($busqueda) > 100) {
    http_response_code(400);
    echo json_encode(["error" => "Parametro excede el tamaño permitido"]);
    exit;
}
$id_alumno = filter_var($id_alumno, FILTER_VALIDATE_INT);
if ($id_alumno === false) {
    http_response_code(400);
    echo json_encode(["error" => "Alumno inválido"]);
    exit;
}

// Preparamos la consulta (regresar los cuentos públicos que contengan la búsqueda en el nombre o descripción, además de los autores)
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
$busqueda = "%$busqueda%"; // Agregamos los comodines para LIKE
$stmt->bind_param("ssi", $busqueda, $busqueda, $id_alumno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);
} else {
    echo json_encode([]);
}
$stmt->close();
$conn->close();
?>