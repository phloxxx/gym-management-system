<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Member ID is required');
    }

    $conn = getConnection();
    $memberId = $_GET['id'];
    
    $query = "SELECT m.*, 
              p.PROGRAM_NAME,
              pc.COACH_ID,
              c.COACH_FNAME,
              c.COACH_LNAME,
              CONCAT(c.COACH_FNAME, ' ', c.COACH_LNAME) as COACH_NAME,
              ms.START_DATE,
              ms.END_DATE,
              s.SUB_NAME,
              s.SUB_ID,
              t.PAYMENT_ID,
              pm.PAY_METHOD
              FROM member m
              LEFT JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
              LEFT JOIN program_coach pc ON p.PROGRAM_ID = pc.PROGRAM_ID
              LEFT JOIN coach c ON pc.COACH_ID = c.COACH_ID
              LEFT JOIN (
                  SELECT * FROM member_subscription 
                  WHERE (MEMBER_ID, START_DATE) IN (
                      SELECT MEMBER_ID, MAX(START_DATE)
                      FROM member_subscription
                      GROUP BY MEMBER_ID
                  )
              ) ms ON m.MEMBER_ID = ms.MEMBER_ID
              LEFT JOIN subscription s ON ms.SUB_ID = s.SUB_ID
              LEFT JOIN transaction t ON m.MEMBER_ID = t.MEMBER_ID AND t.SUB_ID = ms.SUB_ID
              LEFT JOIN payment pm ON t.PAYMENT_ID = pm.PAYMENT_ID
              WHERE m.MEMBER_ID = ?
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
        
        // Get member's comorbidities
        $comorQuery = "SELECT c.COMOR_ID, c.COMOR_NAME 
                      FROM member_comorbidities mc 
                      JOIN comorbidities c ON mc.COMOR_ID = c.COMOR_ID 
                      WHERE mc.MEMBER_ID = ?";
        $stmt = $conn->prepare($comorQuery);
        $stmt->bind_param('i', $memberId);
        $stmt->execute();
        $comorResult = $stmt->get_result();
        
        $member['comorbidities'] = array();
        while ($comor = $comorResult->fetch_assoc()) {
            $member['comorbidities'][] = $comor;
        }
        
        // Get all coaches for this program
        $coachQuery = "SELECT c.COACH_ID, c.COACH_FNAME, c.COACH_LNAME, c.GENDER
                      FROM program_coach pc
                      JOIN coach c ON pc.COACH_ID = c.COACH_ID
                      WHERE pc.PROGRAM_ID = ?";
        $stmt = $conn->prepare($coachQuery);
        $stmt->bind_param('i', $member['PROGRAM_ID']);
        $stmt->execute();
        $coachResult = $stmt->get_result();
        
        $member['available_coaches'] = array();
        while ($coach = $coachResult->fetch_assoc()) {
            $member['available_coaches'][] = $coach;
        }
        
        echo json_encode($member);
    } else {
        throw new Exception('Member not found');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
