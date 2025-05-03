<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $conn = getConnection();
    
    // Get POST data and log it
    $input = file_get_contents('php://input');
    error_log("Raw input: " . $input);
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
    }
    
    error_log("Processed data: " . print_r($data, true));
    
    // Validate required fields
    $requiredFields = ['MEMBER_FNAME', 'MEMBER_LNAME', 'EMAIL', 'PHONE_NUMBER', 'PROGRAM_ID', 'SUB_ID', 'START_DATE', 'END_DATE', 'PAYMENT_ID'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // First verify that the user exists
    $userQuery = "SELECT USER_ID FROM user WHERE USER_ID = ? AND IS_ACTIVE = 1 LIMIT 1";
    $stmt = $conn->prepare($userQuery);
    $userId = 1; // Default admin user
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Invalid user reference. USER_ID not found or inactive.");
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Insert into member table with verified USER_ID
    $memberSql = "INSERT INTO member (MEMBER_FNAME, MEMBER_LNAME, EMAIL, PHONE_NUMBER, IS_ACTIVE, PROGRAM_ID, USER_ID) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($memberSql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Convert values to appropriate types
    $isActive = $data['IS_ACTIVE'] ? 1 : 0;
    $programId = (int)$data['PROGRAM_ID'];
    
    // Debug log
    error_log("Binding parameters: " . json_encode([
        'fname' => $data['MEMBER_FNAME'],
        'lname' => $data['MEMBER_LNAME'],
        'email' => $data['EMAIL'],
        'phone' => $data['PHONE_NUMBER'],
        'active' => $isActive,
        'program' => $programId,
        'user' => $userId
    ]));
    
    // Bind parameters with correct types (s=string, i=integer)
    if (!$stmt->bind_param("ssssiis", 
        $data['MEMBER_FNAME'],
        $data['MEMBER_LNAME'],
        $data['EMAIL'],
        $data['PHONE_NUMBER'],
        $isActive,
        $programId,
        $userId
    )) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }
    
    // Execute with error checking
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error . " (Error #" . $stmt->errno . ")");
    }
    
    $memberId = $conn->insert_id;
    error_log("Member inserted with ID: " . $memberId);
    
    // Insert member comorbidities if any
    if (!empty($data['COMORBIDITIES'])) {
        $comorbiditiesSql = "INSERT INTO member_comorbidities (MEMBER_ID, COMOR_ID) VALUES (?, ?)";
        $stmtComor = $conn->prepare($comorbiditiesSql);
        foreach ($data['COMORBIDITIES'] as $comorId) {
            $comorId = (int)$comorId;
            $stmtComor->bind_param("ii", $memberId, $comorId);
            if (!$stmtComor->execute()) {
                throw new Exception("Error inserting comorbidity: " . $stmtComor->error);
            }
        }
    }
    
    // Insert subscription
    $subSql = "INSERT INTO member_subscription (MEMBER_ID, SUB_ID, START_DATE, END_DATE) 
               VALUES (?, ?, ?, ?)";
    $stmtSub = $conn->prepare($subSql);
    $subId = (int)$data['SUB_ID'];
    $stmtSub->bind_param("iiss", 
        $memberId,
        $subId,
        $data['START_DATE'],
        $data['END_DATE']
    );
    if (!$stmtSub->execute()) {
        throw new Exception("Error inserting subscription: " . $stmtSub->error);
    }
    
    // Insert transaction
    $transSql = "INSERT INTO transaction (MEMBER_ID, SUB_ID, PAYMENT_ID, TRANSAC_DATE) 
                 VALUES (?, ?, ?, ?)";
    $stmtTrans = $conn->prepare($transSql);
    $paymentId = (int)$data['PAYMENT_ID'];
    $stmtTrans->bind_param("iiis", 
        $memberId,
        $subId,
        $paymentId,
        $data['TRANSAC_DATE']
    );
    if (!$stmtTrans->execute()) {
        throw new Exception("Error inserting transaction: " . $stmtTrans->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Get the newly created member details for table display
    $memberQuery = "SELECT m.*, p.PROGRAM_NAME, 
                          ms.START_DATE, ms.END_DATE,
                          (SELECT CONCAT(c.COACH_FNAME, ' ', c.COACH_LNAME) 
                           FROM coach c 
                           JOIN program_coach pc ON c.COACH_ID = pc.COACH_ID 
                           WHERE pc.PROGRAM_ID = m.PROGRAM_ID 
                           LIMIT 1) as COACH_NAME
                   FROM member m
                   JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
                   LEFT JOIN member_subscription ms ON m.MEMBER_ID = ms.MEMBER_ID
                   WHERE m.MEMBER_ID = ?";
                   
    $stmtMember = $conn->prepare($memberQuery);
    $stmtMember->bind_param("i", $memberId);
    $stmtMember->execute();
    $result = $stmtMember->get_result();
    $newMember = $result->fetch_assoc();
    
    if (!$newMember) {
        throw new Exception("Error retrieving new member data");
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Member added successfully',
        'member' => $newMember
    ]);
    
} catch (Exception $e) {
    error_log("Error in create.php: " . $e->getMessage());
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
