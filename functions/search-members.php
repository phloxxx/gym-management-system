<?php
require_once '../config/db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Get search term from query string
$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

// Log the received search term for debugging
error_log("Search term received: " . $searchTerm);

// Validate search term - Changed to accept single character searches
if (empty($searchTerm)) {
    echo json_encode([]);
    exit;
}

try {
    $conn = getConnection();
    
    // Sanitize the search term for SQL query
    $searchPattern = "%" . $conn->real_escape_string($searchTerm) . "%";
    
    // Query to get member data
    $sql = "SELECT 
                m.MEMBER_ID,
                m.MEMBER_FNAME,
                m.MEMBER_LNAME,
                m.EMAIL,
                p.PROGRAM_NAME,
                ms.END_DATE,
                s.SUB_NAME
            FROM member m
            LEFT JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
            LEFT JOIN member_subscription ms ON m.MEMBER_ID = ms.MEMBER_ID 
                AND ms.IS_ACTIVE = 1
            LEFT JOIN subscription s ON ms.SUB_ID = s.SUB_ID
            WHERE (
                m.MEMBER_FNAME LIKE ? 
                OR m.MEMBER_LNAME LIKE ? 
                OR m.EMAIL LIKE ?
                OR CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) LIKE ?
            )
            AND m.IS_ACTIVE = 1
            GROUP BY m.MEMBER_ID
            ORDER BY m.MEMBER_FNAME, m.MEMBER_LNAME
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Database query preparation failed");
    }
    
    // Bind the search pattern to all the placeholders
    $stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Database query execution failed");
    }
    
    $result = $stmt->get_result();
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate subscription status
        $subscriptionStatus = 'No active subscription';
        if (!empty($row['END_DATE'])) {
            $endDate = new DateTime($row['END_DATE']);
            $today = new DateTime();
            if ($endDate >= $today) {
                $subscriptionStatus = $row['SUB_NAME'] . ' (Active)';
            } else {
                $subscriptionStatus = $row['SUB_NAME'] . ' (Expired)';
            }
        }

        $members[] = [
            'id' => $row['MEMBER_ID'],
            'name' => $row['MEMBER_FNAME'] . ' ' . $row['MEMBER_LNAME'],
            'email' => $row['EMAIL'],
            'program' => $row['PROGRAM_NAME'] ?? 'No Program',
            'subscription' => $subscriptionStatus,
            'initials' => strtoupper(substr($row['MEMBER_FNAME'], 0, 1) . substr($row['MEMBER_LNAME'], 0, 1))
        ];
    }
    
    // Remove the sample data fallback entirely - we only want real database results
    error_log("Returning " . count($members) . " results for search: $searchTerm");
    echo json_encode($members);
    
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
