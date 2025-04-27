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
        $conn->begin_transaction();

        // First delete the program-coach relationships
        $stmt = $conn->prepare("DELETE FROM program_coach WHERE PROGRAM_ID = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for program_coach: " . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete program_coach relationships: " . $stmt->error);
        }
        $stmt->close();

        // Then delete the program
        $stmt = $conn->prepare("DELETE FROM program WHERE PROGRAM_ID = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for program: " . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete program: " . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        return ['success' => true, 'message' => 'Program deleted successfully'];
    } catch (Exception $e) {
        $conn->rollback();
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
        // First update the coach's basic information
        $stmt = $conn->prepare("UPDATE coach SET 
            COACH_FNAME = ?, 
            COACH_LNAME = ?, 
            EMAIL = ?, 
            PHONE_NUMBER = ?, 
            GENDER = ?,
            IS_ACTIVE = ?
            WHERE COACH_ID = ?");
            
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $stmt->bind_param("sssssii", 
            $data['COACH_FNAME'], 
            $data['COACH_LNAME'], 
            $data['EMAIL'], 
            $data['PHONE_NUMBER'], 
            $data['GENDER'],
            $isActive,
            $data['COACH_ID']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating coach: " . $stmt->error);
        }
        $stmt->close();
        
        // Delete existing program assignments
        $stmt = $conn->prepare("DELETE FROM program_coach WHERE COACH_ID = ?");
        $stmt->bind_param("i", $data['COACH_ID']);
        if (!$stmt->execute()) {
            throw new Exception("Error removing old program assignments");
        }
        $stmt->close();
        
        // Add new program assignments
        if (!empty($data['PROGRAM_ASSIGNMENTS'])) {
            $assignments = json_decode($data['PROGRAM_ASSIGNMENTS'], true);
            $stmt = $conn->prepare("INSERT INTO program_coach (COACH_ID, PROGRAM_ID) VALUES (?, ?)");
            
            foreach ($assignments as $programId) {
                $stmt->bind_param("ii", $data['COACH_ID'], $programId);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding program assignment");
                }
            }
            $stmt->close();
            
            // Update coach's specialization
            $programsStmt = $conn->prepare("SELECT GROUP_CONCAT(PROGRAM_NAME) as SPECIALIZATION 
                                          FROM program WHERE PROGRAM_ID IN (" . implode(',', $assignments) . ")");
            $programsStmt->execute();
            $result = $programsStmt->get_result();
            $specialization = $result->fetch_assoc()['SPECIALIZATION'];
            $programsStmt->close();
            
            $updateSpecStmt = $conn->prepare("UPDATE coach SET SPECIALIZATION = ? WHERE COACH_ID = ?");
            $updateSpecStmt->bind_param("si", $specialization, $data['COACH_ID']);
            $updateSpecStmt->execute();
            $updateSpecStmt->close();
        } else {
            // Clear specialization if no programs assigned
            $updateSpecStmt = $conn->prepare("UPDATE coach SET SPECIALIZATION = NULL WHERE COACH_ID = ?");
            $updateSpecStmt->bind_param("i", $data['COACH_ID']);
            $updateSpecStmt->execute();
            $updateSpecStmt->close();
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
