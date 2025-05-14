<?php
// Test script for subscription renewal fix
require_once 'config/db_connection.php';
require_once 'functions/transaction-functions.php';

// Set content type to plain text for better terminal output
header('Content-Type: text/plain');

echo "Testing subscription renewal fix...\n\n";

// Sample data for testing
$memberId = 1;
$subscriptionId = 1;
$paymentId = 1;
$startDate = date('Y-m-d'); // Today
$endDate = date('Y-m-d', strtotime('+30 days')); // 30 days from now
$isRenewal = true;
$previousSubId = 1;

try {
    echo "Step 1: Getting initial database state...\n";
    $conn = getConnection();
    
    // Count current subscriptions for this member/subscription
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM member_subscription WHERE MEMBER_ID = ? AND SUB_ID = ?");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $initialCount = $row['count'];
    echo "Initial subscription count: $initialCount\n\n";
    
    // List all current subscriptions
    $stmt = $conn->prepare("SELECT * FROM member_subscription WHERE MEMBER_ID = ? AND SUB_ID = ? ORDER BY START_DATE");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Current subscriptions:\n";
    echo "--------------------------------------\n";
    echo "MEMBER_ID | SUB_ID | START_DATE | END_DATE | IS_ACTIVE\n";
    echo "--------------------------------------\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "{$row['MEMBER_ID']} | {$row['SUB_ID']} | {$row['START_DATE']} | {$row['END_DATE']} | {$row['IS_ACTIVE']}\n";
    }
    echo "--------------------------------------\n\n";
    
    // Count current transactions
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transaction WHERE MEMBER_ID = ? AND SUB_ID = ? AND DATE(TRANSAC_DATE) = CURRENT_DATE()");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $initialTransCount = $row['count'];
    echo "Initial transaction count for today: $initialTransCount\n\n";
    
    echo "Step 2: Simulating renewal...\n";
    // Call the createTransaction function
    $success = createTransaction($memberId, $subscriptionId, $paymentId, $startDate, $endDate, $isRenewal, $previousSubId);
    
    if ($success) {
        echo "Renewal successful!\n\n";
    } else {
        echo "Renewal failed!\n\n";
    }
    
    echo "Step 3: Checking for duplications...\n";
    // Count subscriptions after renewal
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM member_subscription WHERE MEMBER_ID = ? AND SUB_ID = ?");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $finalCount = $row['count'];
    echo "Final subscription count: $finalCount\n\n";
    
    // List all subscriptions after renewal
    $stmt = $conn->prepare("SELECT * FROM member_subscription WHERE MEMBER_ID = ? AND SUB_ID = ? ORDER BY START_DATE");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Subscriptions after renewal:\n";
    echo "--------------------------------------\n";
    echo "MEMBER_ID | SUB_ID | START_DATE | END_DATE | IS_ACTIVE\n";
    echo "--------------------------------------\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "{$row['MEMBER_ID']} | {$row['SUB_ID']} | {$row['START_DATE']} | {$row['END_DATE']} | {$row['IS_ACTIVE']}\n";
    }
    echo "--------------------------------------\n\n";
    
    // Count transactions after renewal
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transaction WHERE MEMBER_ID = ? AND SUB_ID = ? AND DATE(TRANSAC_DATE) = CURRENT_DATE()");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $finalTransCount = $row['count'];
    echo "Final transaction count for today: $finalTransCount\n\n";
    
    echo "Step 4: Re-running the renewal to test duplicate prevention...\n";
    // Try renewal again with same parameters
    $success2 = createTransaction($memberId, $subscriptionId, $paymentId, $startDate, $endDate, $isRenewal, $previousSubId);
    
    if ($success2) {
        echo "Second renewal successful (but should not create duplicates)!\n\n";
    } else {
        echo "Second renewal failed!\n\n";
    }
    
    // Count subscriptions after second renewal
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM member_subscription WHERE MEMBER_ID = ? AND SUB_ID = ?");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $finalCount2 = $row['count'];
    echo "Subscription count after second renewal: $finalCount2\n\n";
    
    // Count transactions after second renewal
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transaction WHERE MEMBER_ID = ? AND SUB_ID = ? AND DATE(TRANSAC_DATE) = CURRENT_DATE()");
    $stmt->bind_param("ii", $memberId, $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $finalTransCount2 = $row['count'];
    echo "Transaction count for today after second renewal: $finalTransCount2\n\n";
    
    // Check for duplicates
    if ($finalCount2 > $finalCount) {
        echo "ERROR: Duplicates detected in member_subscription table!\n";
    } else {
        echo "SUCCESS: No duplicates in member_subscription table!\n";
    }
    
    if ($finalTransCount2 > $finalTransCount) {
        echo "ERROR: Duplicates detected in transaction table!\n";
    } else {
        echo "SUCCESS: No duplicates in transaction table!\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
