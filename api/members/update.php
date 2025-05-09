<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';
session_start();

try {
    // Check if user is logged in and has appropriate role
    if (!isset($_SESSION['user_id']) || (strtolower($_SESSION['role']) !== 'administrator' && strtolower($_SESSION['role']) !== 'staff')) {
        throw new Exception("Unauthorized access. Please login with appropriate credentials.");
    }

    // Get member ID from URL parameter
    $memberId = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$memberId) {
        throw new Exception('Member ID is required');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid input data');
    }

    $conn = getConnection();
    
    // First get current member data to preserve program_id
    $query = "SELECT PROGRAM_ID, USER_ID FROM member WHERE MEMBER_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Member not found");
    }
    
    $currentMember = $result->fetch_assoc();
    
    // For staff members, only allow them to update members they created
    if (strtolower($_SESSION['role']) === 'staff' && $currentMember['USER_ID'] != $_SESSION['user_id']) {
        throw new Exception("You are not authorized to modify this member");
    }
    
    // Update member basic information - but preserve program_id
    $query = "UPDATE member SET 
              MEMBER_FNAME = ?, 
              MEMBER_LNAME = ?, 
              EMAIL = ?, 
              PHONE_NUMBER = ?, 
              IS_ACTIVE = ?
              WHERE MEMBER_ID = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssii', 
        $data['MEMBER_FNAME'],
        $data['MEMBER_LNAME'],
        $data['EMAIL'],
        $data['PHONE_NUMBER'],
        $data['IS_ACTIVE'],
        $memberId
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update member information');
    }

    // Update comorbidities
    if (isset($data['COMORBIDITIES'])) {
        // First delete existing comorbidities
        $deleteQuery = "DELETE FROM member_comorbidities WHERE MEMBER_ID = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('i', $memberId);
        $stmt->execute();

        // Insert new comorbidities
        if (!empty($data['COMORBIDITIES'])) {
            $insertQuery = "INSERT INTO member_comorbidities (MEMBER_ID, COMOR_ID) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            foreach ($data['COMORBIDITIES'] as $comorId) {
                $stmt->bind_param('ii', $memberId, $comorId);
                $stmt->execute();
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Member updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}