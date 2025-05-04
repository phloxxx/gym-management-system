<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$startDate = $data['startDate'] ?? '';
$endDate = $data['endDate'] ?? '';
$subscription = $data['subscription'] ?? 'all';
$program = $data['program'] ?? 'all';
$memberId = $data['memberId'] ?? '';
$memberSearch = $data['memberSearch'] ?? '';
$status = $data['status'] ?? 'all'; // Add status filter to show active/inactive subscriptions

error_log("Filter transactions request: " . json_encode($data));

$conn = getConnection();

try {
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
    
    if (!empty($startDate)) {
        $sql .= " AND (ms.START_DATE >= ? OR ms.END_DATE >= ?)";
        $params[] = $startDate;
        $params[] = $startDate;
        $types .= 'ss';
    }
    
    if (!empty($endDate)) {
        $sql .= " AND (ms.START_DATE <= ? OR ms.END_DATE <= ?)";
        $params[] = $endDate;
        $params[] = $endDate;
        $types .= 'ss';
    }
    
    if ($subscription !== 'all') {
        $sql .= " AND s.SUB_ID = ?";
        $params[] = $subscription;
        $types .= 'i';
    }
    
    if ($program !== 'all') {
        $sql .= " AND m.PROGRAM_ID = ?";
        $params[] = $program;
        $types .= 'i';
    }
    
    // Add status filter
    if ($status !== 'all') {
        $isActive = ($status === 'active') ? 1 : 0;
        $sql .= " AND ms.IS_ACTIVE = ?";
        $params[] = $isActive;
        $types .= 'i';
    }
    
    // Add specific member ID filter if present
    if (!empty($memberId)) {
        $sql .= " AND m.MEMBER_ID = ?";
        $params[] = $memberId;
        $types .= 'i';
    }
    // Only use text search if no specific member ID is selected
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
    
    $sql .= " ORDER BY ms.IS_ACTIVE DESC, ms.END_DATE ASC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subscriptions = [];
    while ($row = $result->fetch_assoc()) {
        $initials = strtoupper(substr($row['MEMBER_FNAME'], 0, 1) . substr($row['MEMBER_LNAME'], 0, 1));
        
        $subscriptions[] = [
            'memberId' => $row['MEMBER_ID'],
            'memberName' => htmlspecialchars($row['MEMBER_FNAME'] . ' ' . $row['MEMBER_LNAME']),
            'memberInitials' => $initials,
            'subscriptionId' => $row['SUB_ID'],
            'subscriptionName' => htmlspecialchars($row['SUB_NAME']),
            'program' => htmlspecialchars($row['PROGRAM_NAME']),
            'startDate' => date('M j, Y', strtotime($row['START_DATE'])),
            'endDate' => date('M j, Y', strtotime($row['END_DATE'])),
            'paidDate' => $row['PAID_DATE'] ? date('M j, Y', strtotime($row['PAID_DATE'])) : null,
            'isActive' => (int)$row['IS_ACTIVE'], // Ensure this is treated as a number
            'daysLeft' => (int)$row['DAYS_LEFT'],
            'transactionId' => $row['TRANSACTION_ID']
        ];
    }
    
    error_log("Filter returned " . count($subscriptions) . " subscriptions");
    echo json_encode($subscriptions);
    
} catch (Exception $e) {
    error_log("Filter error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}
?>