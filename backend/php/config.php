<?php
include_once('load_env.php');

// Get DB credentials
$db_servername = getenv("DB_HOST") ?: "localhost";
$db_username = getenv("DB_USER") ?: "dbuser";
$db_password = getenv("DB_PASS");
$db_name = getenv("DB_NAME") ?: "cuentosBD";
$db_port = "3306";

// Connect to database
$conn = new mysqli($db_servername, $db_username, $db_password, $db_name, $db_port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
?>