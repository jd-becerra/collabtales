<?php
include('cors_headers.php');
include('config.php');

$id_aportacion = $_GET['id_aportacion'] ?? null;

if (!$id_aportacion) {
    echo json_encode(["error" => "id_aportacion es obligatorio"]);
    exit;
}

// Ejecutar consulta
$sql = $conn->prepare("SELECT contenido FROM Aportacion WHERE id_aportacion = ?");
$sql->bind_param("i", $id_aportacion);
$sql->execute();
$sql->store_result();

// Verifica si hay resultados
if ($sql->num_rows > 0) {
    $sql->bind_result($contenido);
    $sql->fetch();  
    echo json_encode(["contenido" => $contenido]);
} else {
    echo json_encode(["error" => "Aportación no encontrada"]);
}

$sql->close();
$conn->close();
?>
