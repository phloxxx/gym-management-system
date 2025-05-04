<?php
require_once __DIR__ . '/../config/db_connection.php';

function getPrograms() {
    $conn = getConnection();
    $programs = [];
    
    try {
        $sql = "SELECT PROGRAM_ID, PROGRAM_NAME FROM program WHERE IS_ACTIVE = 1 ORDER BY PROGRAM_NAME";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $programs[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching programs: " . $e->getMessage());
    } finally {
        $conn->close();
    }
    
    return $programs;
}

function getSubscriptionPlans() {
    $conn = getConnection();
    $plans = [];
    
    try {
        $sql = "SELECT SUB_ID, SUB_NAME, DURATION, PRICE FROM subscription WHERE IS_ACTIVE = 1 ORDER BY PRICE";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $plans[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching subscription plans: " . $e->getMessage());
    } finally {
        $conn->close();
    }
    
    return $plans;
}

function getPaymentMethods() {
    $conn = getConnection();
    $methods = [];
    
    try {
        $sql = "SELECT PAYMENT_ID, PAY_METHOD FROM payment WHERE IS_ACTIVE = 1 ORDER BY PAY_METHOD";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $methods[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching payment methods: " . $e->getMessage());
    } finally {
        $conn->close();
    }
    
    return $methods;
}

function getTransactionSummary() {
    $conn = getConnection();
    $summary = [
        'total' => 0,
        'revenue' => 0,
        'recent' => 0,
        'expiring' => 0,
        'growth' => [
            'transactions' => 0,
            'revenue' => 0
        ]
    ];
    
    try {
        // Get total transactions and revenue
        $sql1 = "SELECT COUNT(*) as total, COALESCE(SUM(s.PRICE), 0) as revenue 
                 FROM transaction t 
                 JOIN subscription s ON t.SUB_ID = s.SUB_ID";
        $result1 = $conn->query($sql1);
        if ($row = $result1->fetch_assoc()) {
            $summary['total'] = $row['total'];
            $summary['revenue'] = $row['revenue'];
        }

        // Get recent transactions (last 30 days)
        $sql2 = "SELECT COUNT(*) as recent FROM transaction 
                 WHERE TRANSAC_DATE >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
        $result2 = $conn->query($sql2);
        if ($row = $result2->fetch_assoc()) {
            $summary['recent'] = $row['recent'];
        }

        // Get expiring subscriptions (next 7 days)
        $sql3 = "SELECT COUNT(*) as expiring FROM member_subscription 
                 WHERE END_DATE BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
                 AND IS_ACTIVE = 1";
        $result3 = $conn->query($sql3);
        if ($row = $result3->fetch_assoc()) {
            $summary['expiring'] = $row['expiring'];
        }

        // Calculate growth (compare current month with previous month)
        $sql4 = "SELECT 
                    ((THIS_MONTH_COUNT - LAST_MONTH_COUNT) / LAST_MONTH_COUNT * 100) as trans_growth,
                    ((THIS_MONTH_REV - LAST_MONTH_REV) / LAST_MONTH_REV * 100) as rev_growth
                 FROM (
                    SELECT 
                        COUNT(CASE WHEN MONTH(TRANSAC_DATE) = MONTH(CURRENT_DATE) THEN 1 END) as THIS_MONTH_COUNT,
                        COUNT(CASE WHEN MONTH(TRANSAC_DATE) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) THEN 1 END) as LAST_MONTH_COUNT,
                        COALESCE(SUM(CASE WHEN MONTH(TRANSAC_DATE) = MONTH(CURRENT_DATE) THEN s.PRICE END), 0) as THIS_MONTH_REV,
                        COALESCE(SUM(CASE WHEN MONTH(TRANSAC_DATE) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) THEN s.PRICE END), 0) as LAST_MONTH_REV
                    FROM transaction t
                    JOIN subscription s ON t.SUB_ID = s.SUB_ID
                 ) growth";
        $result4 = $conn->query($sql4);
        if ($row = $result4->fetch_assoc()) {
            $summary['growth']['transactions'] = round($row['trans_growth'] ?? 0);
            $summary['growth']['revenue'] = round($row['rev_growth'] ?? 0);
        }
    } catch (Exception $e) {
        error_log("Error fetching transaction summary: " . $e->getMessage());
    } finally {
        $conn->close();
    }
    
    return $summary;
}

function getActiveSubscriptions() {
    $conn = getConnection();
    $subscriptions = [];
    
    try {
        $sql = "SELECT m.MEMBER_ID, m.MEMBER_FNAME, m.MEMBER_LNAME, 
                       s.SUB_NAME, ms.START_DATE, ms.END_DATE, ms.IS_ACTIVE,
                       t.TRANSAC_DATE as PAID_DATE
                FROM member m
                JOIN member_subscription ms ON m.MEMBER_ID = ms.MEMBER_ID
                JOIN subscription s ON ms.SUB_ID = s.SUB_ID
                LEFT JOIN transaction t ON m.MEMBER_ID = t.MEMBER_ID AND ms.SUB_ID = t.SUB_ID
                ORDER BY ms.END_DATE";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $subscriptions[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching active subscriptions: " . $e->getMessage());
    } finally {
        $conn->close();
    }
    
    return $subscriptions;
}

