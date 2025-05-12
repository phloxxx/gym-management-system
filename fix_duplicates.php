<?php
// This is a one-time script to clean up duplicate member entries

require_once 'config/db_connection.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $conn = getConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    echo "<h1>Fixing Duplicate Member Entries</h1>";
    
    // 1. Find duplicate members (based on email)
    $findDuplicatesSql = "
        SELECT EMAIL, COUNT(*) as count, GROUP_CONCAT(MEMBER_ID ORDER BY MEMBER_ID) as ids
        FROM member 
        GROUP BY EMAIL
        HAVING COUNT(*) > 1
    ";
    
    $result = $conn->query($findDuplicatesSql);
    
    if ($result->num_rows > 0) {
        echo "<h2>Found " . $result->num_rows . " duplicate email(s):</h2>";
        echo "<table border='1'><tr><th>Email</th><th>IDs</th><th>Action</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ids']) . "</td>";
            
            // Get the IDs as an array, keep the lowest ID, remove others
            $ids = explode(',', $row['ids']);
            $keepId = $ids[0]; // Keep the lowest ID
            $removeIds = array_slice($ids, 1); // IDs to remove
            
            echo "<td>Keeping ID $keepId, removing IDs: " . implode(',', $removeIds) . "</td>";
            echo "</tr>";
            
            // Delete subscriptions for duplicate members
            foreach ($removeIds as $id) {
                $conn->query("DELETE FROM member_subscription WHERE MEMBER_ID = $id");
                echo "<br>Deleted subscriptions for member ID $id";
                
                // Delete comorbidities for duplicate members
                $conn->query("DELETE FROM member_comorbidities WHERE MEMBER_ID = $id");
                echo "<br>Deleted comorbidities for member ID $id";
                
                // Delete transactions for duplicate members
                $conn->query("DELETE FROM transaction WHERE MEMBER_ID = $id");
                echo "<br>Deleted transactions for member ID $id";
                
                // Delete the duplicate member
                $conn->query("DELETE FROM member WHERE MEMBER_ID = $id");
                echo "<br>Deleted member ID $id";
            }
        }
        
        echo "</table>";
    } else {
        echo "<p>No duplicate emails found.</p>";
    }
    
    // Find and fix duplicate member_subscription entries
    $findDuplicateSubsSql = "
        SELECT MEMBER_ID, SUB_ID, COUNT(*) as count 
        FROM member_subscription 
        GROUP BY MEMBER_ID, SUB_ID
        HAVING COUNT(*) > 1
    ";
    
    $result = $conn->query($findDuplicateSubsSql);
    
    if ($result->num_rows > 0) {
        echo "<h2>Found " . $result->num_rows . " duplicate subscription(s):</h2>";
        echo "<table border='1'><tr><th>Member ID</th><th>Subscription ID</th><th>Count</th><th>Action</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['MEMBER_ID'] . "</td>";
            echo "<td>" . $row['SUB_ID'] . "</td>";
            echo "<td>" . $row['count'] . "</td>";
            
            // Keep only the most recent subscription for this member and plan
            $fixDuplicateSubsSql = "
                DELETE ms1 FROM member_subscription ms1
                JOIN member_subscription ms2 ON ms1.MEMBER_ID = ms2.MEMBER_ID AND ms1.SUB_ID = ms2.SUB_ID
                WHERE ms1.MEMBER_ID = " . $row['MEMBER_ID'] . " 
                AND ms1.SUB_ID = " . $row['SUB_ID'] . "
                AND ms1.START_DATE < ms2.START_DATE
            ";
            
            $conn->query($fixDuplicateSubsSql);
            echo "<td>Kept only the most recent subscription</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No duplicate subscriptions found.</p>";
    }
    
    // Add unique email constraint to prevent future duplicates
    $alterTableSql = "ALTER TABLE member ADD UNIQUE INDEX unique_email (EMAIL)";
    
    try {
        $conn->query($alterTableSql);
        echo "<p>Added unique constraint on member email to prevent future duplicates.</p>";
    } catch (Exception $e) {
        echo "<p>Couldn't add unique constraint. Reason: " . $e->getMessage() . "</p>";
    }
    
    // Commit the changes
    $conn->commit();
    
    echo "<h2>Duplicate cleanup completed successfully!</h2>";
    echo "<p>Please refresh your member management page.</p>";
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 