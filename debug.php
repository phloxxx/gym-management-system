<?php
header('Content-Type: text/html');

// Include the database connection
require_once 'config/db_connection.php';

echo "<h1>Subscription Debugging Tool</h1>";

// Connect to the database
$conn = getConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<p>Database connection successful</p>";

// Get subscription data
$sql = "SELECT * FROM subscription";
$result = $conn->query($sql);

echo "<h2>Raw Database Data</h2>";

if ($result->num_rows > 0) {
    echo "<p>Found " . $result->num_rows . " subscriptions:</p>";
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Duration</th><th>Price Raw</th><th>Price Type</th><th>Price Cast</th><th>Price Formatted</th><th>Active</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['SUB_ID'] . "</td>";
        echo "<td>" . $row['SUB_NAME'] . "</td>";
        echo "<td>" . $row['DURATION'] . "</td>";
        echo "<td>" . $row['PRICE'] . "</td>";
        echo "<td>" . gettype($row['PRICE']) . "</td>";
        echo "<td>" . (float)$row['PRICE'] . "</td>";
        echo "<td>" . number_format((float)$row['PRICE'], 2, '.', '') . "</td>";
        echo "<td>" . $row['IS_ACTIVE'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Reset result pointer
    $result->data_seek(0);
    
    echo "<h2>Generated Option Elements</h2>";
    echo "<p>This shows how HTML options would be generated:</p>";
    
    echo "<select>";
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['SUB_ID'] . '" ' .
            'data-duration="' . $row['DURATION'] . '" ' . 
            'data-price="' . $row['PRICE'] . '">' .
            $row['SUB_NAME'] . ' ($' . number_format((float)$row['PRICE'], 2, '.', ',') . ')' .
            '</option>';
    }
    echo "</select>";
    
    echo "<h2>HTML Source of Options</h2>";
    echo "<pre>";
    
    // Reset result pointer again
    $result->data_seek(0);
    
    while($row = $result->fetch_assoc()) {
        $option = '<option value="' . $row['SUB_ID'] . '" ' .
            'data-duration="' . $row['DURATION'] . '" ' . 
            'data-price="' . $row['PRICE'] . '">' .
            $row['SUB_NAME'] . ' ($' . number_format((float)$row['PRICE'], 2, '.', ',') . ')' .
            '</option>';
        
        echo htmlspecialchars($option) . "\n";
    }
    
    echo "</pre>";
    
} else {
    echo "<p>0 results found in subscription table</p>";
}

$conn->close();
echo "<p>Connection closed</p>";

// Add JavaScript to test data attribute access
echo "<script>
window.onload = function() {
    const select = document.querySelector('select');
    if (select) {
        for (let i = 0; i < select.options.length; i++) {
            const option = select.options[i];
            console.log('Option ' + i + ':', {
                text: option.text,
                price: option.getAttribute('data-price'),
                priceType: typeof option.getAttribute('data-price'),
                priceAsNumber: parseFloat(option.getAttribute('data-price'))
            });
        }
    }
}
</script>";
?> 