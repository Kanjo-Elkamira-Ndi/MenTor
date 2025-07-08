<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../../includes/db_connect.php';

// Check if user is student
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$student_id = $_SESSION['user_id'];

$sql = "SELECT c.id, c.course_code, c.name, c.description, c.credits, c.type, c.semester, 
               s.name as specialty_name, e.enrollment_date, e.status as enrollment_status
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        LEFT JOIN specialties s ON c.specialty_id = s.id 
        WHERE e.student_id = ? 
        ORDER BY c.name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = [
        'id' => $row['id'],
        'course_code' => $row['course_code'],
        'name' => $row['name'],
        'description' => $row['description'],
        'credits' => $row['credits'],
        'type' => $row['type'],
        'semester' => $row['semester'],
        'specialty_name' => $row['specialty_name'],
        'enrollment_date' => $row['enrollment_date'],
        'enrollment_status' => $row['enrollment_status']
    ];
}

echo json_encode(['success' => true, 'data' => $courses]);

$conn->close();
?>

