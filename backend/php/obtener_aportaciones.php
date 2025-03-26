<?php
include('cors_headers.php');
include('config.php');

$id_cuento = $_GET['id_cuento'];
$id_alumno = $_GET['id_alumno'];

if (empty($id_cuento)) {
    echo json_encode(["error" => "id_cuento es obligatorio"]);
    exit;
}

// Ejecutar la primera consulta almacenada (procedimiento almacenado)
$sql = $conn->prepare("CALL ListarCuentoAportacionesConAlumnos(?)");
$sql->bind_param("i", $id_cuento);
$sql->execute();
$result = $sql->get_result();

// Almacenar los resultados en un array
$aportaciones = [];
while ($row = $result->fetch_assoc()) {
    $aportaciones[] = $row;
}

// Cerrar el statement para evitar el error de comandos fuera de sincronización
$sql->close();

// Obtener la id_aportacion del alumno
$sql = $conn->prepare("SELECT id_aportacion FROM Aportacion WHERE fk_cuento = ? AND fk_alumno = ?");
$sql->bind_param("ii", $id_cuento, $id_alumno);
$sql->execute();
$result = $sql->get_result();
$id_aportacion = $result->fetch_assoc()['id_aportacion'] ?? null;

// Cerrar el statement
$sql->close();

// Retornar JSON con los datos
echo json_encode([
    "aportaciones" => $aportaciones,
    "id_aportacion_alumno" => $id_aportacion
]);

$conn->close();
?>
