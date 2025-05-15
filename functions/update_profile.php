<?php
session_start();
require_once '../config/db_connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];
$role = strtolower($_SESSION['role'] ?? '');

// Validate role - must match the role specified in the form
$formRole = strtolower($_POST['user_role'] ?? '');
if ($role !== $formRole) {
    echo json_encode(['success' => false, 'message' => 'Role mismatch error']);
    exit;
}

try {
    $conn = getConnection();
    
    // Get form data
    $firstName = $_POST['USER_FNAME'] ?? '';
    $lastName = $_POST['USER_LNAME'] ?? '';
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['PASSWORD'] ?? '';
    
    // Basic validation
    if (empty($firstName) || empty($lastName)) {
        echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update personal information
    $updateUserSql = "UPDATE user SET USER_FNAME = ?, USER_LNAME = ? WHERE USER_ID = ?";
    $stmt = $conn->prepare($updateUserSql);
    $stmt->bind_param("ssi", $firstName, $lastName, $userId);
    $stmt->execute();
    
    // Check if we need to update the password
    if (!empty($currentPassword) && !empty($newPassword)) {
        // Fetch current password to verify
        $passwordSql = "SELECT PASSWORD FROM user WHERE USER_ID = ?";
        $passStmt = $conn->prepare($passwordSql);
        $passStmt->bind_param("i", $userId);
        $passStmt->execute();
        $passResult = $passStmt->get_result();
        $userData = $passResult->fetch_assoc();
        
        // Verify current password
        if (!$userData || !password_verify($currentPassword, $userData['PASSWORD'])) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }
        
        // Hash new password and update
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePassSql = "UPDATE user SET PASSWORD = ? WHERE USER_ID = ?";
        $updatePassStmt = $conn->prepare($updatePassSql);
        $updatePassStmt->bind_param("si", $hashedPassword, $userId);
        $updatePassStmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Update session data
    $_SESSION['name'] = $firstName . ' ' . $lastName;
    $_SESSION['firstname'] = $firstName;
    $_SESSION['lastname'] = $lastName;
    
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    
} catch (Exception $e) {
    // Roll back transaction if error occurs
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>
