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
            // Get enrollments for specific student
            $student_id = $_GET['student_id'];
            $sql = "SELECT e.*, c.name as course_name, c.course_code, c.credits, c.type, c.semester, u.full_name as student_name 
                    FROM enrollments e 
                    JOIN courses c ON e.course_id = c.id 
                    JOIN users u ON e.student_id = u.id 
                    WHERE e.student_id = ? 
                    ORDER BY c.name";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $enrollments = [];
            while ($row = $result->fetch_assoc()) {
                $enrollments[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $enrollments]);
        } else {
            // Get all enrollments
            $sql = "SELECT e.*, c.name as course_name, c.course_code, c.credits, c.type, c.semester, u.full_name as student_name 
                    FROM enrollments e 
                    JOIN courses c ON e.course_id = c.id 
                    JOIN users u ON e.student_id = u.id 
                    ORDER BY u.full_name, c.name";
            $result = $conn->query($sql);
            
            $enrollments = [];
            while ($row = $result->fetch_assoc()) {
                $enrollments[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $enrollments]);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $student_id = $input['student_id'] ?? '';
        $course_id = $input['course_id'] ?? '';
        $enrollment_date = $input['enrollment_date'] ?? date('Y-m-d');
        $status = $input['status'] ?? 'enrolled';
        
        if (empty($student_id) || empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Student ID and Course ID are required']);
            break;
        }
        
        // Check if enrollment already exists
        $check_stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $check_stmt->bind_param("ii", $student_id, $course_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Student is already enrolled in this course']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, enrollment_date, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $student_id, $course_id, $enrollment_date, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Enrollment created successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating enrollment: ' . $conn->error]);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? '';
        $status = $input['status'] ?? '';
        
        if (empty($id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Enrollment ID and status are required']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE enrollments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Enrollment updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating enrollment: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Enrollment ID required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Enrollment deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting enrollment: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>

