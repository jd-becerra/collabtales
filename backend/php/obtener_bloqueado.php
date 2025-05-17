<?php
include('cors_headers.php');
include('jwt_auth.php');
$user = authenticate();

include('config.php');

$id_cuento = $_GET['id_cuento'] ?? null;

if (!$id_cuento) {
    echo json_encode(["error" => "id_cuento"]);
    exit;
}

// Ejecutar consulta
$sql = $conn->prepare("SELECT fk_alumno FROM ListaNegra WHERE fk_cuento = ?");
$sql->bind_param("i", $id_cuento);
$sql->execute();
$sql->store_result();

// Verifica si hay resultados
if ($sql->num_rows > 0) {
    $sql->bind_result($contenido);
    $sql->fetch();  
    echo json_encode(["contenido" => $contenido]);
} else {
    echo json_encode(["error" => "Alumnos no encontrados"]);
}

$sql->close();
$conn->close();
?>
