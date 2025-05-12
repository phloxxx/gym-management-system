<?php
// Include database connection
require_once '../config/db_connection.php';

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($sql);
    return ($result && $result->rowCount() > 0);
}

// Function to get column type
function getColumnType($conn, $table, $column) {
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($sql);
    if ($result && $result->rowCount() > 0) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['Type'];
    }
    return null;
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    echo "Starting subscription table update...<br>";
    
    // Check if DURATION column exists and get its type
    if (columnExists($conn, 'subscription', 'DURATION')) {
        $durationType = getColumnType($conn, 'subscription', 'DURATION');
        echo "Current DURATION column type: $durationType<br>";
        
        // If DURATION is not INT, change it
        if (strpos(strtolower($durationType), 'int') === false) {
            echo "Converting DURATION column to INT...<br>";
            
            // Rename existing column to old_DURATION temporarily
            $conn->exec("ALTER TABLE `subscription` CHANGE `DURATION` `old_DURATION` VARCHAR(10)");
            echo "Renamed DURATION column to old_DURATION<br>";
            
            // Add new DURATION column as INT
            $conn->exec("ALTER TABLE `subscription` ADD `DURATION` INT NOT NULL DEFAULT 0 AFTER `SUB_NAME`");
            echo "Added new DURATION column as INT<br>";
            
            // Update data from old column to new column (convert to integer)
            $stmt = $conn->query("SELECT `SUB_ID`, `old_DURATION` FROM `subscription`");
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($subscriptions as $subscription) {
                $oldDuration = strtolower($subscription['old_DURATION']);
                $durationNumber = preg_replace('/[^0-9]/', '', $oldDuration);
                $durationNumber = $durationNumber ? intval($durationNumber) : 0;
                
                // Convert to days based on unit
                if (strpos($oldDuration, 'day') !== false) {
                    // Already in days, no conversion needed
                } elseif (strpos($oldDuration, 'month') !== false) {
                    $durationNumber *= 30; // Convert months to days
                } elseif (strpos($oldDuration, 'year') !== false) {
                    $durationNumber *= 365; // Convert years to days
                }
                
                // Update the record
                $updateStmt = $conn->prepare("UPDATE `subscription` SET `DURATION` = ? WHERE `SUB_ID` = ?");
                $updateStmt->execute([$durationNumber, $subscription['SUB_ID']]);
            }
            
            echo "Updated DURATION values from old_DURATION<br>";
            
            // Drop old column
            $conn->exec("ALTER TABLE `subscription` DROP COLUMN `old_DURATION`");
            echo "Dropped old_DURATION column<br>";
        } else {
            echo "DURATION column is already INT type. No conversion needed.<br>";
        }
    } else {
        echo "DURATION column does not exist in subscription table.<br>";
    }
    
    // Commit transaction
    $conn->commit();
    echo "Subscription table update completed successfully!<br>";
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error updating subscription table: " . $e->getMessage() . "<br>";
} 