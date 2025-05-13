<?php
require_once '../../config/db_connection.php';
require_once '../../functions/transaction-functions.php';

// Get subscription plans data
$subscriptionPlans = getSubscriptionPlans();

// Display the data
echo "<h1>Subscription Plans Debug</h1>";
echo "<h2>Raw Data:</h2>";
echo "<pre>";
print_r($subscriptionPlans);
echo "</pre>";

// Check for specific issues
echo "<h2>Key Existence Check:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Plan ID</th><th>Plan Name</th><th>Duration Key</th><th>Duration Value</th><th>Price Key</th><th>Price Value</th></tr>";

foreach ($subscriptionPlans as $plan) {
    echo "<tr>";
    echo "<td>" . (isset($plan['SUB_ID']) ? $plan['SUB_ID'] : 'NOT SET') . "</td>";
    echo "<td>" . (isset($plan['SUB_NAME']) ? $plan['SUB_NAME'] : 'NOT SET') . "</td>";
    echo "<td>" . (array_key_exists('DURATION', $plan) ? 'EXISTS' : 'MISSING') . "</td>";
    echo "<td>" . (isset($plan['DURATION']) ? $plan['DURATION'] : 'NOT SET') . "</td>";
    echo "<td>" . (array_key_exists('PRICE', $plan) ? 'EXISTS' : 'MISSING') . "</td>";
    echo "<td>" . (isset($plan['PRICE']) ? $plan['PRICE'] : 'NOT SET') . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test SQL query directly
echo "<h2>Direct SQL Query Test:</h2>";
$conn = getConnection();
$sql = "SELECT SUB_ID, SUB_NAME, DURATION, PRICE FROM subscription WHERE IS_ACTIVE = 1 ORDER BY PRICE";
$result = $conn->query($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>SUB_ID</th><th>SUB_NAME</th><th>DURATION</th><th>PRICE</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['SUB_ID'] . "</td>";
        echo "<td>" . $row['SUB_NAME'] . "</td>"; 
        echo "<td>" . $row['DURATION'] . "</td>";
        echo "<td>" . $row['PRICE'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No subscription plans found in database</td></tr>";
}

echo "</table>";
$conn->close();
?> 