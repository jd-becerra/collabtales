<?php
// Load environment variables (optional, if needed)
$frontend_url = $_ENV['FRONTEND_URL'] ?? '*';

// Apply CORS headers
header("Access-Control-Allow-Origin: $frontend_url");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_encode(["message" => "Preflight request successful"]);
    http_response_code(200);
    exit();
}
?>