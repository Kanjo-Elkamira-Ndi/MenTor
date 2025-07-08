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
            // Get single school
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM schools WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'School not found']);
            }
        } else {
            // Get all schools with optional search
            $search = $_GET['search'] ?? '';
            
            if (!empty($search)) {
                $sql = "SELECT s.*, 
                        (SELECT COUNT(*) FROM programs p WHERE p.specialty_id IN (SELECT id FROM specialties WHERE school_id = s.id)) as programs_count,
                        (SELECT COUNT(*) FROM specialties sp WHERE sp.school_id = s.id) as specialties_count
                        FROM schools s 
                        WHERE s.name LIKE ? OR s.description LIKE ? 
                        ORDER BY s.name";
                $stmt = $conn->prepare($sql);
                $searchParam = "%$search%";
                $stmt->bind_param("ss", $searchParam, $searchParam);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $sql = "SELECT s.*, 
                        (SELECT COUNT(*) FROM programs p WHERE p.specialty_id IN (SELECT id FROM specialties WHERE school_id = s.id)) as programs_count,
                        (SELECT COUNT(*) FROM specialties sp WHERE sp.school_id = s.id) as specialties_count
                        FROM schools s 
                        ORDER BY s.name";
                $result = $conn->query($sql);
            }
            
            $schools = [];
            while ($row = $result->fetch_assoc()) {
                $schools[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $schools]);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $image = trim($input['image'] ?? '');
        
        if (empty($name) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Name and description are required']);
            break;
        }
        
        // Check if school name already exists
        $stmt = $conn->prepare("SELECT id FROM schools WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'School name already exists']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO schools (name, description, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $description, $image);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'School created successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating school: ' . $conn->error]);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? '';
        
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $image = trim($input['image'] ?? '');
        
        if (empty($id) || empty($name) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'ID, name, and description are required']);
            break;
        }
        
        // Check if school name already exists (excluding current school)
        $stmt = $conn->prepare("SELECT id FROM schools WHERE name = ? AND id != ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'School name already exists']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE schools SET name = ?, description = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $image, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'School updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'School not found or no changes made']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating school: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'School ID required']);
            break;
        }
        
        // Check if school has associated specialties or programs
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM specialties WHERE school_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete school with associated specialties. Please remove specialties first.']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM schools WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'School deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'School not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting school: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>

