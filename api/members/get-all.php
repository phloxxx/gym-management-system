<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    $conn = getConnection();
    
    // Get filter parameters
    $programId = isset($_GET['program_id']) ? $_GET['program_id'] : null;
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : null;
    
    // Base query
    $query = "SELECT DISTINCT
                m.MEMBER_ID,
                m.MEMBER_FNAME,
                m.MEMBER_LNAME,
                m.EMAIL,
                m.PHONE_NUMBER,
                m.IS_ACTIVE,
                m.JOINED_DATE,
                p.PROGRAM_NAME,
                (SELECT CONCAT(c.COACH_FNAME, ' ', c.COACH_LNAME) 
                 FROM program_coach pc 
                 JOIN coach c ON pc.COACH_ID = c.COACH_ID 
                 WHERE pc.PROGRAM_ID = m.PROGRAM_ID 
                 LIMIT 1) as COACH_NAME,
                latest_sub.START_DATE,
                latest_sub.END_DATE,
                s.SUB_NAME
              FROM member m
              LEFT JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
              LEFT JOIN (
                  SELECT ms.MEMBER_ID, ms.SUB_ID, ms.START_DATE, ms.END_DATE
                  FROM member_subscription ms
                  WHERE ms.IS_ACTIVE = 1
                  AND (ms.MEMBER_ID, ms.START_DATE) IN (
                      SELECT MEMBER_ID, MAX(START_DATE)
                      FROM member_subscription
                      WHERE IS_ACTIVE = 1
                      GROUP BY MEMBER_ID
                  )
              ) latest_sub ON m.MEMBER_ID = latest_sub.MEMBER_ID
              LEFT JOIN subscription s ON latest_sub.SUB_ID = s.SUB_ID
              WHERE 1=1";
    
    $params = array();
    $types = "";
    
    // Add program filter if specified
    if ($programId) {
        $query .= " AND m.PROGRAM_ID = ?";
        $params[] = $programId;
        $types .= "i"; // Integer type
    }
    
    // Add search filter if specified
    if ($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $query .= " AND (m.MEMBER_FNAME LIKE ? OR m.MEMBER_LNAME LIKE ? OR m.EMAIL LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss"; // String types
    }
    
    // Add ordering
    $query .= " ORDER BY m.MEMBER_ID DESC";
    
    // Prepare and execute the query with parameters
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $members = array();
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'members' => $members
    ]);
    
} catch (Exception $e) {
    error_log("Error in get-all.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