function searchMembers($searchTerm) {
    $conn = getConnection();
    $members = [];
    
    try {
        $sql = "SELECT m.MEMBER_ID, m.MEMBER_FNAME, m.MEMBER_LNAME, m.EMAIL, p.PROGRAM_NAME 
                FROM member m
                JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
                WHERE m.IS_ACTIVE = 1 
                AND (m.MEMBER_FNAME LIKE ? OR m.MEMBER_LNAME LIKE ? OR m.EMAIL LIKE ?)
                LIMIT 5";
                
        $stmt = $conn->prepare($sql);
        $searchPattern = "%{$searchTerm}%";
        $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $members[] = [
                'id' => $row['MEMBER_ID'],
                'name' => $row['MEMBER_FNAME'] . ' ' . $row['MEMBER_LNAME'],
                'email' => $row['EMAIL'],
                'program' => $row['PROGRAM_NAME'],
                'initials' => strtoupper(substr($row['MEMBER_FNAME'], 0, 1) . substr($row['MEMBER_LNAME'], 0, 1))
            ];
        }
    } catch (Exception $e) {
        error_log("Error searching members: " . $e->getMessage());
    } finally {
        $conn->close();
    }
    
    return $members;
}

function createTransaction($memberId, $subscriptionId, $paymentId, $startDate, $endDate, $isRenewal = false, $previousSubId = null) {
    $conn = getConnection();
    $success = false;
    
    try {
        // Log the parameters
        error_log("Creating transaction with: memberId=$memberId, subId=$subscriptionId, paymentId=$paymentId, startDate=$startDate, endDate=$endDate, isRenewal=$isRenewal, previousSubId=$previousSubId");
        
        // Double-check that the member exists to avoid foreign key errors
        $checkMember = $conn->prepare("SELECT MEMBER_ID FROM member WHERE MEMBER_ID = ?");
        $checkMember->bind_param("i", $memberId);
        $checkMember->execute();
        $memberResult = $checkMember->get_result();
        
        if ($memberResult->num_rows === 0) {
            throw new Exception("Member ID $memberId does not exist. Cannot create transaction.");
        }
        
        // Also check subscription and payment method
        $checkSub = $conn->prepare("SELECT SUB_ID FROM subscription WHERE SUB_ID = ?");
        $checkSub->bind_param("i", $subscriptionId);
        $checkSub->execute();
        if ($checkSub->get_result()->num_rows === 0) {
            throw new Exception("Subscription ID $subscriptionId does not exist.");
        }
        
        $checkPay = $conn->prepare("SELECT PAYMENT_ID FROM payment WHERE PAYMENT_ID = ?");
        $checkPay->bind_param("i", $paymentId);
        $checkPay->execute();
        if ($checkPay->get_result()->num_rows === 0) {
            throw new Exception("Payment ID $paymentId does not exist.");
        }
        
        $conn->begin_transaction();
        
        // If this is a renewal and we have a previous subscription ID, reactivate that subscription
        if ($isRenewal && $previousSubId) {
            // First check if the previous sub ID matches the new one
            if ($previousSubId == $subscriptionId) {
                // Update the existing subscription to be active again with new dates
                $updateSql = "UPDATE member_subscription 
                              SET IS_ACTIVE = 1, 
                                  START_DATE = ?, 
                                  END_DATE = ?
                              WHERE MEMBER_ID = ? 
                              AND SUB_ID = ?";
                              
                $updateStmt = $conn->prepare($updateSql);
                if (!$updateStmt) {
                    throw new Exception("Failed to prepare renewal update statement: " . $conn->error);
                }
                
                $updateStmt->bind_param("ssii", $startDate, $endDate, $memberId, $previousSubId);
                $updateStmt->execute();
                
                // If no rows were affected, it might be a different subscription now
                if ($updateStmt->affected_rows <= 0) {
                    $isRenewal = false; // Treat as new subscription
                }
            } else {
                // Different subscription type, so treat as new subscription
                $isRenewal = false;
            }
        }
        
        // Insert into transaction table - adjusted to use correct field names based on your database schema
        $sql1 = "INSERT INTO transaction (MEMBER_ID, SUB_ID, PAYMENT_ID, TRANSAC_DATE) VALUES (?, ?, ?, CURRENT_DATE)";
        $stmt1 = $conn->prepare($sql1);
        
        // Check for preparation errors
        if (!$stmt1) {
            throw new Exception("Failed to prepare transaction statement: " . $conn->error);
        }
        
        $stmt1->bind_param("iii", $memberId, $subscriptionId, $paymentId);
        $stmt1->execute();
        
        if ($stmt1->affected_rows <= 0) {
            throw new Exception("Failed to insert transaction record: " . $stmt1->error);
        }
        
        // Get transaction ID
        $transactionId = $conn->insert_id;
        error_log("Created transaction with ID: $transactionId");
        
        // Insert/Update member_subscription if not already handled by renewal
        if (!$isRenewal) {
            $sql2 = "INSERT INTO member_subscription (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                     VALUES (?, ?, ?, ?, 1) 
                     ON DUPLICATE KEY UPDATE 
                     START_DATE = VALUES(START_DATE),
                     END_DATE = VALUES(END_DATE),
                     IS_ACTIVE = 1";
            $stmt2 = $conn->prepare($sql2);
            
            // Check for preparation errors
            if (!$stmt2) {
                throw new Exception("Failed to prepare subscription statement: " . $conn->error);
            }
            
            $stmt2->bind_param("iiss", $memberId, $subscriptionId, $startDate, $endDate);
            $stmt2->execute();
        }
        
        // Insert transaction log
        $operation = $isRenewal ? "RENEWAL" : "INSERT";
        $description = $isRenewal ? 
            "Renewed subscription ID: $subscriptionId for Member ID: $memberId" :
            "New subscription ID: $subscriptionId for Member ID: $memberId";
            
        $sql3 = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, DESCRIPTION, MODIFIEDDATE) 
                 VALUES (?, ?, ?, CURRENT_DATE)";
        $stmt3 = $conn->prepare($sql3);
        
        // Check for preparation errors
        if (!$stmt3) {
            throw new Exception("Failed to prepare log statement: " . $conn->error);
        }
        
        $stmt3->bind_param("iss", $transactionId, $operation, $description);
        $stmt3->execute();
        
        $conn->commit();
        $success = true;
        error_log("Transaction completed successfully");
        
    } catch (Exception $e) {
        if ($conn->connect_error) {
            error_log("Connection error: " . $conn->connect_error);
        } else {
            $conn->rollback();
            error_log("Error creating transaction: " . $e->getMessage());
        }
        throw new Exception("Database error: " . $e->getMessage());
    } finally {
        if ($conn && !$conn->connect_error) {
            $conn->close();
        }
    }
    
    return $success;
}

