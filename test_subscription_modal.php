<?php
require_once 'config/db_connection.php';
require_once 'functions/transaction-functions.php';

// Set header for proper HTML rendering
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Modal Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        h2 {
            font-size: 18px;
            margin: 15px 0;
            color: #4a5568;
        }
        pre {
            background-color: #f7fafc;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            background-color: white;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container">
        <h1>Subscription Modal Test</h1>
        
        <?php
        // Get subscription plans
        $subscriptionPlans = getSubscriptionPlans();
        ?>
        
        <div class="card">
            <h2>Raw Subscription Data</h2>
            <pre><?php print_r($subscriptionPlans); ?></pre>
        </div>
        
        <div class="card">
            <h2>Admin Version of Dropdown</h2>
            <div class="relative rounded-md shadow-sm">
                <select id="adminSubscriptionSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <option value="">Select Subscription</option>
                    <?php foreach ($subscriptionPlans as $plan): ?>
                        <option value="<?php echo isset($plan['SUB_ID']) ? $plan['SUB_ID'] : ''; ?>" 
                                data-duration="<?php echo isset($plan['DURATION']) ? $plan['DURATION'] : '0'; ?> Days" 
                                data-price="<?php echo isset($plan['PRICE']) ? number_format((float)$plan['PRICE'], 2, '.', '') : '0.00'; ?>">
                            <?php echo isset($plan['SUB_NAME']) ? $plan['SUB_NAME'] : ''; ?> (₱<?php echo isset($plan['PRICE']) ? number_format((float)$plan['PRICE'], 2, '.', ',') : '0.00'; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="card">
            <h2>Staff Version of Dropdown</h2>
            <div class="relative rounded-md shadow-sm">
                <select id="staffSubscriptionSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <option value="">Select Subscription</option>
                    <?php foreach ($subscriptionPlans as $plan): ?>
                        <option value="<?php echo isset($plan['SUB_ID']) ? $plan['SUB_ID'] : ''; ?>" 
                                data-duration="<?php echo isset($plan['DURATION']) ? $plan['DURATION'] : '0'; ?> Days" 
                                data-price="<?php echo isset($plan['PRICE']) ? number_format((float)$plan['PRICE'], 2, '.', '') : '0.00'; ?>">
                            <?php echo isset($plan['SUB_NAME']) ? $plan['SUB_NAME'] : ''; ?> 
                            (<?php echo isset($plan['DURATION']) ? $plan['DURATION'] : '0'; ?> Days - 
                            ₱<?php echo isset($plan['PRICE']) ? number_format((float)$plan['PRICE'], 2, '.', ',') : '0.00'; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="card">
            <h2>Selected Value Information</h2>
            <div id="selectionInfo">Please select a subscription plan to see information</div>
        </div>
    </div>

    <script>
        // Function to display data attributes when an option is selected
        function setupSelect(selectId) {
            const select = document.getElementById(selectId);
            if (select) {
                select.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        const info = {
                            id: selectedOption.value,
                            name: selectedOption.text,
                            duration: selectedOption.getAttribute('data-duration'),
                            price: selectedOption.getAttribute('data-price'),
                            parsedPrice: parseFloat(selectedOption.getAttribute('data-price'))
                        };
                        
                        document.getElementById('selectionInfo').innerHTML = 
                            `<strong>Selected from ${selectId}:</strong><br>` +
                            `ID: ${info.id}<br>` +
                            `Text: ${info.name}<br>` +
                            `Duration: ${info.duration}<br>` +
                            `Price (raw attribute): ${info.price}<br>` +
                            `Price (parsed as float): ${info.parsedPrice}<br>` +
                            `Price (formatted): ₱${parseFloat(info.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    }
                });
            }
        }
        
        // Set up both dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            setupSelect('adminSubscriptionSelect');
            setupSelect('staffSubscriptionSelect');
        });
    </script>
</body>
</html>
