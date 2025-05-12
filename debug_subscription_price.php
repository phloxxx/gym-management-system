<?php
// Debugging script to check subscription data
header('Content-Type: text/html; charset=utf-8');
require_once 'config/db_connection.php';

// Connect directly to the database
$conn = getConnection();

// Test raw query
echo "<h1>Database Connection Test</h1>";
echo "<h2>Raw Subscription Data from Direct Query</h2>";

$sql = "SELECT SUB_ID, SUB_NAME, DURATION, PRICE, IS_ACTIVE FROM subscription WHERE IS_ACTIVE = 1 ORDER BY PRICE";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>SUB_ID</th><th>SUB_NAME</th><th>DURATION</th><th>PRICE (Raw)</th><th>PRICE (Type)</th><th>PRICE (Cast as Float)</th><th>IS_ACTIVE</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['SUB_ID'] . "</td>";
        echo "<td>" . $row['SUB_NAME'] . "</td>";
        echo "<td>" . $row['DURATION'] . "</td>";
        echo "<td>" . $row['PRICE'] . "</td>";
        echo "<td>" . gettype($row['PRICE']) . "</td>";
        echo "<td>" . (float)$row['PRICE'] . "</td>";
        echo "<td>" . ($row['IS_ACTIVE'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No subscription data found or query error.</p>";
}

echo "<hr>";

// Now test our function
require_once 'functions/transaction-functions.php';
$plans = getSubscriptionPlans();

echo "<h2>Data from getSubscriptionPlans() Function</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>SUB_ID</th><th>SUB_NAME</th><th>DURATION</th><th>PRICE (Raw)</th><th>PRICE (Type)</th><th>PRICE (Cast to Float)</th><th>IS_ACTIVE</th></tr>";

foreach($plans as $plan) {
    echo "<tr>";
    echo "<td>" . $plan['SUB_ID'] . "</td>";
    echo "<td>" . $plan['SUB_NAME'] . "</td>";
    echo "<td>" . $plan['DURATION'] . "</td>";
    echo "<td>" . $plan['PRICE'] . "</td>";
    echo "<td>" . gettype($plan['PRICE']) . "</td>";
    echo "<td>" . (float)$plan['PRICE'] . "</td>";
    echo "<td>" . ($plan['IS_ACTIVE'] ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Testing Dropdown Rendering</h2>";
echo "<select style='width: 300px; padding: 10px;'>";
echo "<option value=''>Select a plan</option>";

foreach($plans as $plan) {
    echo "<option value='" . $plan['SUB_ID'] . "' data-price='" . number_format((float)$plan['PRICE'], 2, '.', '') . "' data-duration='" . $plan['DURATION'] . " Days'>";
    echo $plan['SUB_NAME'] . " (₱" . number_format((float)$plan['PRICE'], 2, '.', ',') . ")";
    echo "</option>";
}

echo "</select>";

// Close connection
$conn->close();
?>
