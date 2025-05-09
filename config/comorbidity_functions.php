<?php
require_once dirname(__DIR__) . '/connection/database.php';
require_once __DIR__ . '/db_functions.php';

function getAllComorbidities() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_GetAllComorbidities()");
        if (!$stmt->execute()) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $result = $stmt->get_result();
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
        $stmt = $conn->prepare("CALL sp_CheckComorbidityExists(?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Comorbidity already exists'];
        }
        $stmt->close();
        
        // Add new comorbidity
        $stmt = $conn->prepare("CALL sp_UpsertComorbidity(NULL, ?, ?)");
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
        
        // Check if comorbidity exists with same name but different ID
        $stmt = $conn->prepare("CALL sp_CheckComorbidityExistsForUpdate(?, ?)");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Another comorbidity with this name already exists'];
        }
        $stmt->close();
        
        // Update comorbidity
        $stmt = $conn->prepare("CALL sp_UpsertComorbidity(?, ?, ?)");
        $stmt->bind_param("isi", $id, $name, $isActive);
        
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
        
        $stmt = $conn->prepare("CALL sp_DeleteComorbidity(?)");
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