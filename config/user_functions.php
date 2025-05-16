<?php
require_once dirname(__DIR__) . '/connection/database.php';

function getConnection() {
    global $conn;
    return $conn;
}

function addUser($data) {
    try {
        $conn = getConnection();
        
        // Enhanced validation with more specific error messages
        $requiredFields = [
            'USER_FNAME' => 'First Name',
            'USER_LNAME' => 'Last Name',
            'USERNAME' => 'Username',
            'PASSWORD' => 'Password',
            'USER_TYPE' => 'User Type'
        ];
        
        $missingFields = [];
        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missingFields[] = $label;
            }
        }
        
        if (!empty($missingFields)) {
            return [
                'success' => false, 
                'message' => 'Please fill in all required fields: ' . implode(', ', $missingFields)
            ];
        }
        
        // Password length validation
        if (strlen($data['PASSWORD']) < 8 || strlen($data['PASSWORD']) > 15) {
            return [
                'success' => false,
                'message' => 'Password must be between 8 and 15 characters long'
            ];
        }
        
        // Username length validation
        if (strlen($data['USERNAME']) < 5) {
            return [
                'success' => false,
                'message' => 'Username must be at least 5 characters long'
            ];
        }
        
        // Check username using stored procedure
        $stmt = $conn->prepare("CALL sp_check_username(?)");
        $stmt->bind_param("s", $data['USERNAME']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        $stmt->close();
        $conn->next_result();
        
        // Hash password
        $hashedPassword = password_hash($data['PASSWORD'], PASSWORD_DEFAULT);
        
        // Convert user type
        $userType = strtoupper($data['USER_TYPE']);
        if ($userType === 'ADMIN') {
            $userType = 'ADMINISTRATOR';
        }
        
        // Set default active status for new users
        $isActive = 1;
        
        // Add user using stored procedure
        $stmt = $conn->prepare("CALL sp_add_user(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", 
            $data['USER_FNAME'],
            $data['USER_LNAME'],
            $data['USERNAME'],
            $hashedPassword,
            $userType,
            $isActive
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User added successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to add user: ' . $stmt->error];
        }
    } catch (Exception $e) {
        error_log("Error adding user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function updateUser($data) {
    try {
        $conn = getConnection();
        
        // Strict validation - ensure data is properly set
        if (!is_array($data)) {
            return ['success' => false, 'message' => 'Invalid data format'];
        }
        
        // Enhanced validation with data trimming
        $requiredFields = [
            'USER_ID' => 'User ID',
            'USER_FNAME' => 'First Name',
            'USER_LNAME' => 'Last Name',
            'USERNAME' => 'Username',
            'USER_TYPE' => 'User Type'
        ];
        
        // Trim and validate all string inputs
        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field])) {
                return ['success' => false, 'message' => "$label is required"];
            }
            
            if (is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
                if ($data[$field] === '') {
                    return ['success' => false, 'message' => "$label cannot be empty"];
                }
                
                // Additional validation for username
                if ($field === 'USERNAME' && strlen($data[$field]) < 5) {
                    return ['success' => false, 'message' => 'Username must be at least 5 characters long'];
                }
            }
        }
        
        // Additional validation for username length
        if (strlen($data['USERNAME']) < 5) {
            return [
                'success' => false,
                'message' => 'Username must be at least 5 characters long'
            ];
        }
        
        // Username format validation
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['USERNAME'])) {
            return [
                'success' => false,
                'message' => 'Username can only contain letters, numbers, underscores, and hyphens'
            ];
        }
        
        // Additional validation for user type
        if (!in_array(strtoupper($data['USER_TYPE']), ['ADMIN', 'STAFF'])) {
            return [
                'success' => false,
                'message' => 'Invalid user type. Must be either Admin or Staff'
            ];
        }
        
        // Validate new password if being changed
        if (!empty($data['newPassword'])) {
            if (strlen($data['newPassword']) < 8 || strlen($data['newPassword']) > 15) {
                return [
                    'success' => false,
                    'message' => 'New password must be between 8 and 15 characters long'
                ];
            }
        }
        
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
        $stmt->close();
        $conn->next_result();
        
        // Convert user type
        $userType = strtoupper($data['USER_TYPE']);
        if ($userType === 'ADMIN') {
            $userType = 'ADMINISTRATOR';
        }
        
        // Handle password update
        $password = '';
        if (!empty($data['newPassword'])) {
            $password = password_hash($data['newPassword'], PASSWORD_DEFAULT);
        }
        
        // Set active status
        $isActive = isset($data['IS_ACTIVE']) ? (int)$data['IS_ACTIVE'] : 0;
        
        // Update user using stored procedure
        $stmt = $conn->prepare("CALL sp_UpsertUser(?, ?, ?, ?, ?, ?, ?)");
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
            return ['success' => false, 'message' => 'Failed to update user: ' . $stmt->error];
        }
    } catch (Exception $e) {
        error_log("Error updating user: " . $e->getMessage());
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