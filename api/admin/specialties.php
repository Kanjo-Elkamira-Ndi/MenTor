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
            // Get single specialty
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT sp.*, sc.name as school_name FROM specialties sp LEFT JOIN schools sc ON sp.school_id = sc.id WHERE sp.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Specialty not found']);
            }
        } else {
            // Get all specialties with optional school filtering
            $school_id = $_GET['school_id'] ?? '';
            $search = $_GET['search'] ?? '';
            
            $sql = "SELECT sp.*, sc.name as school_name FROM specialties sp LEFT JOIN schools sc ON sp.school_id = sc.id WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($school_id)) {
                $sql .= " AND sp.school_id = ?";
                $params[] = $school_id;
                $types .= "i";
            }
            
            if (!empty($search)) {
                $sql .= " AND (sp.name LIKE ? OR sp.description LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $types .= "ss";
            }
            
            $sql .= " ORDER BY sp.name";
            
            if (!empty($params)) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql);
            }
            
            $specialties = [];
            while ($row = $result->fetch_assoc()) {
                $specialties[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $specialties]);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $school_id = $input['school_id'] ?? null;
        
        if (empty($name) || empty($school_id)) {
            echo json_encode(['success' => false, 'message' => 'Name and school are required']);
            break;
        }
        
        // Check if specialty name already exists in the same school
        $stmt = $conn->prepare("SELECT id FROM specialties WHERE name = ? AND school_id = ?");
        $stmt->bind_param("si", $name, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Specialty name already exists in this school']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO specialties (name, description, school_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $school_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Specialty created successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating specialty: ' . $conn->error]);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? '';
        
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $school_id = $input['school_id'] ?? null;
        
        if (empty($id) || empty($name) || empty($school_id)) {
            echo json_encode(['success' => false, 'message' => 'ID, name, and school are required']);
            break;
        }
        
        // Check if specialty name already exists in the same school (excluding current specialty)
        $stmt = $conn->prepare("SELECT id FROM specialties WHERE name = ? AND school_id = ? AND id != ?");
        $stmt->bind_param("sii", $name, $school_id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Specialty name already exists in this school']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE specialties SET name = ?, description = ?, school_id = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $description, $school_id, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Specialty updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Specialty not found or no changes made']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating specialty: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Specialty ID required']);
            break;
        }
        
        // Check if specialty has associated programs
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM programs WHERE specialty_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete specialty with associated programs. Please remove programs first.']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM specialties WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Specialty deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Specialty not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting specialty: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>

