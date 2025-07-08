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
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT c.*, s.name as specialty_name FROM courses c LEFT JOIN specialties s ON c.specialty_id = s.id WHERE c.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Course not found']);
            }
        } else {
            $sql = "SELECT c.*, s.name as specialty_name FROM courses c LEFT JOIN specialties s ON c.specialty_id = s.id ORDER BY c.name";
            $result = $conn->query($sql);
            
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $courses]);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $course_code = $input['course_code'] ?? '';
        $name = $input['name'] ?? '';
        $description = $input['description'] ?? '';
        $credits = $input['credits'] ?? 0;
        $type = $input['type'] ?? '';
        $semester = $input['semester'] ?? '';
        $specialty_id = $input['specialty_id'] ?? null;
        
        if (empty($course_code) || empty($name) || empty($type) || empty($semester)) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO courses (course_code, name, description, credits, type, semester, specialty_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissi", $course_code, $name, $description, $credits, $type, $semester, $specialty_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course created successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating course: ' . $conn->error]);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? '';
        $course_code = $input['course_code'] ?? '';
        $name = $input['course_name'] ?? '';
        $description = $input['description'] ?? '';
        $credits = $input['credits'] ?? 0;
        $type = ['course_type'] ?? '';
        $semester = $input['semester'] ?? '';
        $specialty_id = $input['specialty_id'] ?? null;
        
        if (empty($id) || empty($course_code) || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, name = ?, description = ?, credits = ?, type = ?, semester = ?, specialty_id = ? WHERE id = ?");
        $stmt->bind_param("sssissii", $course_code, $name, $description, $credits, $type, $semester, $specialty_id, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating course: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting course: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>