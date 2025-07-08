<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? '';
    
    if (empty($email) || empty($password) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'Email/Matricule, password, and role are required']);
        exit;
    }
    
    // Determine the field to search based on role
    if ($role === 'admin') {
        // For admin, search by email
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ? AND role = 'admin'");
        $stmt->bind_param("s", $email);
    } else {
        // For student, search by matricule_number
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE matricule_number = ? AND role = 'student'");
        $stmt->bind_param("s", $email);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password - check both MD5 (for compatibility) and password_hash
        $password_valid = false;
        
        // First try MD5 (for the default admin account)
        if (md5($password) === $user['password']) {
            $password_valid = true;
        }
        // Then try password_verify for properly hashed passwords
        elseif (password_verify($password, $user['password'])) {
            $password_valid = true;
        }
        
        if ($password_valid) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'user_role' => $user['role'],
                'user_id' => $user['id'],
                'user_name' => $user['full_name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        if ($role === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Admin account not found']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student account not found']);
        }
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
}

$conn->close();
?>

