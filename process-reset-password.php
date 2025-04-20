<?php
session_start();
require_once 'config/db_functions.php';

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

    // Update query to match your table name and column names
    $sql = "SELECT USER_ID, USERNAME, USER_TYPE, IS_ACTIVE FROM user WHERE USERNAME = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $stmt->bind_param("s", $username);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Debug found user data before comparison
    error_log("Found user data: " . print_r($user, true));
    error_log("DB User Type: {$user['USER_TYPE']}, Converted User Type: $userType");
    error_log("DB User ID: {$user['USER_ID']}, Input User ID: $userId");

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Update condition checks with proper role comparison
    if ($user['USER_ID'] != $userId || $user['USER_TYPE'] != $userType || $user['IS_ACTIVE'] != 1) {
        error_log("Verification failed:");
        error_log("ID match: " . ($user['USER_ID'] == $userId ? 'true' : 'false'));
        error_log("Role match: " . ($user['USER_TYPE'] == $userType ? 'true' : 'false'));
        error_log("Active: " . ($user['IS_ACTIVE'] == 1 ? 'true' : 'false'));
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // Store validated user info in session for step 2
    $_SESSION['reset_password'] = [
        'username' => $username,
        'user_id' => $userId,
        'role' => $role,
        'verified' => true
    ];

    echo json_encode(['success' => true, 'step' => 2]);

} elseif ($step === '2') {
    if (!isset($_SESSION['reset_password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid session']);
        exit;
    }

    $verificationCode = filter_input(INPUT_POST, 'verification_code', FILTER_SANITIZE_STRING);

    // For demo purposes, we're using a static verification code
    if ($verificationCode !== "123456") {
        echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
        exit;
    }

    // Generate temporary password
    $tempPassword = generateTempPassword();
    $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Update the table name and column names in the UPDATE query
    $sql = "UPDATE user SET PASSWORD = ? WHERE USERNAME = ? AND USER_ID = ? AND USER_TYPE = ?";
    $stmt = $conn->prepare($sql);

    // Convert role to match your enum format
    $userType = strtoupper($_SESSION['reset_password']['role']);
    if ($userType === 'ADMIN') {
        $userType = 'ADMINISTRATOR';
    }

    $stmt->bind_param("ssis", 
        $hashedPassword,
        $_SESSION['reset_password']['username'],
        $_SESSION['reset_password']['user_id'],
        $userType
    );

    if ($stmt->execute()) {
        // Clear reset password session
        unset($_SESSION['reset_password']);
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successful',
            'temp_password' => $tempPassword
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reset password'
        ]);
    }
}

function generateTempPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

$conn->close();
?>
