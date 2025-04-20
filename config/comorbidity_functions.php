<?php
require_once 'db_functions.php';

function getAllComorbidities() {
    try {
        $conn = getConnection();
        $sql = "SELECT COMOR_ID, COMOR_NAME, IS_ACTIVE FROM comorbidities ORDER BY COMOR_NAME";
        $result = $conn->query($sql);
        
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
        return ['success' => false, 'message' => 'Failed to fetch comorbidities'];
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
        
        // Add new comorbidity
        $stmt = $conn->prepare("INSERT INTO comorbidities (COMOR_NAME, IS_ACTIVE) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $isActive);
        
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
        
        // Check if comorbidity exists
        $stmt = $conn->prepare("SELECT COMOR_ID FROM comorbidities WHERE COMOR_NAME = ? AND COMOR_ID != ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Another comorbidity with this name already exists'];
        }
        
        // Update comorbidity
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
