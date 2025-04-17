<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array();
    
    // Get POST data
    $username = trim($_POST['username']);
    $employeeId = trim($_POST['employee_id']);
    $role = $_POST['role'];
    $step = $_POST['step'];
    $verificationCode = isset($_POST['verification_code']) ? $_POST['verification_code'] : null;
    
    // Convert role to match database enum
    $userType = ($role === 'admin') ? 'ADMINISTRATOR' : 'STAFF';
    
    try {
        if ($step === '1') {
            // Verify user exists
            $stmt = $conn->prepare("SELECT USER_ID, USERNAME, USER_TYPE 
                                  FROM user 
                                  WHERE USERNAME = ? 
                                  AND EMPLOYEE_ID = ? 
                                  AND USER_TYPE = ? 
                                  AND IS_ACTIVE = 1");
            $stmt->execute([$username, $employeeId, $userType]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Store user_id in session for step 2
                $_SESSION['reset_user_id'] = $user['USER_ID'];
                // In production, generate and send real verification code
                $_SESSION['verification_code'] = '123456';
                
                $response = [
                    'success' => true,
                    'message' => 'Verification code sent',
                    'step' => 2
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }
        } 
        elseif ($step === '2') {
            // Verify the code
            if ($verificationCode === $_SESSION['verification_code']) {
                // Generate temporary password
                $tempPassword = 'Temp123!';
                $userId = $_SESSION['reset_user_id'];
                
                // Update password in database
                $stmt = $conn->prepare("UPDATE user SET PASSWORD = ? WHERE USER_ID = ?");
                $stmt->execute([$tempPassword, $userId]);
                
                // Clear session variables
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['verification_code']);
                
                $response = [
                    'success' => true,
                    'message' => 'Password reset successful',
                    'temp_password' => $tempPassword
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Invalid verification code'
                ];
            }
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error occurred'
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
