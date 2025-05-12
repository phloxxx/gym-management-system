<?php
// Set page header
header('Content-Type: text/html; charset=utf-8');

// Include necessary files
require_once 'config/db_connection.php';

// Connect to database
$conn = getConnection();

// Get subscription data
$subscriptionPlans = [];
try {
    $sql = "SELECT SUB_ID, SUB_NAME, DURATION, PRICE, IS_ACTIVE FROM subscription WHERE IS_ACTIVE = 1 ORDER BY PRICE";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Force type conversion
            $row['SUB_ID'] = (int)$row['SUB_ID'];
            $row['DURATION'] = (int)$row['DURATION'];
            $row['PRICE'] = (float)$row['PRICE']; 
            $row['IS_ACTIVE'] = (bool)$row['IS_ACTIVE'];
            $subscriptionPlans[] = $row;
        }
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixed Subscription Dropdown Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { font-size: 24px; margin-bottom: 20px; }
        h2 { font-size: 18px; margin: 20px 0 10px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; margin-bottom: 20px; }
        .dropdown { margin-bottom: 20px; }
        .display-info { background: #f0f7ff; border: 1px solid #cce3ff; padding: 15px; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container">
        <h1>Fixed Subscription Dropdown Test</h1>
        
        <h2>Raw Subscription Data</h2>
        <pre><?php echo htmlspecialchars(print_r($subscriptionPlans, true)); ?></pre>
        
        <h2>Subscription Dropdown with Fixed HTML Attributes</h2>
        <div class="dropdown">
            <select id="testSubscriptionSelect" class="w-full px-4 py-2 border border-gray-300 rounded">
                <option value="">Select Subscription</option>
                <?php foreach ($subscriptionPlans as $plan): 
                    $price = isset($plan['PRICE']) ? $plan['PRICE'] : 0;
                    $formattedPrice = number_format((float)$price, 2, '.', ',');
                    // Properly concatenate the string within PHP
                    $duration = $plan['DURATION'] . ' Days';
                ?>
                    <option value="<?php echo $plan['SUB_ID']; ?>" 
                            data-duration="<?php echo $duration; ?>" 
                            data-price="<?php echo number_format((float)$price, 2, '.', ''); ?>">
                        <?php echo $plan['SUB_NAME']; ?> (₱<?php echo $formattedPrice; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div id="displayInfo" class="display-info hidden">
            <h3 class="text-lg font-medium mb-2">Selected Subscription Details</h3>
            <div id="selectedDetails"></div>
        </div>
    </div>

    <script>
        // Add event listener to the dropdown
        document.getElementById('testSubscriptionSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const displayInfo = document.getElementById('displayInfo');
            const selectedDetails = document.getElementById('selectedDetails');
            
            if (this.value) {
                // Get data from selected option
                const id = selectedOption.value;
                const name = selectedOption.text;
                const duration = selectedOption.getAttribute('data-duration');
                const price = selectedOption.getAttribute('data-price');
                
                // Display the data
                displayInfo.classList.remove('hidden');
                selectedDetails.innerHTML = `
                    <p><strong>ID:</strong> ${id}</p>
                    <p><strong>Name:</strong> ${name}</p>
                    <p><strong>Duration:</strong> ${duration}</p>
                    <p><strong>Price:</strong> ₱${parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                    <p><strong>Raw data-duration attribute:</strong> "${duration}"</p>
                    <p><strong>Raw data-price attribute:</strong> "${price}"</p>
                `;
            } else {
                displayInfo.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
