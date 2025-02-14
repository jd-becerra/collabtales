<?php
$servername = "localhost";
$username = "db";
$password = "1234";
$dbname = "cuentosBD";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

