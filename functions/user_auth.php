<?php
/**
 * User Authentication Helper Functions
 * These functions help with authentication and user ID handling
 */

/**
 * Gets the current authenticated user's ID
 * Returns default admin ID (1) if no user is authenticated
 * 
 * @param int $defaultId The default user ID to return if no user is found
 * @return int The user ID
 */
function getCurrentUserId($defaultId = 1) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    return $defaultId;
}

/**
 * Checks if a user ID exists and is active in the database
 * 
 * @param mysqli $conn Database connection
 * @param int $userId The user ID to check
 * @return bool True if user exists and is active, false otherwise
 */
function isValidUser($conn, $userId) {
    try {
        $stmt = $conn->prepare("SELECT USER_ID FROM user WHERE USER_ID = ? AND IS_ACTIVE = 1 LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return ($result->num_rows > 0);
    } catch (Exception $e) {
        error_log("Error checking user validity: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets a valid user ID for database operations
 * First checks the session, then the provided ID, then uses default
 * 
 * @param mysqli $conn Database connection
 * @param int|null $providedId Optional user ID to validate
 * @return int A valid user ID
 */
function getValidUserId($conn, $providedId = null) {
    // First try session
    $userId = getCurrentUserId();
    
    // If session user is valid, return it
    if (isValidUser($conn, $userId)) {
        return $userId;
    }
    
    // Next try provided ID
    if ($providedId !== null && isValidUser($conn, $providedId)) {
        return $providedId;
    }
    
    // Finally use default admin
    return 1;
}
?>
