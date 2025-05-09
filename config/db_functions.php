<?php
require_once dirname(__DIR__) . '/connection/database.php';

function getConnection() {
    try {
        $conn = new mysqli("localhost", "root", "", "gymaster");
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            throw new Exception("Database connection failed");
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log("Error in getConnection: " . $e->getMessage());
        throw $e;
    }
}

function executeQuery($sql, $params = [], $types = '') {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

function fetchAll($sql, $params = [], $types = '') {
    $stmt = executeQuery($sql, $params, $types);
    if ($stmt) {
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return false;
}

function fetchOne($sql, $params = [], $types = '') {
    $stmt = executeQuery($sql, $params, $types);
    if ($stmt) {
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return false;
}
