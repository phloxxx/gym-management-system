<?php
require_once 'db_functions.php';

function getAllComorbidities() {
    try {
        $conn = getConnection();
        $sql = "SELECT COMOR_ID, COMOR_NAME, IS_ACTIVE FROM comorbidities ORDER BY COMOR_NAME ASC";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $comorbidities = [];
        while ($row = $result->fetch_assoc()) {
            $comorbidities[] = [
                'id' => $row['COMOR_ID'],
                'name' => $row['COMOR_NAME'],
                'isActive' => (bool)$row['IS_ACTIVE']
            ];
        }
        
        return ['success' => true, 'data' => $comorbidities];
    } catch (Exception $e) {
        error_log("Error getting comorbidities: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function addComorbidity($name, $isActive) {
    try {
        $conn = getConnection();
        
        // Check if comorbidity already exists
        $stmt = $conn->prepare("SELECT COMOR_ID FROM comorbidities WHERE COMOR_NAME = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Comorbidity already exists'];
        }
        
        // Use CALL to execute the stored procedure
        $stmt = $conn->prepare("CALL sp_UpsertComorbidity(NULL, ?)");
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Comorbidity added successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to add comorbidity'];
        }
    } catch (Exception $e) {
        error_log("Error adding comorbidity: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function updateComorbidity($id, $name, $isActive) {
    try {
        $conn = getConnection();
        
        // Check if comorbidity exists with same name but different ID
        $stmt = $conn->prepare("SELECT COMOR_ID FROM comorbidities WHERE COMOR_NAME = ? AND COMOR_ID != ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Another comorbidity with this name already exists'];
        }
        
        // Update including IS_ACTIVE status
        $stmt = $conn->prepare("UPDATE comorbidities SET COMOR_NAME = ?, IS_ACTIVE = ? WHERE COMOR_ID = ?");
        $stmt->bind_param("sii", $name, $isActive, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Comorbidity updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update comorbidity'];
        }
    } catch (Exception $e) {
        error_log("Error updating comorbidity: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function deleteComorbidity($id) {
    try {
        $conn = getConnection();
        
        // Since there's no stored procedure for delete, we'll keep the direct SQL
        $stmt = $conn->prepare("DELETE FROM comorbidities WHERE COMOR_ID = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Comorbidity deleted successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to delete comorbidity'
            ];
        }
    } catch (Exception $e) {
        error_log("Error deleting comorbidity: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}