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
        // Get all students or specific student
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT u.*, s.name as specialty_name FROM users u LEFT JOIN specialties s ON u.specialty_id = s.id WHERE u.id = ? AND u.role = 'student'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $student = $result->fetch_assoc();
                unset($student['password']); // Don't send password
                echo json_encode(['success' => true, 'data' => $student]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Student not found']);
            }
        } else {
            // Get all students
            $sql = "SELECT u.*, s.name as specialty_name FROM users u LEFT JOIN specialties s ON u.specialty_id = s.id WHERE u.role = 'student' ORDER BY u.full_name";
            $result = $conn->query($sql);
            
            $students = [];
            while ($row = $result->fetch_assoc()) {
                unset($row['password']); // Don't send password
                $students[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $students]);
        }
        break;
        
    case 'POST':
        // Create new student
        $input = json_decode(file_get_contents('php://input'), true);
        
        $full_name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        $matricule_number = $input['matricule_number'] ?? '';
        $phone_number = $input['phone_number'] ?? '';
        $bio = $input['bio'] ?? '';
        $specialty_id = $input['specialty_id'] ?? null;
        $password = $input['password'] ?? '';
        
        if (empty($full_name) || empty($email) || empty($matricule_number) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            break;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, matricule_number, phone_number, bio, specialty_id, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'student')");
        $stmt->bind_param("sssssis", $full_name, $email, $matricule_number, $phone_number, $bio, $specialty_id, $hashed_password);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Student created successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating student: ' . $conn->error]);
        }
        break;
        
    case 'PUT':
        // Update student
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? '';
        $full_name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        $phone_number = $input['phone_number'] ?? '';
        $bio = $input['bio'] ?? '';
        $specialty_id = $input['specialty_id'] ?? null;
        
        if (empty($id) || empty($full_name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, bio = ?, specialty_id = ? WHERE id = ? AND role = 'student'");
        $stmt->bind_param("ssssii", $full_name, $email, $phone_number, $bio, $specialty_id, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating student: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        // Delete student
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Student ID required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting student: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>

