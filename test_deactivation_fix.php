<?php
// Test file to check subscription deactivation with multiple records

// Include required files
require_once 'includes/db_connection.php';
require_once 'functions/deactivate-subscription.php';

// Output as plain text
header('Content-Type: text/plain');

// Let's look at the current state of member subscriptions
function showCurrentSubscriptions($conn, $memberId) {
    echo "Current subscriptions for Member ID $memberId:\n";
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-10s %-10s %-15s %-15s %-10s\n", "MEMBER_ID", "SUB_ID", "START_DATE", "END_DATE", "IS_ACTIVE");
    echo str_repeat("-", 80) . "\n";

    $sql = "SELECT * FROM member_subscription 
            WHERE MEMBER_ID = ? 
            ORDER BY SUB_ID, END_DATE DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        echo sprintf("%-10s %-10s %-15s %-15s %-10s\n", 
            $row['MEMBER_ID'], 
            $row['SUB_ID'], 
            $row['START_DATE'], 
            $row['END_DATE'], 
            $row['IS_ACTIVE'] ? "ACTIVE" : "INACTIVE"
        );
    }
    echo str_repeat("-", 80) . "\n\n";
}

// Connect to database
$conn = getConnection();

// We'll test with member IDs from the image (1 = Rovic, 2 = Jhanna)
$memberId = 2; // Jhanna
$subId = 4;    // The subscription ID from the image

echo "SUBSCRIPTION DEACTIVATION TEST\n";
echo "=============================\n\n";

// Show subscriptions before deactivation
echo "BEFORE DEACTIVATION:\n";
showCurrentSubscriptions($conn, $memberId);

// Try to deactivate the active subscription
echo "ATTEMPTING TO DEACTIVATE ACTIVE SUBSCRIPTION:\n";
try {
    $result = deactivateSubscription($memberId, $subId);
    echo "Deactivation " . ($result ? "SUCCESSFUL" : "FAILED") . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Show subscriptions after deactivation
echo "AFTER DEACTIVATION:\n";
showCurrentSubscriptions($conn, $memberId);

// Now try to deactivate again (should fail since all are inactive)
echo "ATTEMPTING TO DEACTIVATE AGAIN (SHOULD FAIL):\n";
try {
    $result = deactivateSubscription($memberId, $subId);
    echo "Deactivation " . ($result ? "SUCCESSFUL" : "FAILED") . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Close connection
$conn->close();

echo "Test completed.";
?>
