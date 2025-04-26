<?php
require_once dirname(__DIR__) . '/connection/database.php';

function getConnection() {
    global $conn;
    return $conn;
}

function addUser($data) {
    try {
        $conn = getConnection();
        
        error_log("Adding user - Password length: " . strlen($data['PASSWORD']));
        error_log("User type received: " . $data['USER_TYPE']);
        
        // Check username using stored procedure
        $stmt = $conn->prepare("CALL sp_check_username(?)");
        $stmt->bind_param("s", $data['USERNAME']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        $stmt->close();
        
        // Convert user type
        $userType = strtoupper($data['USER_TYPE']);
        if ($userType === 'ADMIN') {
            $userType = 'ADMINISTRATOR';
        }else if ($userType === 'STAFF') {
            $userType = 'STAFF';
        }
        
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        error_log("Final user type being set: " . $userType);
        
        // Add user using stored procedure
        $stmt = $conn->prepare("CALL sp_add_user(?, ?, ?, ?, ?, ?)");
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

function updateUserProfile($data) {
    try {
        $conn = getConnection();
        
        // First verify the current password if attempting to change password
        if (!empty($data['currentPassword'])) {
            $stmt = $conn->prepare("SELECT PASSWORD FROM user WHERE USER_ID = ?");
            $stmt->bind_param("i", $data['USER_ID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!password_verify($data['currentPassword'], $user['PASSWORD'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
        }
        
        // Build update query
        $sql = "UPDATE user SET USER_FNAME = ?, USER_LNAME = ?";
        $params = [$data['USER_FNAME'], $data['USER_LNAME']];
        $types = "ss";
        
        // Add password to update if provided
        if (!empty($data['PASSWORD'])) {
            $sql .= ", PASSWORD = ?";
            $hashedPassword = password_hash($data['PASSWORD'], PASSWORD_DEFAULT);
            $params[] = $hashedPassword;
            $types .= "s";
        }
        
        $sql .= " WHERE USER_ID = ?";
        $params[] = $data['USER_ID'];
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    } catch (Exception $e) {
        error_log("Error updating user profile: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function getUserProfile($userId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT USER_FNAME, USER_LNAME, USERNAME, USER_TYPE FROM user WHERE USER_ID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting user profile: " . $e->getMessage());
        return false;
    }
}

function getAllUsers() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_GetUsers()");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting users: " . $e->getMessage());
        return false;
    }
}


