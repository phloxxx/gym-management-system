<?php
// Database connection parameters
$host = 'localhost';  // Database host (typically localhost for XAMPP)
$username = 'root';   // Default XAMPP username
$password = '';       // Default XAMPP password (blank)
$database = 'gymaster'; // Database name as defined in your SQL file

// Create a connection function to reuse throughout the application
function getConnection() {
    global $host, $username, $password, $database;
    
    // Create a new mysqli connection
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set character set to utf8mb4 for proper handling of special characters
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Alternative PDO connection option
function getPDOConnection() {
    global $host, $username, $password, $database;
    
    try {
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Example of getting an active connection:
// $conn = getConnection();
// 
// After using the connection, close it:
// $conn->close();

// Example of using PDO connection:
// $pdo = getPDOConnection();
// 
// PDO connections close automatically when the variable goes out of scope
?>
