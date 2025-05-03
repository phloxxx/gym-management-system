<?php
/**
 * Database connection function
 * This file provides a consistent way to connect to the database
 */

// Function to get a database connection
function getConnection() {
    // Database configuration
    $host = 'localhost';
    $username = 'root'; // Default XAMPP username
    $password = '';     // Default XAMPP password
    $database = 'gymaster';

    // Create connection
    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to ensure proper handling of special characters
    $conn->set_charset('utf8mb4');

    return $conn;
}

// Function to close a database connection safely
function closeConnection($conn) {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}