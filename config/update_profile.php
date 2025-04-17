<?php
require_once 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $userId = $_SESSION['USER_ID'] ?? 0;
        $firstName = $_POST['USER_FNAME'];
        $lastName = $_POST['USER_LNAME'];
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['PASSWORD'] ?? '';
        
        // First verify if user exists and current password is correct
        $stmt = $conn->prepare("SELECT PASSWORD FROM user WHERE USER_ID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // If changing password, verify current password
        if (!empty($newPassword)) {
            if ($user['PASSWORD'] !== $currentPassword) {
                throw new Exception("Current password is incorrect");
            }
            
            // Update user with new password
            $stmt = $conn->prepare("UPDATE user SET USER_FNAME = ?, USER_LNAME = ?, PASSWORD = ? WHERE USER_ID = ?");
            $stmt->bind_param("sssi", $firstName, $lastName, $newPassword, $userId);
        } else {
            // Update user without changing password
            $stmt = $conn->prepare("UPDATE user SET USER_FNAME = ?, USER_LNAME = ? WHERE USER_ID = ?");
            $stmt->bind_param("ssi", $firstName, $lastName, $userId);
        }
        
        if ($stmt->execute()) {
            $_SESSION['USER_FNAME'] = $firstName;
            $_SESSION['USER_LNAME'] = $lastName;
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully!';
        } else {
            throw new Exception("Error updating profile");
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
