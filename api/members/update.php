<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
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
    
    // Begin transaction to ensure data consistency
    $conn->begin_transaction();
    
    // First get current member data to preserve program_id
    $query = "SELECT PROGRAM_ID FROM member WHERE MEMBER_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Member not found");
    }
    
    $currentMember = $result->fetch_assoc();
    
    // Update member basic information
    $query = "UPDATE member SET 
              MEMBER_FNAME = ?, 
              MEMBER_LNAME = ?, 
              EMAIL = ?, 
              PHONE_NUMBER = ?, 
              IS_ACTIVE = ?,
              PROGRAM_ID = ?
              WHERE MEMBER_ID = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssiis', 
        $data['MEMBER_FNAME'],
        $data['MEMBER_LNAME'],
        $data['EMAIL'],
        $data['PHONE_NUMBER'],
        $data['IS_ACTIVE'],
        $data['PROGRAM_ID'],
        $memberId
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update member information: ' . $stmt->error);
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
    
    // Handle subscription renewal if requested
    if (isset($data['RENEW_SUBSCRIPTION']) && $data['RENEW_SUBSCRIPTION'] === true) {
        // Mark any existing active subscriptions as inactive
        $deactivateQuery = "UPDATE member_subscription SET IS_ACTIVE = 0 WHERE MEMBER_ID = ? AND IS_ACTIVE = 1";
        $stmt = $conn->prepare($deactivateQuery);
        $stmt->bind_param('i', $memberId);
        $stmt->execute();
        
        // Insert new subscription
        $insertSubQuery = "INSERT INTO member_subscription 
                          (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                          VALUES (?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($insertSubQuery);
        $stmt->bind_param('isss', 
            $memberId, 
            $data['RENEW_SUB_ID'], 
            $data['RENEW_START_DATE'], 
            $data['RENEW_END_DATE']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create new subscription: ' . $stmt->error);
        }
        
        // Get the ID of the newly created subscription
        $newSubId = $conn->insert_id;
        
        // Create transaction record
        $today = date('Y-m-d');
        $insertTransacQuery = "INSERT INTO transaction 
                              (MEMBER_ID, SUB_ID, PAYMENT_ID, USER_ID, TRANSAC_DATE) 
                              VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertTransacQuery);
        $stmt->bind_param('iiiss', 
            $memberId, 
            $data['RENEW_SUB_ID'], 
            $data['RENEW_PAYMENT_ID'], 
            $data['USER_ID'], 
            $today
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create transaction record: ' . $stmt->error);
        }
    }
    
    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Member updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
