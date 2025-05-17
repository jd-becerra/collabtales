    <?php
    include_once('jwt.php');

    function authenticate() {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["error" => "Token inválido"]);
            exit;
        }

        $authHeader = $headers['Authorization'];
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["error" => "Token inválido"]);
            exit;
        }

        $token = $matches[1];
        $decoded = validate_jwt($token);

        if (!$decoded) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["error" => "Token inválido"]);
            exit;
        }

        return $decoded;
    }


    ?>