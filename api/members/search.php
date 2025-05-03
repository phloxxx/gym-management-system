<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Get search term from query parameter
$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

if (empty($searchTerm)) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Search in member_subscription and members tables
    $query = "SELECT 
                m.MEMBER_ID,
                m.MEMBER_FNAME,
                m.MEMBER_LNAME,
                m.EMAIL,
                ms.SUB_ID,
                ms.START_DATE,
                ms.END_DATE,
                s.SUB_NAME
            FROM members m
            LEFT JOIN member_subscription ms ON m.MEMBER_ID = ms.MEMBER_ID
            LEFT JOIN subscription s ON ms.SUB_ID = s.SUB_ID
            WHERE m.MEMBER_FNAME LIKE :term 
            OR m.MEMBER_LNAME LIKE :term 
            OR m.EMAIL LIKE :term
            OR CONCAT(m.MEMBER_FNAME, ' ', m.MEMBER_LNAME) LIKE :term
            ORDER BY m.MEMBER_FNAME, m.MEMBER_LNAME
            LIMIT 10";

    $stmt = $pdo->prepare($query);
    $searchTerm = "%$searchTerm%";
    $stmt->execute(['term' => $searchTerm]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>