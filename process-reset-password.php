<?php
session_start();
require_once './config/db_functions.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$step = $_POST['step'] ?? '1';

if ($step === '1') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    // Debug received values
    error_log("Attempting password reset - Username: $username, UserID: $userId, Role: $role");

    if (!$username || !$userId || !$role) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Convert user_id to integer since database id is typically INT
    $userId = intval($userId);

    // Convert role to match database enum format
    $userType = strtoupper($role);
    if ($userType === 'ADMIN') {
        $userType = 'ADMINISTRATOR';
    }

    // Add debug logging for role comparison
    error_log("Role comparison - Input role: $role, Converted role: $userType");

    // Debug received values with types
    error_log("Input values - Username: $username (" . gettype($username) . ")");
    error_log("UserID: $userId (" . gettype($userId) . ")");
    error_log("Role: $userType (" . gettype($userType) . ")");

    $stmt = $conn->prepare("CALL sp_GetUsers()");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $result = $stmt->get_result();
    $found_user = null;
    
    // Find matching user with detailed logging
    while ($row = $result->fetch_assoc()) {
        error_log("Comparing with DB record - ID: {$row['USER_ID']}, Username: {$row['USERNAME']}, Type: {$row['USER_TYPE']}");
        
        // Debug comparison values
        $idMatch = ($row['USER_ID'] == $userId);
        $usernameMatch = (strtolower($row['USERNAME']) === strtolower($username));
        $typeMatch = (strtoupper($row['USER_TYPE']) === $userType);
        
        error_log("Matches - ID: " . ($idMatch ? 'YES' : 'NO') . 
                 ", Username: " . ($usernameMatch ? 'YES' : 'NO') . 
                 ", Type: " . ($typeMatch ? 'YES' : 'NO'));

        if ($idMatch && $usernameMatch && $typeMatch) {
            $found_user = $row;
            error_log("User found! User data: " . print_r($row, true));
            break;
        }
    }

    if (!$found_user) {
        error_log("No matching user found for - ID: $userId, Username: $username, Type: $userType");
        echo json_encode([
            'success' => false, 
            'message' => 'Unable to verify identity. Please check your User ID, Username, and Role.'
        ]);
        exit;
    }

    // Remove the IS_ACTIVE check since it's not in your database
    
    // Store validated user info in session for step 2
    $_SESSION['reset_password'] = [
        'username' => $username,
        'user_id' => $userId,
        'role' => $role,
        'verified' => true
    ];

    echo json_encode(['success' => true, 'step' => 2]);

} elseif ($step === '2') {
    error_log("Starting step 2 verification process");
    
    // Verify session exists
    if (!isset($_SESSION['reset_password'])) {
        error_log("No reset password session found");
        echo json_encode(['success' => false, 'message' => 'Session expired. Please try again.']);
        exit;
    }

    // Get verification code directly from POST
    $verificationCode = $_POST['verification_code'] ?? '';
    error_log("Verification code received: " . $verificationCode);

    // Check verification code - using static code for demo
    if ($verificationCode !== "123456") {
        error_log("Invalid verification code: " . $verificationCode);
        echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
        exit;
    }

    try {
        // Get user data from session
        $userId = $_SESSION['reset_password']['user_id'];
        $username = $_SESSION['reset_password']['username'];
        $userType = strtoupper($_SESSION['reset_password']['role']);
        if ($userType === 'ADMIN') {
            $userType = 'ADMINISTRATOR';
        }

        // Get existing user data from database
        $stmt = $conn->prepare("CALL sp_GetUsers()");
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = null;
        
        while ($row = $result->fetch_assoc()) {
            if ($row['USER_ID'] == $userId && 
                strtolower($row['USERNAME']) === strtolower($username) &&
                $row['USER_TYPE'] === $userType) {
                $userData = $row;
                break;
            }
        }
        $stmt->close();

        if (!$userData) {
            throw new Exception("User not found");
        }

        // Generate new password and hash it
        $tempPassword = generateTempPassword();
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        // Log hash details for debugging
        error_log("Reset - New temp password (plain): " . $tempPassword);
        error_log("Reset - Hash length: " . strlen($hashedPassword));
        error_log("Reset - Full hash: " . $hashedPassword);

        // Call sp_UpsertUser to update the password
        $stmt = $conn->prepare("CALL sp_UpsertUser(?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("isssss", 
            $userId,
            $userData['USER_FNAME'],
            $userData['USER_LNAME'],
            $userData['USERNAME'],
            $hashedPassword,
            $userType
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // Success - clear session and return temp password
        unset($_SESSION['reset_password']);
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successful! Your temporary password is: ' . $tempPassword,
            'temp_password' => $tempPassword
        ]);
        exit;

    } catch (Exception $e) {
        error_log("Password reset failed: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reset password: ' . $e->getMessage()
        ]);
        exit;
    }
}

function generateTempPassword($length = 8) {
    // Simplified character set for more readable passwords
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

$conn->close();
?>