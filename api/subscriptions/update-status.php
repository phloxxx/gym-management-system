<?php
require_once '../../config/db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Get parameters from URL
$reportType = isset($_GET['type']) ? $_GET['type'] : 'subscription';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
$program = isset($_GET['program']) ? $_GET['program'] : 'all';
$subscription = isset($_GET['subscription']) ? $_GET['subscription'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    // Get database connection
    $conn = getConnection();
    
    // Base SQL for different report types
    if ($reportType === 'subscription') {
        $sql = "SELECT 
                    CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) AS Name, 
                    s.SUB_NAME AS Subscription, 
                    ms.START_DATE AS StartDate, 
                    ms.END_DATE AS EndDate,
                    CASE WHEN ms.IS_ACTIVE = 1 THEN 'Active' ELSE 'Inactive' END AS Status,
                    s.PRICE AS Revenue,
                    p.PROGRAM_NAME AS Program
                FROM member m
                JOIN member_subscription ms ON m.MEMBER_ID = ms.MEMBER_ID
                JOIN subscription s ON ms.SUB_ID = s.SUB_ID
                JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
                WHERE 1=1";
    } else {
        // Revenue report
        $sql = "SELECT 
                    CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) AS Name,
                    s.SUB_NAME AS Subscription,
                    t.TRANSAC_DATE AS TransactionDate, 
                    ms.START_DATE AS StartDate, 
                    ms.END_DATE AS EndDate,
                    p.PAY_METHOD AS PaymentMethod,
                    s.PRICE AS Revenue
                FROM transaction t
                JOIN member m ON t.MEMBER_ID = m.MEMBER_ID
                JOIN member_subscription ms ON t.MEMBER_ID = ms.MEMBER_ID AND t.SUB_ID = ms.SUB_ID
                JOIN subscription s ON t.SUB_ID = s.SUB_ID
                JOIN payment p ON t.PAYMENT_ID = p.PAYMENT_ID
                JOIN program prog ON m.PROGRAM_ID = prog.PROGRAM_ID
                WHERE 1=1";
    }
    
    // Add filter conditions
    $params = [];
    $types = '';
    
    // Date filters
    if ($startDate) {
        if ($reportType === 'subscription') {
            $sql .= " AND (ms.START_DATE >= ? OR ms.END_DATE >= ?)";
            $params[] = $startDate;
            $params[] = $startDate;
            $types .= 'ss';
        } else {
            $sql .= " AND t.TRANSAC_DATE >= ?";
            $params[] = $startDate;
            $types .= 's';
        }
    }
    
    if ($endDate) {
        if ($reportType === 'subscription') {
            $sql .= " AND (ms.START_DATE <= ? OR ms.END_DATE <= ?)";
            $params[] = $endDate;
            $params[] = $endDate;
            $types .= 'ss';
        } else {
            $sql .= " AND t.TRANSAC_DATE <= ?";
            $params[] = $endDate;
            $types .= 's';
        }
    }
    
    // Program filter
    if ($program !== 'all') {
        $sql .= " AND m.PROGRAM_ID = ?";
        $params[] = $program;
        $types .= 'i';
    }
    
    // Subscription filter
    if ($subscription !== 'all') {
        $sql .= " AND s.SUB_ID = ?";
        $params[] = $subscription;
        $types .= 'i';
    }
    
    // Status filter
    if ($status !== 'all') {
        $isActive = ($status === 'active' || $status === '1') ? 1 : 0;
        $sql .= " AND ms.IS_ACTIVE = ?";
        $params[] = $isActive;
        $types .= 'i';
    }
    
    // Order by
    $sql .= " ORDER BY " . ($reportType === 'subscription' ? "Name, StartDate" : "TransactionDate DESC");
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get data for response
    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates for display
        if (isset($row['StartDate'])) {
            $row['StartDate'] = date('M j, Y', strtotime($row['StartDate']));
        }
        if (isset($row['EndDate'])) {
            $row['EndDate'] = date('M j, Y', strtotime($row['EndDate']));
        }
        if (isset($row['TransactionDate'])) {
            $row['TransactionDate'] = date('M j, Y', strtotime($row['TransactionDate']));
        }
        
        // Format revenue as currency
        if (isset($row['Revenue'])) {
            $row['Revenue'] = '₱' . number_format($row['Revenue'], 2);
        }
        
        $data[] = $row;
    }
    
    // Return the data as JSON
    echo json_encode($data);
    
} catch (Exception $e) {
    error_log('Report generation error: ' . $e->getMessage());
    echo json_encode([
        'error' => true,
        'message' => 'Error generating report: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>