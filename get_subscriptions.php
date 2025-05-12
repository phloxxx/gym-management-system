<?php
require_once 'config/db_connection.php';
require_once 'functions/transaction-functions.php';

// Get database connection
$conn = getConnection();

// Display subscription data using direct database query
echo "<h2>Subscription Table Data (Direct Query)</h2>";
echo "<table border='1'>";
echo "<tr><th>SUB_ID</th><th>SUB_NAME</th><th>DURATION</th><th>PRICE</th><th>IS_ACTIVE</th></tr>";

$sql = "SELECT * FROM subscription";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . ($row["SUB_ID"] ?? 'NULL') . "</td>";
        echo "<td>" . ($row["SUB_NAME"] ?? 'NULL') . "</td>";
        echo "<td>" . ($row["DURATION"] ?? 'NULL') . "</td>";
        echo "<td>₱" . number_format(($row["PRICE"] ?? 0), 2) . "</td>";
        echo "<td>" . (($row["IS_ACTIVE"] ?? 0) ? "Active" : "Inactive") . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No subscription data found</td></tr>";
}

echo "</table>";

// Display subscription data using the function
echo "<h2>Subscription Table Data (Using Function)</h2>";
$subscriptionPlans = getSubscriptionPlans();

echo "<pre>";
echo "Number of subscription plans returned by function: " . count($subscriptionPlans) . "\n\n";
echo "Data returned by getSubscriptionPlans():\n";
print_r($subscriptionPlans);
echo "</pre>";

echo "<table border='1'>";
echo "<tr><th>SUB_ID</th><th>SUB_NAME</th><th>DURATION</th><th>PRICE</th></tr>";

if (!empty($subscriptionPlans)) {
    foreach ($subscriptionPlans as $plan) {
        echo "<tr>";
        echo "<td>" . ($plan["SUB_ID"] ?? 'NULL') . "</td>";
        echo "<td>" . ($plan["SUB_NAME"] ?? 'NULL') . "</td>";
        echo "<td>" . ($plan["DURATION"] ?? 'NULL') . "</td>";
        echo "<td>₱" . number_format(($plan["PRICE"] ?? 0), 2) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No subscription plans returned by function</td></tr>";
}

echo "</table>";

// Close connection
$conn->close();
?> 