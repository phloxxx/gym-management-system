<?php
require_once '../config/db_connection.php';
require_once 'transaction-functions.php';

header('Content-Type: application/json');

// Get JSON data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Log the received data for debugging
error_log('Transaction data received: ' . json_encode($data));

// Validate required fields
if (!isset($data['memberId']) || !isset($data['subscriptionId']) || !isset($data['paymentId']) 
    || !isset($data['startDate']) || !isset($data['endDate'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$memberId = intval($data['memberId']);
$subscriptionId = intval($data['subscriptionId']);
$paymentId = intval($data['paymentId']);
$startDate = $data['startDate'];
$endDate = $data['endDate'];
$isRenewal = isset($data['isRenewal']) ? (bool) $data['isRenewal'] : false;
$previousSubId = isset($data['previousSubId']) ? intval($data['previousSubId']) : null;

try {
    // First verify that the member, subscription, and payment method exist
    $conn = getConnection();
    
    // Check if member exists
    $memberStmt = $conn->prepare("SELECT MEMBER_ID FROM member WHERE MEMBER_ID = ?");
    if (!$memberStmt) {
        throw new Exception("Failed to prepare member validation statement: " . $conn->error);
    }
    $memberStmt->bind_param("i", $memberId);
    $memberStmt->execute();
    $memberResult = $memberStmt->get_result();
    if ($memberResult->num_rows === 0) {
        throw new Exception("Member ID $memberId does not exist");
    }
    
    // Check if subscription exists
    $subStmt = $conn->prepare("SELECT SUB_ID FROM subscription WHERE SUB_ID = ? AND IS_ACTIVE = 1");
    if (!$subStmt) {
        throw new Exception("Failed to prepare subscription validation statement: " . $conn->error);
    }
    $subStmt->bind_param("i", $subscriptionId);
    $subStmt->execute();
    $subResult = $subStmt->get_result();
    if ($subResult->num_rows === 0) {
        throw new Exception("Subscription ID $subscriptionId does not exist or is inactive");
    }
    
    // Check if payment method exists
    $payStmt = $conn->prepare("SELECT PAYMENT_ID FROM payment WHERE PAYMENT_ID = ? AND IS_ACTIVE = 1");
    if (!$payStmt) {
        throw new Exception("Failed to prepare payment validation statement: " . $conn->error);
    }
    $payStmt->bind_param("i", $paymentId);
    $payStmt->execute();
    $payResult = $payStmt->get_result();
    if ($payResult->num_rows === 0) {
        throw new Exception("Payment method ID $paymentId does not exist or is inactive");
    }
    
    // Now create the transaction
    $result = createTransaction($memberId, $subscriptionId, $paymentId, $startDate, $endDate, $isRenewal, $previousSubId);
    
    if ($result) {
        // Get member name for success message
        $stmt = $conn->prepare("SELECT CONCAT(MEMBER_FNAME, ' ', MEMBER_LNAME) as name, MEMBER_FNAME, MEMBER_LNAME, EMAIL FROM member WHERE MEMBER_ID = ?");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $memberResult = $stmt->get_result();
        $memberData = $memberResult->fetch_assoc();
        $memberName = $memberData['name'] ?? 'Member';
        
        // Get subscription name and details
        $subStmt = $conn->prepare("SELECT SUB_NAME, DURATION, PRICE FROM subscription WHERE SUB_ID = ?");
        $subStmt->bind_param("i", $subscriptionId);
        $subStmt->execute();
        $subResult = $subStmt->get_result();
        $subscriptionData = $subResult->fetch_assoc();
        
        // Get payment method name
        $payStmt = $conn->prepare("SELECT PAY_METHOD FROM payment WHERE PAYMENT_ID = ?");
        $payStmt->bind_param("i", $paymentId);
        $payStmt->execute();
        $payResult = $payStmt->get_result();
        $paymentMethod = $payResult->fetch_assoc()['PAY_METHOD'] ?? 'Unknown';
        
        // Customize the message based on whether this is a new subscription or renewal
        $message = $isRenewal 
            ? "{$subscriptionData['SUB_NAME']} subscription successfully renewed for $memberName"
            : "{$subscriptionData['SUB_NAME']} subscription successfully processed for $memberName";
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'isRenewal' => $isRenewal,
            'transaction' => [
                'memberId' => $memberId,
                'memberName' => $memberName,
                'memberFname' => $memberData['MEMBER_FNAME'],
                'memberLname' => $memberData['MEMBER_LNAME'],
                'memberEmail' => $memberData['EMAIL'],
                'subscriptionId' => $subscriptionId,
                'subscriptionName' => $subscriptionData['SUB_NAME'],
                'duration' => $subscriptionData['DURATION'],
                'price' => $subscriptionData['PRICE'],
                'paymentId' => $paymentId,
                'paymentMethod' => $paymentMethod,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'transactionDate' => date('Y-m-d')
            ]
        ]);
    } else {
        throw new Exception("Failed to create transaction");
    }
} catch (Exception $e) {
    error_log('Transaction error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
