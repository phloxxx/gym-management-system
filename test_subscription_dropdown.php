<?php
require_once 'config/db_connection.php';
require_once 'functions/transaction-functions.php';

// Get subscription plans using the function
$subscriptionPlans = getSubscriptionPlans();

echo "<h2>Testing Subscription Dropdown</h2>";
echo "<p>This script tests if our fix for the subscription dropdown in the transaction modal is working properly.</p>";

// Display subscription plans array
echo "<h3>Data from getSubscriptionPlans() function:</h3>";
echo "<pre>";
print_r($subscriptionPlans);
echo "</pre>";

// Create and display the actual dropdown
echo "<h3>Rendered Dropdown:</h3>";
echo "<div style='width: 300px; padding: 20px; border: 1px solid #ccc;'>";
echo "<select style='width: 100%; padding: 8px;'>";
echo "<option value=''>Select Subscription</option>";

if(!empty($subscriptionPlans)) {
    foreach ($subscriptionPlans as $plan) {
        // Make sure all required keys exist
        $subId = isset($plan['SUB_ID']) ? $plan['SUB_ID'] : '';
        $subName = isset($plan['SUB_NAME']) ? $plan['SUB_NAME'] : '';
        $duration = isset($plan['DURATION']) ? $plan['DURATION'] : '0';
        $price = isset($plan['PRICE']) ? $plan['PRICE'] : '0';
        
        echo "<option value='" . $subId . "' data-duration='" . $duration . " Days' data-price='" . number_format($price, 2) . "'>";
        echo $subName . " - ₱" . number_format($price, 2);
        echo "</option>";
    }
} else {
    echo "<option value='' disabled>No subscription plans found</option>";
}

echo "</select>";
echo "</div>";

// Instructions
echo "<h3>Next Steps:</h3>";
echo "<p>If the dropdown above shows subscription plans properly, our fix is working. If not, we need to investigate further.</p>";
echo "<p>Now you can try accessing your transaction modal in the admin interface to see if the issue is resolved.</p>";
?> 