function getMemberTransactionHistory($memberId) {
    $conn = getConnection();
    $history = [];
    
    try {
        $sql = "SELECT t.TRANSACTION_ID, t.TRANSAC_DATE, 
                       s.SUB_NAME, s.PRICE, p.PAY_METHOD,
                       ms.START_DATE, ms.END_DATE
                FROM transaction t
                JOIN subscription s ON t.SUB_ID = s.SUB_ID
                JOIN payment p ON t.PAYMENT_ID = p.PAYMENT_ID
                JOIN member_subscription ms ON t.MEMBER_ID = ms.MEMBER_ID AND t.SUB_ID = ms.SUB_ID
                WHERE t.MEMBER_ID = ?
                ORDER BY t.TRANSAC_DATE DESC";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    } catch (Exception $e) {
        error_log("Error fetching member transaction history: " . $e->getMessage());
    } finally {
        $conn->close();
    }
    
    return $history;
}

function deactivateSubscription($memberId, $subId) {
    $conn = getConnection();
    $success = false;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update the subscription status in member_subscription table
        $updateSql = "UPDATE member_subscription 
                      SET IS_ACTIVE = 0 
                      WHERE MEMBER_ID = ? AND SUB_ID = ? AND IS_ACTIVE = 1";
        
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $memberId, $subId);
        $stmt->execute();
        
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            $success = true;
            
            // Log the deactivation in transaction_log table
            $logSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
                       SELECT t.TRANSACTION_ID, 'DEACTIVATED', CURRENT_DATE()
                       FROM transaction t
                       WHERE t.MEMBER_ID = ? AND t.SUB_ID = ?
                       ORDER BY t.TRANSACTION_ID DESC
                       LIMIT 1";
                       
            $logStmt = $conn->prepare($logSql);
            $logStmt->bind_param("ii", $memberId, $subId);
            $logStmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        throw new Exception("Failed to deactivate subscription: " . $e->getMessage());
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
    
    return $success;
}