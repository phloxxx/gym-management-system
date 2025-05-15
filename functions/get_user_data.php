<?php
/**
 * Function to fetch user data from the database based on the current user session
 * This ensures we always have up-to-date user data rather than relying on session values
 */
function getUserData() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    
    try {
        require_once dirname(__FILE__) . '/../config/db_connection.php';
        $conn = getConnection();
        
        $sql = "SELECT USER_ID, USERNAME, USER_FNAME, USER_LNAME, USER_TYPE 
                FROM user WHERE USER_ID = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $userData = $result->fetch_assoc();
            
            // Update session with fresh data from database
            $_SESSION['firstname'] = $userData['USER_FNAME'];
            $_SESSION['lastname'] = $userData['USER_LNAME'];
            $_SESSION['name'] = $userData['USER_FNAME'] . ' ' . $userData['USER_LNAME'];
            $_SESSION['username'] = $userData['USERNAME'];
            $_SESSION['role'] = strtolower($userData['USER_TYPE']);
            
            return $userData;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return false;
    }
}
