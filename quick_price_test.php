<?php
// Quick subscription price test - this is a very simple script
// to test subscription price display

// Required files
require_once 'config/db_connection.php';

// Connect directly to the database
$conn = getConnection();

// Set header
header('Content-Type: text/html; charset=utf-8');

// Simple HTML page
echo "<!DOCTYPE html>
<html>
<head>
    <title>Subscription Price Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Subscription Price Test</h1>";

// Simple direct query to the database
$sql = "SELECT SUB_ID, SUB_NAME, DURATION, PRICE, IS_ACTIVE FROM subscription WHERE IS_ACTIVE = 1 ORDER BY PRICE";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<h2>Raw Database Query Results</h2>";
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Duration</th>
                <th>Price (Raw)</th>
                <th>Price as Float</th>
                <th>Formatted Price (₱)</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $price_as_float = (float)$row['PRICE'];
        $formatted_price = number_format($price_as_float, 2, '.', ',');
        
        echo "<tr>
                <td>{$row['SUB_ID']}</td>
                <td>{$row['SUB_NAME']}</td>
                <td>{$row['DURATION']}</td>
                <td>{$row['PRICE']}</td>
                <td>$price_as_float</td>
                <td>₱$formatted_price</td>
              </tr>";
    }
    
    echo "</table>";
    
    // Reset the result pointer
    $result->data_seek(0);
    
    echo "<h2>Dropdown Test</h2>";
    echo "<select style='padding: 10px; width: 300px;'>";
    echo "<option value=''>Select a plan</option>";
    
    while ($row = $result->fetch_assoc()) {
        $price_as_float = (float)$row['PRICE'];
        $formatted_price = number_format($price_as_float, 2, '.', ',');
        
        echo "<option value='{$row['SUB_ID']}' data-price='$price_as_float' data-duration='{$row['DURATION']} Days'>";
        echo "{$row['SUB_NAME']} (₱$formatted_price)";
        echo "</option>";
    }
    
    echo "</select>";
} else {
    echo "<p>No subscription data found</p>";
}

echo "</body></html>";

// Close the connection
$conn->close();
?>
