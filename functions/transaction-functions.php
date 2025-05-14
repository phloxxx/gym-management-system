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
    
    try {        // Using Common Table Expressions (CTE) to show all unique subscription records
        // This shows all subscription history (both active and inactive)
        $sql = "WITH LatestTransactions AS (
                    SELECT 
                        t.MEMBER_ID,
                        t.SUB_ID,
                        t.TRANSACTION_ID,
                        t.TRANSAC_DATE,
                        ROW_NUMBER() OVER (PARTITION BY t.MEMBER_ID, t.SUB_ID ORDER BY t.TRANSAC_DATE DESC) as rn
                    FROM transaction t
                ),
                SubscriptionDates AS (
                    -- This gets unique start/end date combinations for each member+subscription
                    SELECT 
                        ms.MEMBER_ID,
                        ms.SUB_ID,
                        ms.START_DATE,
                        ms.END_DATE,
                        ms.IS_ACTIVE
                    FROM member_subscription ms
                    GROUP BY ms.MEMBER_ID, ms.SUB_ID, ms.START_DATE, ms.END_DATE, ms.IS_ACTIVE
                )
                SELECT 
                    m.MEMBER_ID, 
                    m.MEMBER_FNAME, 
                    m.MEMBER_LNAME, 
                    s.SUB_ID, 
                    s.SUB_NAME, 
                    sd.START_DATE, 
                    sd.END_DATE, 
                    sd.IS_ACTIVE,
                    lt.TRANSAC_DATE as PAID_DATE
                FROM member m
                JOIN SubscriptionDates sd ON m.MEMBER_ID = sd.MEMBER_ID
                JOIN subscription s ON sd.SUB_ID = s.SUB_ID
                LEFT JOIN LatestTransactions lt ON sd.MEMBER_ID = lt.MEMBER_ID 
                                              AND sd.SUB_ID = lt.SUB_ID 
                                              AND lt.rn = 1
                ORDER BY m.MEMBER_ID, s.SUB_ID, 
                         sd.IS_ACTIVE DESC,         -- Active subscriptions first
                         sd.END_DATE DESC,          -- Latest end date next
                         sd.START_DATE DESC,        -- Then latest start date
                         lt.TRANSAC_DATE DESC";
                
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

