<?php
require_once dirname(__DIR__) . '/connection/database.php';

function getAllPrograms() {
    global $conn;
    try {
        $result = $conn->query("CALL sp_GetAllPrograms()");
        
        if ($result === false) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->close();
        
        // Clear any remaining results
        while ($conn->more_results()) {
            $conn->next_result();
        }
        
        return ['success' => true, 'programs' => $data];
    } catch (Exception $e) {
        error_log("Error in getAllPrograms: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllCoaches() {
    global $conn;
    try {
        $result = $conn->query("CALL sp_GetAllCoachesWithPrograms()");
        
        if ($result === false) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->close();
        
        // Clear any remaining results
        while ($conn->more_results()) {
            $conn->next_result();
        }
        
        return ['success' => true, 'coaches' => $data];
    } catch (Exception $e) {
        error_log("Error in getAllCoaches: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function addProgram($data) {
    global $conn;
    // Clear any previous results
    while ($conn->more_results()) {
        $conn->next_result();
    }
    
    $stmt = $conn->prepare("CALL sp_AddProgram(?, ?)");
    $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
    $stmt->bind_param("si", $data['PROGRAM_NAME'], $isActive);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->next_result(); // Clear the result set
        return ['success' => true, 'message' => 'Program added successfully'];
    }
    $error = $stmt->error;
    $stmt->close();
    return ['success' => false, 'message' => $error];
}

function editProgram($data) {
    global $conn;
    $stmt = $conn->prepare("CALL sp_EditProgram(?, ?, ?)");
    $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
    $stmt->bind_param("isi", $data['PROGRAM_ID'], $data['PROGRAM_NAME'], $isActive);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Program updated successfully'];
    }
    return ['success' => false, 'message' => $stmt->error];
}

function deleteProgram($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("CALL sp_DeleteProgram(?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete program: " . $stmt->error);
        }
        $stmt->close();
        
        return ['success' => true, 'message' => 'Program deleted successfully'];
    } catch (Exception $e) {
        error_log("Error in deleteProgram: " . $e->getMessage());
        return ['success' => false, 'message' => 'Cannot delete program. It may be in use.'];
    }
}

function addCoach($data) {
    global $conn;
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("CALL sp_AddCoach(?, ?, ?, ?, ?, ?)");
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $stmt->bind_param("sssssi", 
            $data['COACH_FNAME'], 
            $data['COACH_LNAME'], 
            $data['EMAIL'], 
            $data['PHONE_NUMBER'], 
            $data['GENDER'],
            $isActive
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error adding coach");
        }
        
        $result = $stmt->get_result();
        $coachData = $result->fetch_assoc();
        $coachId = $coachData['COACH_ID'];
        $stmt->close();
        $conn->next_result();
        
        // Add program assignments
        if (!empty($data['PROGRAM_ASSIGNMENTS'])) {
            $assignments = json_decode($data['PROGRAM_ASSIGNMENTS'], true);
            $stmt = $conn->prepare("CALL sp_AddCoachProgram(?, ?)");
            
            foreach ($assignments as $programId) {
                $stmt->bind_param("ii", $coachId, $programId);
                if (!$stmt->execute()) {
                    throw new Exception("Error assigning program");
                }
                $conn->next_result();
            }
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Coach added successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function editCoach($data) {
    global $conn;
    $conn->begin_transaction();
    
    try {
        // Update coach's basic information using sp_EditCoach
        $stmt = $conn->prepare("CALL sp_EditCoach(?, ?, ?, ?, ?, ?, ?)");
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $stmt->bind_param("isssssi", 
            $data['COACH_ID'],
            $data['COACH_FNAME'], 
            $data['COACH_LNAME'], 
            $data['EMAIL'], 
            $data['PHONE_NUMBER'], 
            $data['GENDER'],
            $isActive
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating coach: " . $stmt->error);
        }
        $stmt->close();
        $conn->next_result();
        
        // Update program assignments using sp_EditCoachPrograms
        if (isset($data['PROGRAM_ASSIGNMENTS'])) {
            // Convert JSON array to comma-separated string
            $programIds = implode(',', json_decode($data['PROGRAM_ASSIGNMENTS']));
            $stmt = $conn->prepare("CALL sp_EditCoachPrograms(?, ?)");
            $stmt->bind_param("is", $data['COACH_ID'], $programIds);
            if (!$stmt->execute()) {
                throw new Exception("Error updating program assignments: " . $stmt->error);
            }
            $stmt->close();
            $conn->next_result();
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Coach updated successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function deleteCoach($id) {
    global $conn;
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("CALL sp_DeleteCoachPrograms(?)");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Error removing program assignments");
        }
        $stmt->close();
        $conn->next_result();
        
        $stmt = $conn->prepare("CALL sp_DeleteCoach(?)");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting coach");
        }
        $stmt->close();
        
        $conn->commit();
        return ['success' => true, 'message' => 'Coach deleted successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>
