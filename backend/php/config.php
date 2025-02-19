<?php
// Define function first
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($name, $value) = explode("=", $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"");
        putenv("$name=$value");
    }
}

// Load .env variables
loadEnv(__DIR__ . '/../../.env');

// Get DB credentials
$servername = getenv("DB_HOST") ?: "localhost";
$username = getenv("DB_USER") ?: "dbuser";
$password = getenv("DB_PASS");
$dbname = getenv("DB_NAME") ?: "cuentosBD";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
?>