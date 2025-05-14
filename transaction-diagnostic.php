<?php
require_once 'config/db_connection.php';

// Set headers for plain text output
header('Content-Type: text/plain');
echo "Transaction System Diagnostic Tool\n";
echo "================================\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $conn = getConnection();
    if ($conn) {
        echo "   SUCCESS: Database connection established.\n\n";
    } else {
        echo "   FAILED: Could not connect to database.\n\n";
        exit;
    }
    
    // Check tables existence
    echo "2. Checking required tables...\n";
    $requiredTables = ['transaction', 'member', 'subscription', 'payment', 'member_subscription', 'transaction_log'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "   SUCCESS: All required tables exist.\n\n";
    } else {
        echo "   WARNING: Missing tables: " . implode(", ", $missingTables) . "\n\n";
    }
    
    // Describe transaction table structure
    echo "3. Checking transaction table structure...\n";
    $result = $conn->query("DESCRIBE transaction");
    if ($result) {
        echo "   Transaction table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - {$row['Field']}: {$row['Type']} " . 
                 ($row['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . 
                 (isset($row['Default']) ? " DEFAULT '{$row['Default']}'" : "") . 
                 ($row['Key'] === 'PRI' ? " (PRIMARY KEY)" : "") . "\n";
        }
        echo "\n";
    } else {
        echo "   ERROR: Could not check transaction table structure.\n\n";
    }

    // Describe member_subscription table structure
    echo "4. Checking member_subscription table structure...\n";
    $result = $conn->query("DESCRIBE member_subscription");
    if ($result) {
        echo "   Member_subscription table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - {$row['Field']}: {$row['Type']} " . 
                 ($row['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . 
                 (isset($row['Default']) ? " DEFAULT '{$row['Default']}'" : "") . 
                 ($row['Key'] === 'PRI' ? " (PRIMARY KEY)" : "") . "\n";
        }
        echo "\n";
    } else {
        echo "   ERROR: Could not check member_subscription table structure.\n\n";
    }
    
    // Check recent transactions
    echo "5. Checking recent transactions...\n";
    $transQuery = "SELECT t.TRANSACTION_ID, t.MEMBER_ID, t.SUB_ID, t.PAYMENT_ID, 
                         t.TRANSAC_DATE, s.SUB_NAME, m.MEMBER_FNAME, m.MEMBER_LNAME,
                         p.PAY_METHOD
                  FROM `transaction` t
                  LEFT JOIN member m ON t.MEMBER_ID = m.MEMBER_ID
                  LEFT JOIN subscription s ON t.SUB_ID = s.SUB_ID
                  LEFT JOIN payment p ON t.PAYMENT_ID = p.PAYMENT_ID
                  ORDER BY t.TRANSACTION_ID DESC
                  LIMIT 10";
    
    $result = $conn->query($transQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "   Found " . $result->num_rows . " recent transactions:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - ID: " . $row['TRANSACTION_ID'] . " | Date: " . $row['TRANSAC_DATE'] . 
                 " | Member: " . $row['MEMBER_FNAME'] . " " . $row['MEMBER_LNAME'] . 
                 " | Sub: " . $row['SUB_NAME'] . " | Payment: " . $row['PAY_METHOD'] . "\n";
        }
        echo "\n";
    } else {
        echo "   WARNING: No recent transactions found.\n\n";
    }
    
    // Check member_subscription table
    echo "6. Checking recent subscription records...\n";
    $subQuery = "SELECT ms.MEMBER_ID, ms.SUB_ID, ms.START_DATE, ms.END_DATE, ms.IS_ACTIVE,
                       m.MEMBER_FNAME, m.MEMBER_LNAME, s.SUB_NAME
                FROM member_subscription ms
                LEFT JOIN member m ON ms.MEMBER_ID = m.MEMBER_ID
                LEFT JOIN subscription s ON ms.SUB_ID = s.SUB_ID
                ORDER BY ms.START_DATE DESC
                LIMIT 10";
    
    $result = $conn->query($subQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "   Found " . $result->num_rows . " recent subscription records:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - Member: " . $row['MEMBER_FNAME'] . " " . $row['MEMBER_LNAME'] . 
                 " | Sub: " . $row['SUB_NAME'] . 
                 " | Period: " . $row['START_DATE'] . " to " . $row['END_DATE'] . 
                 " | Active: " . ($row['IS_ACTIVE'] ? "Yes" : "No") . "\n";
        }
        echo "\n";
    } else {
        echo "   WARNING: No subscription records found.\n\n";
    }
    
    // Check for active subscriptions
    echo "7. Checking active subscriptions...\n";
    $activeSubsQuery = "SELECT COUNT(*) as active_count FROM member_subscription WHERE IS_ACTIVE = 1";
    $result = $conn->query($activeSubsQuery);
    $activeCount = $result->fetch_assoc()['active_count'];
    echo "   Found $activeCount active subscriptions.\n\n";
    
    // Look for recent errors in logs
    echo "8. Checking recent errors in transaction operations...\n";
    $errorLogQuery = "SELECT * FROM transaction_log WHERE OPERATION LIKE '%ERROR%' OR OPERATION LIKE '%FAIL%' ORDER BY MODIFIEDDATE DESC LIMIT 5";
    $result = $conn->query($errorLogQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "   Found " . $result->num_rows . " error logs:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - TransactionID: " . $row['TRANSACTION_ID'] . " | Operation: " . $row['OPERATION'] . " | Date: " . $row['MODIFIEDDATE'] . "\n";
        }
    } else {
        echo "   No recent error logs found in transaction_log table.\n";
    }
    
    // Test subscription creation function directly
    echo "\n9. Testing transaction creation function...\n";
    echo "   Note: This is a test only and won't create an actual transaction.\n\n";
    
    // Find a valid member, subscription, and payment method for testing
    $memberQuery = "SELECT MEMBER_ID FROM member WHERE IS_ACTIVE = 1 LIMIT 1";
    $subQuery = "SELECT SUB_ID FROM subscription WHERE IS_ACTIVE = 1 LIMIT 1";
    $payQuery = "SELECT PAYMENT_ID FROM payment WHERE IS_ACTIVE = 1 LIMIT 1";
    
    $memberResult = $conn->query($memberQuery);
    $subResult = $conn->query($subQuery);
    $payResult = $conn->query($payQuery);
    
    if ($memberResult->num_rows > 0 && $subResult->num_rows > 0 && $payResult->num_rows > 0) {
        $memberId = $memberResult->fetch_assoc()['MEMBER_ID'];
        $subId = $subResult->fetch_assoc()['SUB_ID'];
        $payId = $payResult->fetch_assoc()['PAYMENT_ID'];
        
        echo "   Test parameters ready:\n";
        echo "   - Member ID: $memberId\n";
        echo "   - Subscription ID: $subId\n";
        echo "   - Payment ID: $payId\n\n";
        
        // Check if createTransaction function exists
        if (function_exists('createTransaction')) {
            echo "   Function 'createTransaction' exists. Test would be valid.\n";
        } else {
            echo "   WARNING: Function 'createTransaction' not defined. Include transaction-functions.php to test it directly.\n";
        }
    } else {
        echo "   ERROR: Could not find valid test data (active member, subscription, and payment method).\n";
    }

    echo "\nDiagnostic completed.\n";
    echo "If you're experiencing issues with transactions not being saved, please check:\n";
    echo "1. Database connection and permissions\n";
    echo "2. Table structures and constraints\n";
    echo "3. PHP error logs for detailed error messages\n";
    echo "4. Transaction function implementation in transaction-functions.php\n";
    echo "5. Form submission and data handling in transaction-management.js\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
