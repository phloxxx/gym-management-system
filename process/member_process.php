<?php
require_once '../config/db_connection.php';
session_start();

// Set header to return JSON
header('Content-Type: application/json');

try {
    // Check if user is logged in and has appropriate role
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrator', 'staff'])) {
        throw new Exception("Unauthorized access");
    }
    
    $conn = getConnection();
    
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }
    
    // Generate unique request ID to prevent duplicate processing
    $requestId = isset($_POST['request_id']) ? $_POST['request_id'] : uniqid();
    
    // Check if this request has been processed already (using session to track)
    if (isset($_SESSION['processed_requests']) && 
        in_array($requestId, $_SESSION['processed_requests'])) {
        // This is a duplicate request, return success but do nothing
        error_log("Duplicate member creation request detected with ID: $requestId");
        echo json_encode([
            'status' => 'success',
            'message' => 'Member request already processed',
            'isDuplicate' => true
        ]);
        exit;
    }
    
    // Log the received data for debugging
    error_log("Member data received: " . json_encode($_POST));
    
    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'programId'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("$field is required");
        }
    }
    
    // Sanitize inputs
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $programId = (int)$_POST['programId'];
    $isActive = isset($_POST['isActive']) ? 1 : 0;
    $gender = $_POST['gender'] ?? '';
    $birthdate = $_POST['birthdate'] ?? null;
    
    // Begin transaction for data consistency
    $conn->begin_transaction();
    
    // Check if email already exists to prevent duplicates - use a more thorough check
    $checkEmailSql = "SELECT COUNT(*) as count FROM member WHERE LOWER(EMAIL) = LOWER(?)";
    $checkStmt = $conn->prepare($checkEmailSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        throw new Exception("Email address already exists");
    }
    
    // Also check for a recent member with same name and program to catch duplicates
    $checkDuplicateSql = "SELECT COUNT(*) as count FROM member 
                         WHERE LOWER(MEMBER_FNAME) = LOWER(?) 
                         AND LOWER(MEMBER_LNAME) = LOWER(?) 
                         AND PROGRAM_ID = ? 
                         AND JOINED_DATE = CURRENT_DATE()";
    $duplicateStmt = $conn->prepare($checkDuplicateSql);
    $duplicateStmt->bind_param("ssi", $firstName, $lastName, $programId);
    $duplicateStmt->execute();
    $duplicateResult = $duplicateStmt->get_result();
    $duplicateRow = $duplicateResult->fetch_assoc();
    
    if ($duplicateRow['count'] > 0) {
        throw new Exception("A member with this name and program was already added today. This may be a duplicate.");
    }
    
    // Insert new member
    $insertSql = "INSERT INTO member (MEMBER_FNAME, MEMBER_LNAME, EMAIL, PHONE_NUMBER, PROGRAM_ID, IS_ACTIVE, GENDER, BIRTHDATE, JOINED_DATE) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE())";
    
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ssssisss", $firstName, $lastName, $email, $phone, $programId, $isActive, $gender, $birthdate);
    
    if ($stmt->execute()) {
        $memberId = $conn->insert_id;
        
        // Process comorbidities if provided
        if (isset($_POST['comorbidities']) && !empty($_POST['comorbidities'])) {
            $comorbidities = json_decode($_POST['comorbidities'], true);
            
            if (!empty($comorbidities)) {
                $comorbidityInsertSql = "INSERT INTO member_comorbidity (MEMBER_ID, COMOR_ID) VALUES (?, ?)";
                $comorbidityStmt = $conn->prepare($comorbidityInsertSql);
                
                foreach ($comorbidities as $comorbidityId) {
                    $comorbidityStmt->bind_param("ii", $memberId, $comorbidityId);
                    $comorbidityStmt->execute();
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Mark this request as processed to prevent duplicates
        if (!isset($_SESSION['processed_requests'])) {
            $_SESSION['processed_requests'] = [];
        }
        $_SESSION['processed_requests'][] = $requestId;
        
        // Limit the size of processed requests array to prevent session bloat
        if (count($_SESSION['processed_requests']) > 100) {
            array_shift($_SESSION['processed_requests']);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Member added successfully',
            'memberId' => $memberId
        ]);
    } else {
        throw new Exception("Failed to add member: " . $stmt->error);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    error_log("Error adding member: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