function createTransaction($memberId, $subscriptionId, $paymentId, $startDate, $endDate = null, $isRenewal = false, $previousSubId = null) {
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
        $checkSub = $conn->prepare("SELECT SUB_ID, DURATION FROM subscription WHERE SUB_ID = ?");
        $checkSub->bind_param("i", $subscriptionId);
        $checkSub->execute();
        $subResult = $checkSub->get_result();
        
        if ($subResult->num_rows === 0) {
            throw new Exception("Subscription ID $subscriptionId does not exist.");
        }
        
        // Get the subscription duration
        $subData = $subResult->fetch_assoc();
        $duration = $subData['DURATION']; // Duration in days
        
        // Calculate the end date based on start date and subscription duration
        $startDateTime = new DateTime($startDate);
        $endDateTime = clone $startDateTime;
        $endDateTime->add(new DateInterval("P{$duration}D"));
        $calculatedEndDate = $endDateTime->format('Y-m-d');
        
        error_log("Calculated end date: $calculatedEndDate based on duration: $duration days");
        
        $checkPay = $conn->prepare("SELECT PAYMENT_ID FROM payment WHERE PAYMENT_ID = ?");
        $checkPay->bind_param("i", $paymentId);
        $checkPay->execute();
        if ($checkPay->get_result()->num_rows === 0) {
            throw new Exception("Payment ID $paymentId does not exist.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        error_log("Transaction started");
        
        // IMPORTANT: Check if a transaction for this exact subscription was already created today
        // This prevents duplicate transactions when form is submitted multiple times
        $checkDuplicate = $conn->prepare("SELECT TRANSACTION_ID FROM transaction 
                                       WHERE MEMBER_ID = ? AND SUB_ID = ? 
                                       AND DATE(TRANSAC_DATE) = CURRENT_DATE()
                                       LIMIT 1");
        $checkDuplicate->bind_param("ii", $memberId, $subscriptionId);
        $checkDuplicate->execute();
        $duplicateResult = $checkDuplicate->get_result();
        
        if ($duplicateResult->num_rows > 0) {
            // A transaction already exists for today - don't create a duplicate
            error_log("Found existing transaction for today. Avoiding duplicate creation.");
            $duplicateRow = $duplicateResult->fetch_assoc();
            $existingTransactionId = $duplicateRow['TRANSACTION_ID'];
            
            // Check if member_subscription record for this transaction already exists and is active
            $checkSubActive = $conn->prepare("SELECT MEMBER_ID, SUB_ID, IS_ACTIVE FROM member_subscription 
                                            WHERE MEMBER_ID = ? AND SUB_ID = ? 
                                            AND START_DATE = ? AND END_DATE = ?");
            $checkSubActive->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
            $checkSubActive->execute();
            $subActiveResult = $checkSubActive->get_result();
            
            if ($subActiveResult->num_rows > 0) {
                $subRow = $subActiveResult->fetch_assoc();
                
                // If subscription exists but is inactive, reactivate it
                if ($subRow['IS_ACTIVE'] == 0) {
                    $updateSub = $conn->prepare("UPDATE member_subscription 
                                              SET IS_ACTIVE = 1 
                                              WHERE MEMBER_ID = ? AND SUB_ID = ? 
                                              AND START_DATE = ? AND END_DATE = ?");
                    $updateSub->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
                    $updateSub->execute();
                    error_log("Reactivated existing subscription record");
                }
            }
            
            // Don't create a duplicate record, just return success and use the existing transaction
            $conn->commit();
            return true;
        }
          // If this is a renewal, deactivate ONLY the specified previous subscription
        // to avoid deactivating other valid subscriptions the member might have
        if ($isRenewal) {
            if ($previousSubId) {
                // Deactivate only the specific subscription being renewed
                $deactivateSql = "UPDATE member_subscription 
                                SET IS_ACTIVE = 0 
                                WHERE MEMBER_ID = ? AND SUB_ID = ? AND IS_ACTIVE = 1";
                                
                $deactivateStmt = $conn->prepare($deactivateSql);
                if (!$deactivateStmt) {
                    throw new Exception("Failed to prepare deactivation statement: " . $conn->error);
                }
                
                $deactivateStmt->bind_param("ii", $memberId, $previousSubId);
                $deactivateStmt->execute();
                $deactivatedRows = $deactivateStmt->affected_rows;
                error_log("Deactivated subscription ID: $previousSubId for member ID: $memberId, affected rows: $deactivatedRows");
            } else {
                // If no specific subscription ID was provided for renewal,
                // deactivate any subscriptions that would overlap with the new date range
                $deactivateOverlapSql = "UPDATE member_subscription 
                                      SET IS_ACTIVE = 0 
                                      WHERE MEMBER_ID = ? AND IS_ACTIVE = 1 AND (
                                          (? BETWEEN START_DATE AND END_DATE) OR
                                          (? BETWEEN START_DATE AND END_DATE) OR
                                          (START_DATE BETWEEN ? AND ?) OR
                                          (END_DATE BETWEEN ? AND ?)
                                      )";
                
                $deactivateOverlapStmt = $conn->prepare($deactivateOverlapSql);
                $deactivateOverlapStmt->bind_param("issssss", 
                    $memberId, 
                    $startDate, $calculatedEndDate, 
                    $startDate, $calculatedEndDate, 
                    $startDate, $calculatedEndDate
                );
                $deactivateOverlapStmt->execute();
                $deactivatedRows = $deactivateOverlapStmt->affected_rows;
                error_log("Deactivated overlapping subscriptions for member ID: $memberId, affected rows: $deactivatedRows");
            }
            
            // Check if a record with the same parameters already exists (active or inactive)
            $checkExistingSub = $conn->prepare("SELECT MEMBER_ID, SUB_ID, IS_ACTIVE FROM member_subscription 
                                              WHERE MEMBER_ID = ? AND SUB_ID = ? 
                                              AND START_DATE = ? AND END_DATE = ?");
            $checkExistingSub->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
            $checkExistingSub->execute();
            $existingSubResult = $checkExistingSub->get_result();
            
            if ($existingSubResult->num_rows === 0) {
                // No existing record found, create a new one
                $insertSubSql = "INSERT INTO member_subscription 
                               (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                               VALUES (?, ?, ?, ?, 1)";
                
                $insertSubStmt = $conn->prepare($insertSubSql);
                if (!$insertSubStmt) {
                    throw new Exception("Failed to prepare subscription insert statement: " . $conn->error);
                }
                
                $insertSubStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
                $insertSubStmt->execute();
                
                if ($insertSubStmt->affected_rows <= 0) {
                    throw new Exception("Failed to insert new subscription record: " . $insertSubStmt->error);
                }
                
                error_log("Created new subscription record for member ID: $memberId with subscription ID: $subscriptionId");
            } else {
                // Record exists, update it to be active
                $existingRow = $existingSubResult->fetch_assoc();
                if ($existingRow['IS_ACTIVE'] == 0) {
                    $activateSubSql = "UPDATE member_subscription 
                                     SET IS_ACTIVE = 1 
                                     WHERE MEMBER_ID = ? AND SUB_ID = ? 
                                     AND START_DATE = ? AND END_DATE = ?";
                    $activateSubStmt = $conn->prepare($activateSubSql);
                    $activateSubStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
                    $activateSubStmt->execute();
                    error_log("Activated existing subscription record for member $memberId and subscription $subscriptionId");
                } else {
                    error_log("Subscription record for member $memberId and subscription $subscriptionId is already active");
                }
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
            // First deactivate any overlapping active subscriptions
            $deactivateOverlapSql = "UPDATE member_subscription 
                                   SET IS_ACTIVE = 0 
                                   WHERE MEMBER_ID = ? AND IS_ACTIVE = 1 AND (
                                       (? BETWEEN START_DATE AND END_DATE) OR
                                       (? BETWEEN START_DATE AND END_DATE) OR
                                       (START_DATE BETWEEN ? AND ?) OR
                                       (END_DATE BETWEEN ? AND ?)
                                   )";
            
            $deactivateOverlapStmt = $conn->prepare($deactivateOverlapSql);
            $deactivateOverlapStmt->bind_param("issssss", 
                $memberId, 
                $startDate, $calculatedEndDate, 
                $startDate, $calculatedEndDate, 
                $startDate, $calculatedEndDate
            );
            $deactivateOverlapStmt->execute();
            $deactivatedCount = $deactivateOverlapStmt->affected_rows;
            error_log("Deactivated $deactivatedCount overlapping subscriptions for member $memberId");
            
            // Check if this exact subscription already exists to prevent duplicates
            $checkDuplicateSql = "SELECT MEMBER_ID, SUB_ID, IS_ACTIVE FROM member_subscription 
                                WHERE MEMBER_ID = ? AND SUB_ID = ? AND START_DATE = ? AND END_DATE = ?";
            $checkDuplicateStmt = $conn->prepare($checkDuplicateSql);
            $checkDuplicateStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
            $checkDuplicateStmt->execute();
            $duplicateResult = $checkDuplicateStmt->get_result();
            
            if ($duplicateResult->num_rows === 0) {
                // No duplicate exists, create new subscription
                $sql2 = "INSERT INTO member_subscription (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                         VALUES (?, ?, ?, ?, 1)";
                $stmt2 = $conn->prepare($sql2);
                
                // Check for preparation errors
                if (!$stmt2) {
                    throw new Exception("Failed to prepare subscription statement: " . $conn->error);
                }
                
                $stmt2->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
                $stmt2->execute();
                error_log("Inserted new subscription record for member $memberId and subscription $subscriptionId");
            } else {
                // Duplicate exists, check if it's active
                $existingRow = $duplicateResult->fetch_assoc();
                if ($existingRow['IS_ACTIVE'] == 0) {
                    // Only update if it's inactive
                    $activateSql = "UPDATE member_subscription SET IS_ACTIVE = 1 
                                   WHERE MEMBER_ID = ? AND SUB_ID = ? 
                                   AND START_DATE = ? AND END_DATE = ?";
                    $activateStmt = $conn->prepare($activateSql);
                    $activateStmt->bind_param("iiss", $memberId, $subscriptionId, $startDate, $calculatedEndDate);
                    $activateStmt->execute();
                    error_log("Activated existing subscription for member $memberId and subscription $subscriptionId");
                } else {
                    error_log("Subscription for member $memberId and subscription $subscriptionId is already active");
                }
            }
        }
        
        // Check if transaction_log table has a DESCRIPTION column
        $checkTableQuery = "SHOW COLUMNS FROM transaction_log LIKE 'DESCRIPTION'";
        $checkResult = $conn->query($checkTableQuery);
        $hasDescriptionColumn = ($checkResult && $checkResult->num_rows > 0);
        
        // Insert transaction log with the correct columns
        $operation = $isRenewal ? "RENEWAL" : "INSERT";
        
        if ($hasDescriptionColumn) {
            $description = $isRenewal ? 
                "Renewed subscription ID: $subscriptionId for Member ID: $memberId" :
                "New subscription ID: $subscriptionId for Member ID: $memberId";
                
            $sql3 = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, DESCRIPTION, MODIFIEDDATE) 
                     VALUES (?, ?, ?, CURRENT_DATE)";
            $stmt3 = $conn->prepare($sql3);
            
            if (!$stmt3) {
                throw new Exception("Failed to prepare log statement: " . $conn->error);
            }
            
            $stmt3->bind_param("iss", $transactionId, $operation, $description);
        } else {
            // No DESCRIPTION column, just insert TRANSACTION_ID, OPERATION, and MODIFIEDDATE
            $sql3 = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
                     VALUES (?, ?, CURRENT_DATE)";
            $stmt3 = $conn->prepare($sql3);
            
            if (!$stmt3) {
                throw new Exception("Failed to prepare log statement: " . $conn->error);
            }
            
            $stmt3->bind_param("is", $transactionId, $operation);
        }
        
        $stmt3->execute();

        // Ensure the member is set to active if they have an active subscription
        // This prevents members from staying inactive after a renewal
        $updateMemberSql = "UPDATE member SET IS_ACTIVE = 1 WHERE MEMBER_ID = ?";
        $updateMemberStmt = $conn->prepare($updateMemberSql);
        $updateMemberStmt->bind_param("i", $memberId);
        $updateMemberStmt->execute();
        error_log("Updated member ID: $memberId to active status");
        
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
