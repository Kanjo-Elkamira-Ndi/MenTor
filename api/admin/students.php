<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include '../../includes/db_connect.php';

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check admin authentication
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

function getInputData() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Invalid JSON input']));
    }
    return $input;
}

function validateStudentData($input, $isUpdate = false) {
    $errors = [];
    
    if (!$isUpdate) {
        if (empty($input['matricule_number'])) $errors[] = 'Matricule number is required';
        if (empty($input['password'])) $errors[] = 'Password is required';
    }
    
    if (empty($input['full_name'])) $errors[] = 'Full name is required';
    if (empty($input['email'])) $errors[] = 'Email is required';
    
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    return $errors;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $id = $_GET['id'] ?? $_GET['student_id'] ?? null; // Support both id and student_id
            $search = $_GET['search'] ?? '';
            $specialtyId = $_GET['specialty_id'] ?? null;
            
            $query = "SELECT u.id as student_id, u.*, s.name as specialty_name 
                      FROM users u 
                      LEFT JOIN specialties s ON u.specialty_id = s.id 
                      WHERE u.role = 'student'";
            
            $params = [];
            $types = '';
            
            if ($id) {
                $query .= " AND u.id = ?";
                $params[] = $id;
                $types .= 'i';
            }
            
            if (!empty($search)) {
                $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.matricule_number LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sss';
            }
            
            if (!empty($specialtyId)) {
                $query .= " AND u.specialty_id = ?";
                $params[] = $specialtyId;
                $types .= 'i';
            }
            
            $query .= " ORDER BY u.full_name";
            
            $stmt = $conn->prepare($query);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $students = [];
            while ($row = $result->fetch_assoc()) {
                unset($row['password']);
                $students[] = $row;
            }
            
            if ($id) {
                if (empty($students)) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Student not found']);
                } else {
                    echo json_encode(['success' => true, 'data' => $students[0]]);
                }
            } else {
                echo json_encode(['success' => true, 'data' => $students]);
            }
            break;
            
        case 'POST':
            $input = getInputData();
            $errors = validateStudentData($input);
            
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                break;
            }
            
            // Check for duplicate email or matricule
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR matricule_number = ?");
            $stmt->bind_param("ss", $input['email'], $input['matricule_number']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email or matricule number already exists']);
                break;
            }
            
            // Hash password
            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
            if (!$hashedPassword) {
                throw new Exception("Password hashing failed");
            }
            
            // Check if specialty exists if provided
            if (!empty($input['specialty_id'])) {
                $stmt = $conn->prepare("SELECT id FROM specialties WHERE id = ?");
                $stmt->bind_param("i", $input['specialty_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid specialty ID']);
                    break;
                }
            }
            
            // Insert student
            $specialty_id = !empty($input['specialty_id']) ? $input['specialty_id'] : null;
            $phone_number = !empty($input['phone_number']) ? $input['phone_number'] : null;
            
            $stmt = $conn->prepare("INSERT INTO users 
                (full_name, email, matricule_number, phone_number, specialty_id, password, role) 
                VALUES (?, ?, ?, ?, ?, ?, 'student')");
            
            $stmt->bind_param("ssssis", 
                $input['full_name'],
                $input['email'],
                $input['matricule_number'],
                $phone_number,
                $specialty_id,
                $hashedPassword
            );
            
            if ($stmt->execute()) {
                $studentId = $conn->insert_id;
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Student created successfully',
                    'id' => $studentId
                ]);
            } else {
                throw new Exception("Database error: " . $conn->error);
            }
            break;
            
        case 'PUT':
            $id = $_GET['id'] ?? $_GET['student_id'] ?? null; // Support both id and student_id
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID required']);
                break;
            }
            
            $input = getInputData();
            $errors = validateStudentData($input, true);
            
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                break;
            }
            
            // Check if specialty exists if provided
            if (isset($input['specialty_id']) && !empty($input['specialty_id'])) {
                $stmt = $conn->prepare("SELECT id FROM specialties WHERE id = ?");
                $stmt->bind_param("i", $input['specialty_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid specialty ID']);
                    break;
                }
            }
            
            // Build dynamic update query
            $fields = [];
            $params = [];
            $types = '';
            
            $fields[] = "full_name = ?";
            $params[] = $input['full_name'];
            $types .= 's';
            
            $fields[] = "email = ?";
            $params[] = $input['email'];
            $types .= 's';
            
            if (isset($input['phone_number'])) {
                $fields[] = "phone_number = ?";
                $params[] = $input['phone_number'];
                $types .= 's';
            }
            
            if (isset($input['specialty_id'])) {
                if (empty($input['specialty_id'])) {
                    $fields[] = "specialty_id = NULL";
                } else {
                    $fields[] = "specialty_id = ?";
                    $params[] = $input['specialty_id'];
                    $types .= 'i';
                }
            }
            
            if (!empty($input['password'])) {
                $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                if (!$hashedPassword) {
                    throw new Exception("Password hashing failed");
                }
                $fields[] = "password = ?";
                $params[] = $hashedPassword;
                $types .= 's';
            }
            
            $params[] = $id;
            $types .= 'i';
            
            $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ? AND role = 'student'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Student not found or no changes made']);
                }
            } else {
                throw new Exception("Database error: " . $conn->error);
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? $_GET['student_id'] ?? null; // Support both id and student_id
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID required']);
                break;
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Student not found']);
                }
            } else {
                throw new Exception("Database error: " . $conn->error);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>