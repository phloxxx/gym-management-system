<?php
require_once '../config/db_connection.php';
require_once 'transaction-functions.php';

// Set proper headers and error reporting
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users, but log them

// Log the start of the request with timestamp
error_log('Transaction creation request received: ' . date('Y-m-d H:i:s'));

// Get JSON data from POST request
$jsonInput = file_get_contents('php://input');
error_log('Raw input: ' . $jsonInput);

$data = json_decode($jsonInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . json_last_error_msg()]);
    exit;
}

// Generate a unique request ID if not provided
if (!isset($data['requestId'])) {
    $data['requestId'] = uniqid('txn_', true);
}

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
    // Check for duplicate submission via session
    session_start();
    
    // If this exact request was processed in the last 60 seconds, reject it
    if (isset($_SESSION['last_transaction']) && 
        $_SESSION['last_transaction']['memberId'] === $memberId &&
        $_SESSION['last_transaction']['subscriptionId'] === $subscriptionId &&
        $_SESSION['last_transaction']['timestamp'] > time() - 60 ) {
        
        error_log("Duplicate transaction submission detected, returning cached response");
        echo json_encode($_SESSION['last_transaction']['response']);
        exit;
    }
    
    // Validate the start date is not in the past
    $currentDate = date('Y-m-d');
    if ($startDate < $currentDate) {
        error_log("Rejected: Start date {$startDate} is before current date {$currentDate}");
        throw new Exception("Start date cannot be in the past. Please select today or a future date.");
    }
    
    error_log("Getting database connection");
    $conn = getConnection();
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    error_log("Database connection successful");
    
    // Verify member exists
    $memberStmt = $conn->prepare("SELECT MEMBER_ID, IS_ACTIVE FROM member WHERE MEMBER_ID = ?");
    if (!$memberStmt) {
        throw new Exception("Failed to prepare member validation statement: " . $conn->error);
    }
    $memberStmt->bind_param("i", $memberId);
    $memberStmt->execute();
    $memberResult = $memberStmt->get_result();
    if ($memberResult->num_rows === 0) {
        throw new Exception("Member ID $memberId does not exist");
    }
    
    // NEW CODE: Check if member already has an active subscription during this period
    // This prevents adding a transaction if an active subscription exists for the same period
    if (!$isRenewal) { // Don't check for renewals as we're explicitly replacing the subscription
        $checkOverlapSql = "SELECT ms.SUB_ID, ms.START_DATE, ms.END_DATE, s.SUB_NAME 
                           FROM member_subscription ms 
                           JOIN subscription s ON ms.SUB_ID = s.SUB_ID 
                           WHERE ms.MEMBER_ID = ? 
                           AND ms.IS_ACTIVE = 1 
                           AND (
                               (? BETWEEN ms.START_DATE AND ms.END_DATE) OR  -- New start date within existing range
                               (? BETWEEN ms.START_DATE AND ms.END_DATE) OR  -- New end date within existing range
                               (ms.START_DATE BETWEEN ? AND ?) OR          -- Existing start date within new range
                               (ms.END_DATE BETWEEN ? AND ?)              -- Existing end date within new range
                           )";
        
        $checkOverlapStmt = $conn->prepare($checkOverlapSql);
        $checkOverlapStmt->bind_param("issssss", 
            $memberId, 
            $startDate, $endDate, 
            $startDate, $endDate, 
            $startDate, $endDate
        );
        $checkOverlapStmt->execute();
        $overlapResult = $checkOverlapStmt->get_result();
        
        if ($overlapResult->num_rows > 0) {
            $existingSub = $overlapResult->fetch_assoc();
            error_log("Rejected: Member has active subscription that overlaps with requested dates");
            throw new Exception(
                "This member already has an active subscription ({$existingSub['SUB_NAME']}) " .
                "from {$existingSub['START_DATE']} to {$existingSub['END_DATE']}. " .
                "Please deactivate the existing subscription before adding a new one, or use the renewal option."
            );
        }
    }
    
    // Check for duplicate transaction
    if ($isRenewal) {
        $checkDuplicateTransaction = $conn->prepare(
            "SELECT t.TRANSACTION_ID 
             FROM transaction t
             WHERE t.MEMBER_ID = ? 
             AND t.SUB_ID = ? 
             AND DATE(t.TRANSAC_DATE) = CURRENT_DATE()"
        );
        $checkDuplicateTransaction->bind_param("ii", $memberId, $subscriptionId);
        $checkDuplicateTransaction->execute();
        $duplicateResult = $checkDuplicateTransaction->get_result();
        
        if ($duplicateResult->num_rows > 0) {
            error_log("Found existing transaction for memberId=$memberId, subscriptionId=$subscriptionId made today");
            
            // Get member name for response message
            $memberData = $memberResult->fetch_assoc();
            $memberStmt = $conn->prepare("SELECT CONCAT(MEMBER_FNAME, ' ', MEMBER_LNAME) as name FROM member WHERE MEMBER_ID = ?");
            $memberStmt->bind_param("i", $memberId);
            $memberStmt->execute();
            $memberNameResult = $memberStmt->get_result();
            $memberName = $memberNameResult->fetch_assoc()['name'] ?? 'Member';
            
            // Get subscription name
            $subStmt = $conn->prepare("SELECT SUB_NAME FROM subscription WHERE SUB_ID = ?");
            $subStmt->bind_param("i", $subscriptionId);
            $subStmt->execute();
            $subResult = $subStmt->get_result();
            $subscriptionName = $subResult->fetch_assoc()['SUB_NAME'] ?? 'Subscription';
            
            // Return success response but indicate it was already processed
            $response = [
                'success' => true, 
                'message' => "$subscriptionName subscription has already been renewed for $memberName today",
                'isRenewal' => true,
                'isDuplicate' => true
            ];
            
            echo json_encode($response);
            exit;
        }
    }
    
    // Start transaction for data consistency
    $conn->begin_transaction();
    error_log("Beginning transaction creation...");
    
    // Insert transaction record first
    $transactionSql = "INSERT INTO transaction (MEMBER_ID, SUB_ID, PAYMENT_ID, TRANSAC_DATE) 
                      VALUES (?, ?, ?, CURRENT_DATE())";
    $transStmt = $conn->prepare($transactionSql);
    if (!$transStmt) {
        throw new Exception("Failed to prepare transaction insert statement: " . $conn->error);
    }
    
    $transStmt->bind_param("iii", $memberId, $subscriptionId, $paymentId);
    if (!$transStmt->execute()) {
        throw new Exception("Failed to insert transaction: " . $transStmt->error);
    }
    
    $transactionId = $conn->insert_id;
    error_log("Created transaction with ID: $transactionId");
    
    // Get subscription duration to calculate end date
    $durationSql = "SELECT DURATION FROM subscription WHERE SUB_ID = ?";
    $durationStmt = $conn->prepare($durationSql);
    $durationStmt->bind_param("i", $subscriptionId);
    $durationStmt->execute();
    $durationResult = $durationStmt->get_result();
    $durationData = $durationResult->fetch_assoc();
    $duration = $durationData['DURATION'];
    
    // Calculate end date
    $startDateTime = new DateTime($startDate);
    $endDateTime = clone $startDateTime;
    $endDateTime->add(new DateInterval("P{$duration}D"));
    $calculatedEndDate = $endDateTime->format('Y-m-d');
    
    error_log("Calculated end date: $calculatedEndDate based on duration $duration days");
    
    // If renewal, specifically deactivate the previous subscription
    if ($isRenewal) {
        error_log("Processing renewal: memberId=$memberId, previousSubId=$previousSubId, newSubId=$subscriptionId");

        if ($previousSubId) {
            // Deactivate previous subscription
            $deactivateSql = "UPDATE member_subscription 
                             SET IS_ACTIVE = 0 
                             WHERE MEMBER_ID = ? AND SUB_ID = ? AND IS_ACTIVE = 1";
            $deactivateStmt = $conn->prepare($deactivateSql);
            $deactivateStmt->bind_param("ii", $memberId, $previousSubId);
            $deactivateStmt->execute();
            $deactivatedRows = $deactivateStmt->affected_rows;
            error_log("Deactivated previous subscription: rows affected: $deactivatedRows");

            // Log the deactivation in transaction_log
            $logDeactivationSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
                                  VALUES (?, 'DEACTIVATED', CURRENT_DATE())";
            $logDeactivateStmt = $conn->prepare($logDeactivationSql);
            $logDeactivateStmt->bind_param("i", $transactionId);
            $logDeactivateStmt->execute();
        } else {
            // If no specific previous subscription ID provided, deactivate all active subscriptions
            error_log("No specific previous subscription provided, deactivating all active subscriptions");
            $deactivateAllSql = "UPDATE member_subscription SET IS_ACTIVE = 0 WHERE MEMBER_ID = ? AND IS_ACTIVE = 1";
            $deactivateAllStmt = $conn->prepare($deactivateAllSql);
            $deactivateAllStmt->bind_param("i", $memberId);
            $deactivateAllStmt->execute();
            error_log("Deactivated all subscriptions: rows affected: " . $deactivateAllStmt->affected_rows);
        }

        // Insert new subscription record - always insert for renewals, don't update existing
        $insertSubSql = "INSERT INTO member_subscription (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                         VALUES (?, ?, ?, ?, 1)";
        $insertSubStmt = $conn->prepare($insertSubSql);
        $insertSubStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
        
        if (!$insertSubStmt->execute()) {
            throw new Exception("Failed to insert new subscription for renewal: " . $insertSubStmt->error);
        }
        error_log("Inserted new subscription record for renewal");
    } else {
        // Regular transaction (not renewal)
        // Insert or update member_subscription
        $checkSubscriptionSql = "SELECT * FROM member_subscription 
                               WHERE MEMBER_ID = ? AND SUB_ID = ? AND START_DATE = ? AND END_DATE = ?";
        $checkSubStmt = $conn->prepare($checkSubscriptionSql);
        $checkSubStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
        $checkSubStmt->execute();
        $checkSubResult = $checkSubStmt->get_result();
        
        if ($checkSubResult->num_rows > 0) {
            // Update existing subscription
            $updateSubSql = "UPDATE member_subscription SET IS_ACTIVE = 1 
                           WHERE MEMBER_ID = ? AND SUB_ID = ? AND START_DATE = ? AND END_DATE = ?";
            $updateSubStmt = $conn->prepare($updateSubSql);
            $updateSubStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
            $updateSubStmt->execute();
            error_log("Updated existing subscription: rows affected: " . $updateSubStmt->affected_rows);
        } else {
            // Insert new subscription
            $insertSubSql = "INSERT INTO member_subscription (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                            VALUES (?, ?, ?, ?, 1)";
            $insertSubStmt = $conn->prepare($insertSubSql);
            $insertSubStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
            if (!$insertSubStmt->execute()) {
                throw new Exception("Failed to insert subscription: " . $insertSubStmt->error);
            }
            error_log("Created new subscription record");
        }
    }
    
    // Update member to be active
    $activateMemberSql = "UPDATE member SET IS_ACTIVE = 1 WHERE MEMBER_ID = ?";
    $activateMemberStmt = $conn->prepare($activateMemberSql);
    $activateMemberStmt->bind_param("i", $memberId);
    $activateMemberStmt->execute();
    
    // Log the transaction
    $logSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
              VALUES (?, ?, CURRENT_DATE())";
    $logStmt = $conn->prepare($logSql);
    $operation = $isRenewal ? "RENEWAL" : "INSERT";
    $logStmt->bind_param("is", $transactionId, $operation);
    $logStmt->execute();
    
    // Commit the transaction
    $conn->commit();
    error_log("Transaction committed successfully");
    
    // Get member data for response
    $memberDataSql = "SELECT MEMBER_FNAME, MEMBER_LNAME, EMAIL FROM member WHERE MEMBER_ID = ?";
    $memberDataStmt = $conn->prepare($memberDataSql);
    $memberDataStmt->bind_param("i", $memberId);
    $memberDataStmt->execute();
    $memberResult = $memberDataStmt->get_result();
    $memberData = $memberResult->fetch_assoc();
    
    // Get subscription data
    $subDataSql = "SELECT SUB_NAME, DURATION, PRICE FROM subscription WHERE SUB_ID = ?";
    $subDataStmt = $conn->prepare($subDataSql);
    $subDataStmt->bind_param("i", $subscriptionId);
    $subDataStmt->execute();
    $subResult = $subDataStmt->get_result();
    $subscriptionData = $subResult->fetch_assoc();
    
    // Get payment method
    $paymentSql = "SELECT PAY_METHOD FROM payment WHERE PAYMENT_ID = ?";
    $paymentStmt = $conn->prepare($paymentSql);
    $paymentStmt->bind_param("i", $paymentId);
    $paymentStmt->execute();
    $paymentResult = $paymentStmt->get_result();
    $paymentData = $paymentResult->fetch_assoc();
    
    // Build response
    $memberName = $memberData['MEMBER_FNAME'] . ' ' . $memberData['MEMBER_LNAME'];
    $message = $isRenewal ? 
        "{$subscriptionData['SUB_NAME']} subscription renewed for $memberName" : 
        "{$subscriptionData['SUB_NAME']} subscription created for $memberName";
    
    $response = [
        'success' => true,
        'message' => $message,
        'isRenewal' => $isRenewal,
        'transaction' => [
            'transactionId' => $transactionId,
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
            'paymentMethod' => $paymentData['PAY_METHOD'],
            'startDate' => $startDate,
            'endDate' => $calculatedEndDate,
            'transactionDate' => date('Y-m-d'),
            'requestId' => $data['requestId']
        ]
    ];
    
    // Cache this response to prevent duplicate submissions
    $_SESSION['last_transaction'] = [
        'memberId' => $memberId,
        'subscriptionId' => $subscriptionId,
        'timestamp' => time(),
        'response' => $response
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Transaction error: ' . $e->getMessage());
    error_log('Exception details: ' . $e->getTraceAsString());
    
    // If we have a database connection and a transaction is active, roll it back
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
        error_log("Transaction rolled back due to error");
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
        error_log("Database connection closed");
    }
    error_log('Transaction request processing complete');
}
