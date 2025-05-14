<?php
require_once '../config/db_connection.php';

// Set content type header for JSON response
header('Content-Type: application/json');

// Custom error handler to catch PHP errors and convert to exceptions
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Log request information for debugging
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestHeaders = getallheaders();
    $actionType = isset($requestHeaders['X-Action-Type']) ? $requestHeaders['X-Action-Type'] : 'unknown';
    
    error_log("Filter Transactions Request: Method=$requestMethod, Action=$actionType");
    
    // Get input data with proper validation
    $jsonInput = file_get_contents('php://input');
    error_log("Raw request data: " . $jsonInput);
    
    // Decode JSON data
    $data = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Check if this is a reset filters request
    $isResetRequest = isset($data['reset']) && $data['reset'] === true;
    $showAllTransactions = isset($data['show_all']) && $data['show_all'] === true;
    
    if ($isResetRequest) {
        error_log("Processing as Reset Filters request");
    }
    
    // Get database connection
    $conn = getConnection();
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }    // Base query to get transaction data - improved to show all transactions
    // This query ensures we always see transactions regardless of subscription status
    $baseQuery = "WITH LatestMemberSubscriptions AS (
                    SELECT 
                        ms.MEMBER_ID,
                        ms.SUB_ID,
                        ms.START_DATE,
                        ms.END_DATE,
                        ms.IS_ACTIVE,
                        ROW_NUMBER() OVER (PARTITION BY ms.MEMBER_ID, ms.SUB_ID ORDER BY ms.IS_ACTIVE DESC, ms.END_DATE DESC) as rn
                    FROM member_subscription ms
                )
                SELECT 
                    m.MEMBER_ID, 
                    m.MEMBER_FNAME, 
                    m.MEMBER_LNAME, 
                    s.SUB_ID,
                    s.SUB_NAME, 
                    ms.START_DATE, 
                    ms.END_DATE, 
                    ms.IS_ACTIVE,
                    t.TRANSACTION_ID,
                    t.TRANSAC_DATE as PAID_DATE,
                    p.PROGRAM_NAME,
                    pay.PAY_METHOD as PAYMENT_METHOD,
                    DATEDIFF(ms.END_DATE, CURRENT_DATE) as DAYS_LEFT
                FROM transaction t
                JOIN member m ON t.MEMBER_ID = m.MEMBER_ID
                JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
                JOIN subscription s ON t.SUB_ID = s.SUB_ID
                LEFT JOIN LatestMemberSubscriptions ms ON t.MEMBER_ID = ms.MEMBER_ID 
                                                     AND t.SUB_ID = ms.SUB_ID 
                LEFT JOIN payment pay ON t.PAYMENT_ID = pay.PAYMENT_ID
                WHERE 1=1";    // If this is a reset request or first page load, return all transactions without duplicates
    if ($isResetRequest || $showAllTransactions) {
        error_log("Processing as Reset Filters or Show All Transactions request");
        $sql = $baseQuery . " GROUP BY t.TRANSACTION_ID ORDER BY t.TRANSAC_DATE DESC, m.MEMBER_FNAME, m.MEMBER_LNAME";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            error_log("Database query failed: " . $conn->error);
            error_log("SQL Query: " . $sql);
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $transactions = fetchTransactionResults($result);
        error_log("Reset/Show All returned " . count($transactions) . " transactions");
        echo json_encode($transactions);
        exit;
    }
    
    // If we're specifically searching for a member, make sure we return ALL their transactions
    $showAllMemberTransactions = isset($data['show_all_member_transactions']) && $data['show_all_member_transactions'] === true;
    $memberSearch = isset($data['memberSearch']) ? trim($data['memberSearch']) : '';
      if (!empty($memberSearch)) {
        // When searching for a member, we want to show ONLY their actual transactions without duplicates
        $searchTerm = "%$memberSearch%";
        $sql = $baseQuery . " AND (
            m.MEMBER_FNAME LIKE ? 
            OR m.MEMBER_LNAME LIKE ? 
            OR CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) LIKE ?
            OR m.EMAIL LIKE ?
        ) GROUP BY t.TRANSACTION_ID ORDER BY t.TRANSAC_DATE DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare search query: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            throw new Exception("Member search query failed: " . $stmt->error);
        }
        
        $transactions = fetchTransactionResults($result);
        error_log("Member search for '$memberSearch' returned " . count($transactions) . " unique transactions");
        echo json_encode($transactions);
        exit;
    }
    
    // Apply regular filters if not reset or member search
    $sql = $baseQuery;
    $params = [];
    $types = '';
    
    // Extract and sanitize filter parameters
    $startDate = isset($data['startDate']) ? trim($data['startDate']) : '';
    $endDate = isset($data['endDate']) ? trim($data['endDate']) : '';
    $subscription = isset($data['subscription']) ? trim($data['subscription']) : 'all';
    $program = isset($data['program']) ? trim($data['program']) : 'all';
    $memberId = isset($data['memberId']) ? trim($data['memberId']) : '';
    $status = isset($data['status']) ? trim($data['status']) : 'all';
      // Apply date filters if provided - looking at transaction dates rather than subscription dates
    // This ensures we get transactions within the selected date range
    if (!empty($startDate) && !empty($endDate)) {
        $sql .= " AND (t.TRANSAC_DATE BETWEEN ? AND ?)";
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= 'ss';
    } else if (!empty($startDate)) {
        $sql .= " AND t.TRANSAC_DATE >= ?";
        $params[] = $startDate;
        $types .= 's';
    } else if (!empty($endDate)) {
        $sql .= " AND t.TRANSAC_DATE <= ?";
        $params[] = $endDate;
        $types .= 's';
    }
    
    // Apply subscription filter
    if ($subscription !== 'all') {
        $sql .= " AND s.SUB_ID = ?";
        $params[] = $subscription;
        $types .= 'i';
    }
    
    // Apply program filter
    if ($program !== 'all') {
        $sql .= " AND m.PROGRAM_ID = ?";
        $params[] = $program;
        $types .= 'i';
    }
      // Apply status filter - make it more inclusive to show transactions even when ms.IS_ACTIVE is null
    if ($status !== 'all') {
        $isActive = ($status === 'active') ? 1 : 0;
        // This allows us to see transactions even if there's no matching subscription record
        if ($isActive == 1) {
            $sql .= " AND (ms.IS_ACTIVE = 1 OR (ms.IS_ACTIVE IS NULL AND t.TRANSAC_DATE >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)))";
        } else {
            $sql .= " AND (ms.IS_ACTIVE = 0 OR ms.IS_ACTIVE IS NULL)";
        }
    }
    
    // Apply member ID filter if provided
    if (!empty($memberId)) {
        $sql .= " AND m.MEMBER_ID = ?";
        $params[] = $memberId;
        $types .= 'i';
    }
      // Apply grouping to ensure unique transaction records and then sorting
    $sql .= " GROUP BY t.TRANSACTION_ID ORDER BY t.TRANSAC_DATE DESC, m.MEMBER_FNAME, m.MEMBER_LNAME";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('SQL prepare error: ' . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
      // Get results and return
    $transactions = fetchTransactionResults($result);
    
    // Log the filter parameters and result counts for debugging
    error_log("Applied filters - Start: $startDate, End: $endDate, Subscription: $subscription, Program: $program, Status: $status");
    error_log("Filter query returned " . count($transactions) . " transactions");
    
    echo json_encode($transactions);
    
} catch (Exception $e) {
    error_log("Filter transactions error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'status' => 'error',
        'message' => 'Failed to filter transactions: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    restore_error_handler();
}

/**
 * Process result set into transaction array
 * @param mysqli_result $result The query result
 * @return array Processed transactions
 */
function fetchTransactionResults($result) {
    $transactions = [];
    
    while ($row = $result->fetch_assoc()) {
        $initials = strtoupper(substr($row['MEMBER_FNAME'], 0, 1) . substr($row['MEMBER_LNAME'], 0, 1));
        
        // Format dates nicely
        $startDate = !empty($row['START_DATE']) ? date('M j, Y', strtotime($row['START_DATE'])) : 'N/A';
        $endDate = !empty($row['END_DATE']) ? date('M j, Y', strtotime($row['END_DATE'])) : 'N/A';
        $paidDate = !empty($row['PAID_DATE']) ? date('M j, Y', strtotime($row['PAID_DATE'])) : 'N/A';
        
        // Format days left
        $daysLeft = isset($row['DAYS_LEFT']) ? (int)$row['DAYS_LEFT'] : null;
        
        // Check if the transaction was created today
        $isNew = date('Y-m-d') === date('Y-m-d', strtotime($row['PAID_DATE'] ?? 'now'));
        
        $transactions[] = [
            'memberId' => $row['MEMBER_ID'],
            'memberName' => htmlspecialchars($row['MEMBER_FNAME'] . ' ' . $row['MEMBER_LNAME']),
            'memberInitials' => $initials,
            'subscriptionId' => $row['SUB_ID'],
            'subscriptionName' => htmlspecialchars($row['SUB_NAME']),
            'program' => htmlspecialchars($row['PROGRAM_NAME']),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paidDate' => $paidDate,
            'isActive' => isset($row['IS_ACTIVE']) ? (int)$row['IS_ACTIVE'] : 0,
            'daysLeft' => $daysLeft,
            'transactionId' => $row['TRANSACTION_ID'],
            'paymentMethod' => $row['PAYMENT_METHOD'] ?? 'Unknown',
            'isNew' => $isNew
        ];
    }
    
    error_log("Returning " . count($transactions) . " transactions");
    return $transactions;
}
?>
