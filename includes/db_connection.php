<?php
// Database connection parameters
$host = "localhost";
$db_name = "gymaster";
$username = "root";
$password = "";
$charset = "utf8mb4";

try {
    // Create connection using PDO
    $conn = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=$charset",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch(PDOException $e) {
    // Log error (don't expose details to users)
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>
