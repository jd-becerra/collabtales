<?php
include('cors_headers.php');
include('validate_method.php');
validate_method("PUT");
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$_PUT = json_decode(file_get_contents("php://input"), true);

// Si hay más de 2 parámetros
if (count($_PUT) > 2) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

$id_alumno = $user['id_alumno'] ?? null;

$nombre = $_PUT['nombre'] ?? null;
$correo = $_PUT['correo'] ?? null;

// Sanitizar
$nombre = isset($nombre) ? trim(htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8')) : null;
$correo = isset($correo) ? trim(htmlspecialchars($correo, ENT_QUOTES, 'UTF-8')) : null;

// Se debe enviar al menos un campo a modificar
$hasNombre = $nombre !== null && $nombre !== '';
$hasCorreo = $correo !== null && $correo !== '';
if (empty($id_alumno) || (!$hasNombre && !$hasCorreo)) {
    http_response_code(400);
    echo json_encode(["error" => "Se requiere al menos nombre o correo no vacíos."]);
    exit;
}

if ($nombre !== null && strlen($nombre) > 50) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit;
}

if ($correo !== null && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit;
}

// Llamar al procedimiento almacenado
$stmt = $conn->prepare("CALL EditarAlumno(?, ?, ?)");
$stmt->bind_param("iss", $id_alumno, $nombre, $correo);

try {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $msg = $result->fetch_assoc();
        http_response_code(200);
        echo json_encode(["success" => 'Datos actualizados correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error de servidor"]);
    }
} catch (mysqli_sql_exception $e) {
    $message = $e->getMessage();

    if (str_contains($message, 'No se detectaron cambios.')) {
        // No queremos interrumpir el flujo si no se detectaron cambios
        http_response_code(200);
        echo json_encode(['success' => 'Sin cambios detectados']);
    } elseif (str_contains($message, 'El nombre de usuario ya está en uso.')) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'Nombre duplicado']);
    } elseif (str_contains($message, 'El correo electrónico ya está en uso.')) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'Correo duplicado']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => false, 'message' => 'Error en el servidor.', 'error' => $message]);
    }
}

$stmt->close();
$conn->close();
?>
