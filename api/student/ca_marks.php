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

$sql = "SELECT c.name as course_name, c.course_code, c.semester, 
               g.score, g.grade, g.status, g.assessment_type
        FROM grades g 
        JOIN enrollments e ON g.enrollment_id = e.id 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.student_id = ? AND g.assessment_type = 'CA'
        ORDER BY c.name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$ca_marks = [];
$courses_passed = 0;
$courses_failed = 0;
$in_progress = 0;
$total_score = 0;
$total_courses = 0;

while ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    if ($status === 'Passed') {
        $courses_passed++;
    } elseif ($status === 'Failed') {
        $courses_failed++;
    } else {
        $in_progress++;
        $status = 'In Progress';
    }
    
    if ($row['score'] !== null) {
        $total_score += $row['score'];
        $total_courses++;
    }
    
    $ca_marks[] = [
        'course_name' => $row['course_name'],
        'course_code' => $row['course_code'],
        'semester' => $row['semester'],
        'score' => $row['score'],
        'grade' => $row['grade'],
        'status' => $status
    ];
}

// Calculate current GPA (assuming 4.0 scale)
$current_gpa = $total_courses > 0 ? round(($total_score / $total_courses) / 25, 2) : 0;

$summary = [
    'courses_passed' => $courses_passed,
    'courses_failed' => $courses_failed,
    'in_progress' => $in_progress,
    'current_gpa' => $current_gpa
];

echo json_encode([
    'success' => true, 
    'data' => $ca_marks,
    'summary' => $summary
]);

$conn->close();
?>

