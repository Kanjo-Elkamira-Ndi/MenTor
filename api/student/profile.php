<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

include '../../includes/db_connect.php';

// Check if user is student
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$student_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT u.id, u.full_name, u.email, u.matricule_number, u.phone_number, 
                       u.profile_image, u.bio, s.name as specialty_name,
                       COUNT(e.id) as total_courses,
                       AVG(g.score) as avg_score
                FROM users u 
                LEFT JOIN specialties s ON u.specialty_id = s.id 
                LEFT JOIN enrollments e ON u.id = e.student_id 
                LEFT JOIN grades g ON e.id = g.enrollment_id AND g.assessment_type = 'CA'
                WHERE u.id = ? AND u.role = 'student'
                GROUP BY u.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $profile = $result->fetch_assoc();
            
            // Calculate GPA and years (assuming current year - enrollment year)
            $gpa = $profile['avg_score'] ? round($profile['avg_score'] / 25, 2) : 0;
            $years = 2; // Default to 2 years, can be calculated based on enrollment date
            
            $profile_data = [
                'id' => $profile['id'],
                'full_name' => $profile['full_name'],
                'email' => $profile['email'],
                'matricule_number' => $profile['matricule_number'],
                'phone_number' => $profile['phone_number'],
                'profile_image' => $profile['profile_image'],
                'bio' => $profile['bio'],
                'specialty_name' => $profile['specialty_name'],
                'total_courses' => $profile['total_courses'],
                'gpa' => $gpa,
                'years' => $years
            ];
            
            echo json_encode(['success' => true, 'data' => $profile_data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Profile not found']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $full_name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        $phone_number = $input['phone_number'] ?? '';
        $bio = $input['bio'] ?? '';
        
        if (empty($full_name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, bio = ? WHERE id = ? AND role = 'student'");
        $stmt->bind_param("ssssi", $full_name, $email, $phone_number, $bio, $student_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>

