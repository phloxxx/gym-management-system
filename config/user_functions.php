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
        
        // Check username using stored procedure
        $stmt = $conn->prepare("CALL sp_check_username(?)");
        $stmt->bind_param("s", $data['USERNAME']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['USER_ID'] != $data['USER_ID']) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
        }
        
        // Convert user type
        $userType = strtoupper($data['USER_TYPE']);
        if ($userType === 'ADMIN') $userType = 'ADMINISTRATOR';
        
        $stmt = $conn->prepare("CALL sp_UpsertUser(?, ?, ?, ?, ?, ?, ?)");
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $password = !empty($data['PASSWORD']) ? 
                   password_hash($data['PASSWORD'], PASSWORD_DEFAULT) : 
                   '';
        
        $stmt->bind_param("isssssi", 
            $data['USER_ID'],
            $data['USER_FNAME'],
            $data['USER_LNAME'],
            $data['USERNAME'],
            $password,
            $userType,
            $isActive
        );
        
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
        
        // Get user info using stored procedure
        $stmt = $conn->prepare("CALL sp_GetUsers()");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $user = array_filter($users, function($u) use ($data) {
            return $u['USER_ID'] == $data['USER_ID'];
        });
        $user = reset($user);
        
        if (!empty($data['currentPassword']) && 
            !password_verify($data['currentPassword'], $user['PASSWORD'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Use sp_UpsertUser for profile update
        $stmt = $conn->prepare("CALL sp_UpsertUser(?, ?, ?, ?, ?, ?, ?)");
        $password = !empty($data['PASSWORD']) ? 
                   password_hash($data['PASSWORD'], PASSWORD_DEFAULT) : 
                   $user['PASSWORD'];
        
        $stmt->bind_param("isssssi", 
            $data['USER_ID'],
            $data['USER_FNAME'],
            $data['USER_LNAME'],
            $user['USERNAME'],  // Keep existing username
            $password,
            $user['USER_TYPE'], // Keep existing user type
            $user['IS_ACTIVE']  // Keep existing active status
        );
        
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
        $stmt = $conn->prepare("CALL sp_GetUsers()");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        return array_filter($users, function($user) use ($userId) {
            return $user['USER_ID'] == $userId;
        })[0] ?? false;
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


