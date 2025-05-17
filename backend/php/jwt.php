<?php
declare(strict_types=1);
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once('../vendor/autoload.php');
include_once('load_env.php');

$jwt_secret = getenv("JWT_SECRET");
$jwt_expires_in = getenv("JWT_EXPIRES_IN");
if (!$jwt_secret || !$jwt_expires_in) {
    die(json_encode(["error" => "Unauthorized"]));
}

// Generar JWT token
function generate_jwt(array $payload): string {
    global $jwt_secret, $jwt_expires_in;
    $issuedAt = time();
    $expirationTime = $issuedAt + (int)$jwt_expires_in;

    $token = array_merge($payload, [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
    ]);

    return JWT::encode($token, $jwt_secret, 'HS256');
}

// Validar JWT token
function validate_jwt(string $jwt): ?array {
    global $jwt_secret;

    try {
        return (array) JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}
?>
