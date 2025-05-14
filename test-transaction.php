<?php
require_once 'config/db_connection.php';
require_once 'functions/transaction-functions.php';

// Set output format
$format = isset($_GET['format']) && $_GET['format'] === 'html' ? 'html' : 'text';

if ($format === 'html') {
    header('Content-Type: text/html');
    echo "<!DOCTYPE html><html><head><title>Transaction Test</title>";
    echo "<style>body{font-family:monospace;margin:20px}h1{color:#333}.success{color:green}.error{color:red}.info{color:blue}</style>";
    echo "</head><body><h1>Transaction Test Tool</h1><pre>";
} else {
    header('Content-Type: text/plain');
    echo "Transaction Test Tool\n";
    echo "====================\n\n";
}

function output($message, $type = 'info') {
    global $format;
    if ($format === 'html') {
        echo "<div class=\"$type\">$message</div>";
    } else {
        echo $message . "\n";
    }
}

try {
    // Step 1: Test database connection
    output("1. Testing database connection...");
    $conn = getConnection();
    if ($conn) {
        output("   SUCCESS: Database connection established.", "success");
    } else {
        output("   FAILED: Could not connect to database.", "error");
        exit;
    }
    
    // Define test parameters
    $testMode = isset($_GET['mode']) ? $_GET['mode'] : 'check';
    
    // Get test data
    $memberId = isset($_GET['member_id']) ? intval($_GET['member_id']) : null;
    $subId = isset($_GET['sub_id']) ? intval($_GET['sub_id']) : null;
    $payId = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : null;
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $isRenewal = isset($_GET['is_renewal']) && $_GET['is_renewal'] === '1';
    $previousSubId = isset($_GET['previous_sub_id']) ? intval($_GET['previous_sub_id']) : null;
    
    output("\n2. Test parameters:");
    output("   - Mode: $testMode");
    output("   - Member ID: " . ($memberId ? $memberId : "Not specified"));
    output("   - Subscription ID: " . ($subId ? $subId : "Not specified"));
    output("   - Payment ID: " . ($payId ? $payId : "Not specified"));
    output("   - Start Date: $startDate");
    output("   - Is Renewal: " . ($isRenewal ? "Yes" : "No"));
    output("   - Previous Sub ID: " . ($previousSubId !== null ? $previousSubId : "None"));
    
    // If any required parameter is missing, find some valid test data
    if (!$memberId || !$subId || !$payId) {
        output("\n3. Finding valid test data...");
        
        if (!$memberId) {
            $memberQuery = "SELECT MEMBER_ID, CONCAT(MEMBER_FNAME, ' ', MEMBER_LNAME) as name 
                          FROM member WHERE IS_ACTIVE = 1 LIMIT 5";
            $memberResult = $conn->query($memberQuery);
            
            if ($memberResult && $memberResult->num_rows > 0) {
                output("   Available members:");
                while ($row = $memberResult->fetch_assoc()) {
                    output("   - ID: {$row['MEMBER_ID']} | Name: {$row['name']}");
                    if (!$memberId) $memberId = $row['MEMBER_ID'];
                }
            } else {
                output("   ERROR: No active members found in database.", "error");
            }
        }
        
        if (!$subId) {
            $subQuery = "SELECT SUB_ID, SUB_NAME, DURATION, PRICE FROM subscription WHERE IS_ACTIVE = 1 LIMIT 5";
            $subResult = $conn->query($subQuery);
            
            if ($subResult && $subResult->num_rows > 0) {
                output("   Available subscriptions:");
                while ($row = $subResult->fetch_assoc()) {
                    output("   - ID: {$row['SUB_ID']} | Name: {$row['SUB_NAME']} | Duration: {$row['DURATION']} days | Price: {$row['PRICE']}");
                    if (!$subId) $subId = $row['SUB_ID'];
                }
            } else {
                output("   ERROR: No active subscriptions found in database.", "error");
            }
        }
        
        if (!$payId) {
            $payQuery = "SELECT PAYMENT_ID, PAY_METHOD FROM payment WHERE IS_ACTIVE = 1 LIMIT 5";
            $payResult = $conn->query($payQuery);
            
            if ($payResult && $payResult->num_rows > 0) {
                output("   Available payment methods:");
                while ($row = $payResult->fetch_assoc()) {
                    output("   - ID: {$row['PAYMENT_ID']} | Method: {$row['PAY_METHOD']}");
                    if (!$payId) $payId = $row['PAYMENT_ID'];
                }
            } else {
                output("   ERROR: No active payment methods found in database.", "error");
            }
        }
    }
    
    // Calculate end date based on subscription duration
    $durationQuery = "SELECT DURATION FROM subscription WHERE SUB_ID = ?";
    $stmt = $conn->prepare($durationQuery);
    $stmt->bind_param("i", $subId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $duration = $result->fetch_assoc()['DURATION'];
        $startDateTime = new DateTime($startDate);
        $endDateTime = clone $startDateTime;
        $endDateTime->add(new DateInterval("P{$duration}D"));
        $endDate = $endDateTime->format('Y-m-d');
        
        output("   - Calculated End Date: $endDate");
    } else {
        output("   ERROR: Could not determine subscription duration.", "error");
        exit;
    }
    
    // In check mode, just validate the parameters
    if ($testMode === 'check') {
        output("\n4. Validating parameters...");
        
        // Check member exists and is active
        $memberCheckQuery = "SELECT IS_ACTIVE FROM member WHERE MEMBER_ID = ?";
        $stmt = $conn->prepare($memberCheckQuery);
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $isActive = $result->fetch_assoc()['IS_ACTIVE'];
            if ($isActive) {
                output("   - Member ID $memberId is valid and active.", "success");
            } else {
                output("   - Member ID $memberId exists but is INACTIVE. This may cause issues.", "error");
            }
        } else {
            output("   - Member ID $memberId does not exist in the database.", "error");
        }
        
        // Check subscription exists and is active
        $subCheckQuery = "SELECT IS_ACTIVE FROM subscription WHERE SUB_ID = ?";
        $stmt = $conn->prepare($subCheckQuery);
        $stmt->bind_param("i", $subId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $isActive = $result->fetch_assoc()['IS_ACTIVE'];
            if ($isActive) {
                output("   - Subscription ID $subId is valid and active.", "success");
            } else {
                output("   - Subscription ID $subId exists but is INACTIVE. This may cause issues.", "error");
            }
        } else {
            output("   - Subscription ID $subId does not exist in the database.", "error");
        }
        
        // Check payment method exists and is active
        $payCheckQuery = "SELECT IS_ACTIVE FROM payment WHERE PAYMENT_ID = ?";
        $stmt = $conn->prepare($payCheckQuery);
        $stmt->bind_param("i", $payId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $isActive = $result->fetch_assoc()['IS_ACTIVE'];
            if ($isActive) {
                output("   - Payment ID $payId is valid and active.", "success");
            } else {
                output("   - Payment ID $payId exists but is INACTIVE. This may cause issues.", "error");
            }
        } else {
            output("   - Payment ID $payId does not exist in the database.", "error");
        }
        
        // Check for existing transactions today
        $existingQuery = "SELECT COUNT(*) as count FROM transaction 
                         WHERE MEMBER_ID = ? AND SUB_ID = ? AND DATE(TRANSAC_DATE) = CURRENT_DATE()";
        $stmt = $conn->prepare($existingQuery);
        $stmt->bind_param("ii", $memberId, $subId);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            output("   - WARNING: A transaction for this member and subscription already exists today.", "error");
        } else {
            output("   - No existing transaction today for this member and subscription.", "success");
        }
        
        // Check for overlapping subscriptions
        $overlapQuery = "SELECT * FROM member_subscription 
                        WHERE MEMBER_ID = ? AND IS_ACTIVE = 1
                        AND ((? BETWEEN START_DATE AND END_DATE) OR
                            (? BETWEEN START_DATE AND END_DATE))";
        $stmt = $conn->prepare($overlapQuery);
        $stmt->bind_param("iss", $memberId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            output("   - WARNING: Found overlapping active subscriptions for this member.", "error");
            while ($row = $result->fetch_assoc()) {
                output("     * Subscription ID {$row['SUB_ID']} from {$row['START_DATE']} to {$row['END_DATE']}");
            }
        } else {
            output("   - No overlapping subscriptions found.", "success");
        }
        
        output("\nAll parameters validated. Use 'mode=create' to actually create the transaction.");
    } 
    // In create mode, actually create the transaction
    else if ($testMode === 'create') {
        output("\n4. Attempting to create transaction...");
        
        // Create the transaction by calling createTransaction function directly
        $result = createTransaction(
            $memberId, 
            $subId, 
            $payId, 
            $startDate, 
            $endDate, 
            $isRenewal,
            $isRenewal ? $previousSubId : null
        );
        
        if ($result) {
            output("   SUCCESS: Transaction created successfully!", "success");
            
            // Get the details of the created transaction
            $transQuery = "SELECT t.TRANSACTION_ID, t.TRANSAC_DATE, 
                                 m.MEMBER_FNAME, m.MEMBER_LNAME, 
                                 s.SUB_NAME, p.PAY_METHOD
                          FROM transaction t
                          JOIN member m ON t.MEMBER_ID = m.MEMBER_ID
                          JOIN subscription s ON t.SUB_ID = s.SUB_ID
                          JOIN payment p ON t.PAYMENT_ID = p.PAYMENT_ID
                          WHERE t.MEMBER_ID = ? AND t.SUB_ID = ?
                          ORDER BY t.TRANSACTION_ID DESC LIMIT 1";
            
            $stmt = $conn->prepare($transQuery);
            $stmt->bind_param("ii", $memberId, $subId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $transaction = $result->fetch_assoc();
                output("\n5. Transaction details:");
                output("   - ID: " . $transaction['TRANSACTION_ID']);
                output("   - Date: " . $transaction['TRANSAC_DATE']);
                output("   - Member: " . $transaction['MEMBER_FNAME'] . " " . $transaction['MEMBER_LNAME']);
                output("   - Subscription: " . $transaction['SUB_NAME']);
                output("   - Payment Method: " . $transaction['PAY_METHOD']);
            } else {
                output("   WARNING: Transaction was created but could not retrieve details.", "error");
            }
            
            // Check member_subscription record
            $subRecordQuery = "SELECT * FROM member_subscription 
                               WHERE MEMBER_ID = ? AND SUB_ID = ? AND START_DATE = ? AND END_DATE = ?";
            
            $stmt = $conn->prepare($subRecordQuery);
            $stmt->bind_param("iiss", $memberId, $subId, $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $subscription = $result->fetch_assoc();
                output("\n6. Subscription record details:");
                output("   - Start Date: " . $subscription['START_DATE']);
                output("   - End Date: " . $subscription['END_DATE']);
                output("   - Status: " . ($subscription['IS_ACTIVE'] ? "Active" : "Inactive"));
                
                if (!$subscription['IS_ACTIVE']) {
                    output("   WARNING: Subscription record exists but is not active!", "error");
                }
            } else {
                output("\n6. ERROR: Could not find subscription record in member_subscription table!", "error");
                output("   This indicates a database inconsistency - the transaction exists without a corresponding subscription.");
            }
        } else {
            output("   ERROR: Transaction creation failed.", "error");
            output("   Check PHP error logs for more details.");
        }
    } else {
        output("\n4. Invalid test mode specified. Use 'check' or 'create'.", "error");
    }

} catch (Exception $e) {
    output("\nERROR: " . $e->getMessage(), "error");
    output("Stack trace: " . $e->getTraceAsString());
}

// Provide help and link to create mode if in check mode
if ($testMode === 'check') {
    $createUrl = $_SERVER['PHP_SELF'] . '?mode=create';
    if ($memberId) $createUrl .= "&member_id=$memberId";
    if ($subId) $createUrl .= "&sub_id=$subId";
    if ($payId) $createUrl .= "&payment_id=$payId";
    $createUrl .= "&start_date=$startDate";
    if ($isRenewal) $createUrl .= "&is_renewal=1";
    if ($previousSubId) $createUrl .= "&previous_sub_id=$previousSubId";
    
    if ($format === 'html') {
        output("\n<a href=\"$createUrl\">Click here to run the actual transaction creation test</a>");
    } else {
        output("\nTo run the actual transaction creation test, visit:");
        output("$createUrl");
    }
}

if ($format === 'html') {
    echo "</pre></body></html>";
}
?>
