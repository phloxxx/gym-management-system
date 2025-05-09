<?php
$servername = 'localhost';
$dbname = 'gymaster';
$username = 'root';
$password = '';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ini_set('display_errors', 0);
error_reporting(0);
?>