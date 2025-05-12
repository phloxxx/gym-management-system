<?php
// Include the necessary function
require_once './functions/deactivate-subscription.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Manually call the function with test parameters
try {
    // Test with memberId = 1 and subId = 1
    $memberId = 1;
    $subId = 1;
    
    // Log parameters
    error_log("Testing deactivation with memberId=$memberId, subId=$subId");
    
    // Call the deactivation function
    $result = deactivateSubscription($memberId, $subId);
    
    // Output result
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Subscription successfully deactivated' : 'No subscription was deactivated'
    ]);
} catch (Exception $e) {
    // Log error
    error_log("Deactivation test error: " . $e->getMessage());
    
    // Output error
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 