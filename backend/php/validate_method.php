<?php
function validate_method($method) {
    // Verifica si el método de la petición es POST
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        http_response_code(405); // Método no permitido
        echo json_encode(["error" => "Método no permitido para esta ruta."]);
        exit;
    }
}

?>