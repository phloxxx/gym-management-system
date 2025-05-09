<?php
/**
 * Cron job to update all member statuses based on their subscription status
 * This script should be run daily to ensure member statuses are accurate
 */

// Set to run in CLI environment
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
}

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../functions/update-member-status.php';

// Initialize counters for reporting
$totalMembers = 0;
$updatedMembers = 0;
$errors = [];

try {
    $conn = getConnection();
    
    // Get all members
    $query = "SELECT MEMBER_ID FROM member";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Failed to query members: " . $conn->error);
    }
    
    $totalMembers = $result->num_rows;
    
    // Loop through each member and update their status
    while ($row = $result->fetch_assoc()) {
        $memberId = $row['MEMBER_ID'];
        
        try {
            // Update the member's status
            $statusResult = updateMemberStatus($memberId);
            
            if ($statusResult['success']) {
                $updatedMembers++;
                echo "Updated member ID $memberId to status: " . ($statusResult['status'] ? 'Active' : 'Inactive') . "\n";
                
                // If running in web environment, flush output buffer
                if (php_sapi_name() !== 'cli') {
                    ob_flush();
                    flush();
                }
            } else {
                $errors[] = "Failed to update member ID $memberId: " . $statusResult['message'];
            }
        } catch (Exception $e) {
            $errors[] = "Error updating member ID $memberId: " . $e->getMessage();
        }
    }
    
    // Report success
    $summary = [
        'success' => true,
        'totalMembers' => $totalMembers,
        'updatedMembers' => $updatedMembers,
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log the results
    $logFile = __DIR__ . '/logs/member_status_updates.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    file_put_contents(
        $logFile, 
        date('Y-m-d H:i:s') . " - Processed $totalMembers members, updated $updatedMembers, errors: " . count($errors) . "\n",
        FILE_APPEND
    );
    
    // Output result
    if (php_sapi_name() !== 'cli') {
        // Web output
        echo json_encode($summary);
    } else {
        // CLI output
        echo "\nSummary:\n";
        echo "Total members processed: $totalMembers\n";
        echo "Members updated: $updatedMembers\n";
        echo "Errors: " . count($errors) . "\n";
        
        if (!empty($errors)) {
            echo "\nErrors:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
        }
    }
    
} catch (Exception $e) {
    $errorMessage = "Fatal error: " . $e->getMessage();
    
    if (php_sapi_name() !== 'cli') {
        // Web output
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    } else {
        // CLI output
        echo "ERROR: $errorMessage\n";
        exit(1);
    }
}
?> 