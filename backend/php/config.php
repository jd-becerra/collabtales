<?php
$servername = "localhost";
$username = "root";
$password = "lalo2003";
$dbname = "cuentosbd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

