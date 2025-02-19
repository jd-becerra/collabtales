<?php
// Load environment variables (optional, if needed)
$frontend_url = $_ENV['FRONTEND_URL'] ?? '*';

// Apply CORS headers
header("Access-Control-Allow-Origin: $frontend_url");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    echo json_decode(['message' => 'Cant connect to $frontend_url']);
    exit();
}
?>