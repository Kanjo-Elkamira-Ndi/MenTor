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

// Get enrolled courses count
$enrolled_courses_sql = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND status = 'enrolled'";
$stmt = $conn->prepare($enrolled_courses_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$enrolled_courses_result = $stmt->get_result();
$enrolled_courses = $enrolled_courses_result->fetch_assoc()['count'];

// Calculate GPA
$gpa_sql = "SELECT AVG(g.score) as gpa 
            FROM grades g 
            JOIN enrollments e ON g.enrollment_id = e.id 
            WHERE e.student_id = ? AND g.assessment_type = 'CA' AND g.status = 'Passed'";
$stmt = $conn->prepare($gpa_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$gpa_result = $stmt->get_result();
$gpa_row = $gpa_result->fetch_assoc();
$gpa = $gpa_row['gpa'] ? round($gpa_row['gpa'] / 25, 2) : 0; // Convert to 4.0 scale

// Get recent grades count
$recent_grades_sql = "SELECT COUNT(*) as count 
                      FROM grades g 
                      JOIN enrollments e ON g.enrollment_id = e.id 
                      WHERE e.student_id = ? AND g.assessment_type = 'CA'";
$stmt = $conn->prepare($recent_grades_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$recent_grades_result = $stmt->get_result();
$recent_grades = $recent_grades_result->fetch_assoc()['count'];

// Get account status (always active for now)
$account_status = 'Active';

// Get performance data for chart
$performance_sql = "SELECT c.name as course_name, g.score 
                    FROM grades g 
                    JOIN enrollments e ON g.enrollment_id = e.id 
                    JOIN courses c ON e.course_id = c.id 
                    WHERE e.student_id = ? AND g.assessment_type = 'CA' 
                    ORDER BY c.name 
                    LIMIT 5";
$stmt = $conn->prepare($performance_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$performance_result = $stmt->get_result();

$performance_data = [];
while ($row = $performance_result->fetch_assoc()) {
    $performance_data[] = [
        'course' => $row['course_name'],
        'score' => (float)$row['score']
    ];
}

$dashboard_data = [
    'enrolled_courses' => $enrolled_courses,
    'current_gpa' => $gpa,
    'recent_grades' => $recent_grades,
    'account_status' => $account_status,
    'performance_data' => $performance_data
];

echo json_encode(['success' => true, 'data' => $dashboard_data]);

$conn->close();
?>

