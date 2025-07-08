<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include '../../includes/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['student_id'])) {
            // Get grades for specific student
            $student_id = $_GET['student_id'];
            $sql = "SELECT g.*, e.student_id, c.name as course_name, c.course_code, u.full_name as student_name 
                    FROM grades g 
                    JOIN enrollments e ON g.enrollment_id = e.id 
                    JOIN courses c ON e.course_id = c.id 
                    JOIN users u ON e.student_id = u.id 
                    WHERE e.student_id = ? 
                    ORDER BY c.name, g.assessment_type";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $grades = [];
            while ($row = $result->fetch_assoc()) {
                $grades[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $grades]);
        } else {
            // Get all grades
            $sql = "SELECT g.*, e.student_id, c.name as course_name, c.course_code, u.full_name as student_name 
                    FROM grades g 
                    JOIN enrollments e ON g.enrollment_id = e.id 
                    JOIN courses c ON e.course_id = c.id 
                    JOIN users u ON e.student_id = u.id 
                    ORDER BY u.full_name, c.name, g.assessment_type";
            $result = $conn->query($sql);
            
            $grades = [];
            while ($row = $result->fetch_assoc()) {
                $grades[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $grades]);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $enrollment_id = $input['enrollment_id'] ?? '';
        $assessment_type = $input['assessment_type'] ?? '';
        $score = $input['score'] ?? null;
        $grade = $input['grade'] ?? '';
        $status = $input['status'] ?? '';
        
        if (empty($enrollment_id) || empty($assessment_type)) {
            echo json_encode(['success' => false, 'message' => 'Enrollment ID and assessment type are required']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO grades (enrollment_id, assessment_type, score, grade, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $enrollment_id, $assessment_type, $score, $grade, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Grade created successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating grade: ' . $conn->error]);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? '';
        $score = $input['score'] ?? null;
        $grade = $input['grade'] ?? '';
        $status = $input['status'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Grade ID is required']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE grades SET score = ?, grade = ?, status = ? WHERE id = ?");
        $stmt->bind_param("dssi", $score, $grade, $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Grade updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating grade: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Grade ID required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM grades WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Grade deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting grade: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>

