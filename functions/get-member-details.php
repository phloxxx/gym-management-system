<?php
require_once '../config/db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if ID parameter is provided
if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Member ID is required'
    ]);
    exit;
}

$memberId = intval($_GET['id']);

// Log the request for debugging
error_log("Fetching member details for ID: $memberId");

if ($memberId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid member ID format'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    // Query to get member details
    $query = "SELECT 
                m.MEMBER_ID, 
                m.MEMBER_FNAME, 
                m.MEMBER_LNAME, 
                m.EMAIL, 
                m.PHONE_NUMBER, 
                p.PROGRAM_NAME, 
                m.JOINED_DATE,
                m.IS_ACTIVE
              FROM member m
              LEFT JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
              WHERE m.MEMBER_ID = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Member not found with ID: $memberId");
        echo json_encode([
            'success' => false,
            'message' => "Member with ID $memberId not found"
        ]);
        exit;
    }
    
    $member = $result->fetch_assoc();
    
    // Format member data for response
    $response = [
        'success' => true,
        'member' => [
            'id' => $member['MEMBER_ID'],
            'firstName' => $member['MEMBER_FNAME'],
            'lastName' => $member['MEMBER_LNAME'],
            'fullName' => $member['MEMBER_FNAME'] . ' ' . $member['MEMBER_LNAME'],
            'email' => $member['EMAIL'],
            'phone' => $member['PHONE_NUMBER'],
            'program' => $member['PROGRAM_NAME'],
            'joinedDate' => $member['JOINED_DATE'],
            'isActive' => (bool)$member['IS_ACTIVE'],
            'initials' => strtoupper(substr($member['MEMBER_FNAME'], 0, 1) . substr($member['MEMBER_LNAME'], 0, 1))
        ]
    ];
    
    error_log("Successfully fetched member details for ID: $memberId");
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Error fetching member details: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching member details: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>