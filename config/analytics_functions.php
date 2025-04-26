<?php
require_once dirname(__DIR__) . '/connection/database.php';

function executeStoredProcedure($procedureName) {
    global $conn;
    try {
        // Prepare and execute
        $stmt = $conn->prepare("CALL " . $procedureName);
        if (!$stmt) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }
        if (!$stmt->execute()) {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
        
        // Get the result
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        // Clean up
        $stmt->close();
        while ($conn->more_results()) {
            $conn->next_result();
            if ($moreResult = $conn->store_result()) {
                $moreResult->free();
            }
        }
        
        return $data;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in $procedureName: " . $e->getMessage());
        return null;
    }
}

function executeStoredProcedureWithMultipleRows($procedureName) {
    global $conn;
    try {
        // Prepare and execute
        $stmt = $conn->prepare("CALL " . $procedureName);
        if (!$stmt) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }
        if (!$stmt->execute()) {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
        
        // Get all results
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Clean up
        $stmt->close();
        while ($conn->more_results()) {
            $conn->next_result();
            if ($moreResult = $conn->store_result()) {
                $moreResult->free();
            }
        }
        
        return $data;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in $procedureName: " . $e->getMessage());
        return [];
    }
}

function getActiveMembersCount() {
    $result = executeStoredProcedure("GetActiveMembersCount");
    return $result['count'] ?? 0;
}

function getMonthlyRevenue() {
    $result = executeStoredProcedure("GetMonthlyRevenue");
    return $result['revenue'] ?? 0;
}

function getActiveProgramsCount() {
    $result = executeStoredProcedure("GetActiveProgramsCount");
    return $result['count'] ?? 0;
}

function getStaffCount() {
    $result = executeStoredProcedure("GetStaffCount");
    return $result['count'] ?? 0;
}

function getMembershipGrowthData() {
    $months = array_fill(0, 6, 0);
    $results = executeStoredProcedureWithMultipleRows("GetMembershipGrowthData");
    
    foreach ($results as $row) {
        $monthIndex = (int)$row['month'] - 1;
        if ($monthIndex >= 0 && $monthIndex < 6) {
            $months[$monthIndex] = (int)$row['count'];
        }
    }
    return array_values($months);
}

function getSubscriptionDistribution() {
    $results = executeStoredProcedureWithMultipleRows("GetSubscriptionDistribution");
    $data = [];
    foreach ($results as $row) {
        $data[$row['SUB_NAME']] = (int)$row['count'];
    }
    return $data;
}

function getRecentMembers() {
    return executeStoredProcedureWithMultipleRows("GetRecentMembers");
}

function getRecentTransactions() {
    return executeStoredProcedureWithMultipleRows("GetRecentTransactions");
}
