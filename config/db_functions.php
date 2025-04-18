<?php
require_once 'database.php';

function getConnection() {
    try {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "gymaster";  // make sure this matches your database name
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
        
        // Check for 'user' table instead of 'users'
        $tableCheck = $conn->query("SHOW TABLES LIKE 'user'");
        if ($tableCheck->num_rows == 0) {
            // Create user table if it doesn't exist
            $sql = "CREATE TABLE IF NOT EXISTS `user` (
                `USER_ID` smallint(6) NOT NULL AUTO_INCREMENT,
                `USER_FNAME` varchar(50) NOT NULL,
                `USER_LNAME` varchar(30) NOT NULL,
                `USERNAME` varchar(20) NOT NULL,
                `PASSWORD` varchar(255) NOT NULL,
                `USER_TYPE` enum('ADMINISTRATOR','STAFF') NOT NULL,
                `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`USER_ID`),
                UNIQUE KEY `USERNAME` (`USERNAME`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if (!$conn->query($sql)) {
                throw new Exception("Table creation failed: " . $conn->error);
            }
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        throw $e;
    }
}

function addUser($data) {
    try {
        $conn = getConnection();
        
        // Debug log incoming data
        error_log("Adding user - Password length: " . strlen($data['PASSWORD']));
        error_log("User type received: " . $data['USER_TYPE']); // Add debug log
        
        // Check if username already exists
        $stmt = $conn->prepare("SELECT USER_ID FROM user WHERE USERNAME = ?");
        $stmt->bind_param("s", $data['USERNAME']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        // Insert user with explicit column names
        $stmt = $conn->prepare("INSERT INTO user (USER_FNAME, USER_LNAME, USERNAME, PASSWORD, USER_TYPE, IS_ACTIVE) 
                               VALUES (?, ?, ?, ?, ?, ?)");
                               
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        // Convert user type to match database enum
        $userType = strtoupper($data['USER_TYPE']);
        if ($userType === 'ADMIN') {
            $userType = 'ADMINISTRATOR';
        }else if ($userType === 'STAFF') {
            $userType = 'STAFF';
        } 
        
        error_log("Final user type being set: " . $userType); // Debug log
        
        $stmt->bind_param("sssssi", 
            $data['USER_FNAME'],
            $data['USER_LNAME'],
            $data['USERNAME'],
            $data['PASSWORD'],
            $userType,
            $isActive
        );
        
        $success = $stmt->execute();
        
        if ($success) {
            return ['success' => true, 'message' => 'User added successfully'];
        } else {
            error_log("Database error: " . $stmt->error);
            return ['success' => false, 'message' => 'Failed to add user: ' . $stmt->error];
        }
    } catch (Exception $e) {
        error_log("Error adding user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
    }
}

function updateUser($data) {
    try {
        $conn = getConnection();
        
        // Check if username exists for other users
        $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE USERNAME = ? AND USER_ID != ?");
        $stmt->bind_param("si", $data['USERNAME'], $data['USER_ID']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_row()[0] > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        // Convert user type
        $userType = strtoupper($data['USER_TYPE']);
        if ($userType === 'ADMIN') $userType = 'ADMINISTRATOR';
        
        // Build update query
        $sql = "UPDATE user SET 
                USER_FNAME = ?, 
                USER_LNAME = ?, 
                USERNAME = ?, 
                USER_TYPE = ?, 
                IS_ACTIVE = ?";
        
        // Add password to update if provided
        $params = [
            $data['USER_FNAME'],
            $data['USER_LNAME'],
            $data['USERNAME'],
            $userType,
            isset($data['IS_ACTIVE']) ? 1 : 0
        ];
        $types = "ssssi";
        
        if (!empty($data['PASSWORD'])) {
            $sql .= ", PASSWORD = ?";
            $params[] = password_hash($data['PASSWORD'], PASSWORD_DEFAULT);
            $types .= "s";
        }
        
        $sql .= " WHERE USER_ID = ?";
        $params[] = $data['USER_ID'];
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update user'];
        }
    } catch (Exception $e) {
        error_log("Database error in updateUser: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}


