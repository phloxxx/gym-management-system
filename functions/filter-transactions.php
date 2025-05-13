<?php
require_once '../config/db_connection.php';

// Always set content type header for JSON
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
    // Get input data with proper validation
    $inputJson = file_get_contents('php://input');
    
    if (empty($inputJson)) {
        throw new Exception('No input data received');
    }
    
    $data = json_decode($inputJson, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Extract and sanitize filter parameters
    $startDate = isset($data['startDate']) ? trim($data['startDate']) : '';
    $endDate = isset($data['endDate']) ? trim($data['endDate']) : '';
    $subscription = isset($data['subscription']) ? trim($data['subscription']) : 'all';
    $program = isset($data['program']) ? trim($data['program']) : 'all';
    $memberId = isset($data['memberId']) ? trim($data['memberId']) : '';
    $memberSearch = isset($data['memberSearch']) ? trim($data['memberSearch']) : '';
    $status = isset($data['status']) ? trim($data['status']) : 'all';
    
    // Validate date formats if present
    if (!empty($startDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        throw new Exception('Invalid start date format. Use YYYY-MM-DD');
    }
    
    if (!empty($endDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception('Invalid end date format. Use YYYY-MM-DD');
    }
    
    // Validate subscription and program values if not 'all'
    if ($subscription !== 'all' && !is_numeric($subscription)) {
        throw new Exception('Invalid subscription value');
    }
    
    if ($program !== 'all' && !is_numeric($program)) {
        throw new Exception('Invalid program value');
    }
    
    // Validate member ID if present
    if (!empty($memberId) && !is_numeric($memberId)) {
        throw new Exception('Invalid member ID');
    }
    
    // Log received parameters for debugging
    error_log("Filter transactions request: " . json_encode([
        'startDate' => $startDate,
        'endDate' => $endDate,
        'subscription' => $subscription,
        'program' => $program,
        'memberId' => $memberId,
        'memberSearch' => $memberSearch,
        'status' => $status
    ]));
    
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('Failed to connect to database');
    }
    
    // Build the SQL query
    $sql = "SELECT 
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
                DATEDIFF(ms.END_DATE, CURRENT_DATE) as DAYS_LEFT
            FROM member m
            JOIN member_subscription ms ON m.MEMBER_ID = ms.MEMBER_ID
            JOIN subscription s ON ms.SUB_ID = s.SUB_ID
            JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
            LEFT JOIN transaction t ON m.MEMBER_ID = t.MEMBER_ID AND ms.SUB_ID = t.SUB_ID AND t.TRANSAC_DATE = (
                SELECT MAX(t2.TRANSAC_DATE) 
                FROM transaction t2 
                WHERE t2.MEMBER_ID = m.MEMBER_ID AND t2.SUB_ID = ms.SUB_ID
            )
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Apply date filters if provided
    if (!empty($startDate) && !empty($endDate)) {
        $sql .= " AND ((ms.START_DATE >= ? AND ms.START_DATE <= ?) 
                  OR (ms.END_DATE >= ? AND ms.END_DATE <= ?) 
                  OR (ms.START_DATE <= ? AND ms.END_DATE >= ?))";
        $params[] = $startDate;
        $params[] = $endDate;
        $params[] = $startDate;
        $params[] = $endDate;
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= 'ssssss';
    } else if (!empty($startDate)) {
        $sql .= " AND (ms.START_DATE >= ? OR ms.END_DATE >= ?)";
        $params[] = $startDate;
        $params[] = $startDate;
        $types .= 'ss';
    } else if (!empty($endDate)) {
        $sql .= " AND (ms.START_DATE <= ? OR ms.END_DATE <= ?)";
        $params[] = $endDate;
        $params[] = $endDate;
        $types .= 'ss';
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
    
    // Apply status filter
    if ($status !== 'all') {
        $isActive = ($status === 'active') ? 1 : 0;
        $sql .= " AND ms.IS_ACTIVE = ?";
        $params[] = $isActive;
        $types .= 'i';
    }
    
    // Apply member ID filter if provided
    if (!empty($memberId)) {
        $sql .= " AND m.MEMBER_ID = ?";
        $params[] = $memberId;
        $types .= 'i';
    }
    // Apply member search filter if no specific ID is provided
    else if (!empty($memberSearch)) {
        $sql .= " AND (
            m.MEMBER_FNAME LIKE ? 
            OR m.MEMBER_LNAME LIKE ? 
            OR CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) LIKE ?
            OR m.EMAIL LIKE ?
        )";
        $searchTerm = "%$memberSearch%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }
    
    // Apply sorting
    $sql .= " ORDER BY t.TRANSAC_DATE DESC, m.MEMBER_FNAME, m.MEMBER_LNAME";
    
    // Log the query for debugging
    error_log("SQL Query: " . $sql);
    error_log("Params: " . json_encode($params));
    
    // Prepare and execute the query with error handling
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('SQL prepare error: ' . $conn->error);
    }
    
    if (!empty($params)) {
        if (!$stmt->bind_param($types, ...$params)) {
            throw new Exception('Bind param error: ' . $stmt->error);
        }
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception('Result error: ' . $stmt->error);
    }
    
    // Process the results
    $subscriptions = [];
    while ($row = $result->fetch_assoc()) {
        $initials = strtoupper(substr($row['MEMBER_FNAME'], 0, 1) . substr($row['MEMBER_LNAME'], 0, 1));
        
        $subscriptions[] = [
            'MEMBER_ID' => $row['MEMBER_ID'],
            'SUB_ID' => $row['SUB_ID'],
            'memberId' => $row['MEMBER_ID'],
            'memberName' => htmlspecialchars($row['MEMBER_FNAME'] . ' ' . $row['MEMBER_LNAME']),
            'memberInitials' => $initials,
            'subscriptionId' => $row['SUB_ID'],
            'subscriptionName' => htmlspecialchars($row['SUB_NAME']),
            'program' => htmlspecialchars($row['PROGRAM_NAME']),
            'startDate' => date('M j, Y', strtotime($row['START_DATE'])),
            'endDate' => date('M j, Y', strtotime($row['END_DATE'])),
            'paidDate' => $row['PAID_DATE'] ? date('M j, Y', strtotime($row['PAID_DATE'])) : null,
            'isActive' => (int)$row['IS_ACTIVE'],
            'daysLeft' => (int)$row['DAYS_LEFT'],
            'transactionId' => $row['TRANSACTION_ID']
        ];
    }
    
    error_log("Filter returned " . count($subscriptions) . " subscriptions");
    
    // Send the successful response
    echo json_encode($subscriptions);
    
} catch (Exception $e) {
    // Log the error
    error_log("Filter error: " . $e->getMessage());
    
    // Send error response with proper status code
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'status' => 'error',
        'message' => 'Failed to filter transactions: ' . $e->getMessage()
    ]);
} finally {
    // Restore default error handler
    restore_error_handler();
    
    // Close the database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
