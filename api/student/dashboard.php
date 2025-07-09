<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable detailed error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../includes/db_connect.php';

// Log function for debugging
function logError($message, $data = null) {
    error_log("Dashboard API Error: " . $message . (($data) ? " - Data: " . json_encode($data) : ""));
}

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    // Check if user is student
    if ($_SESSION['user_role'] !== 'student') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    $student_id = $_SESSION['user_id'];
    logError("Processing request for student_id: " . $student_id);

    // Get enrolled courses count
    $enrolled_courses = 0;
    try {
        $enrolled_courses_sql = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ?";
        $stmt = $conn->prepare($enrolled_courses_sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $enrolled_courses_result = $stmt->get_result();
        $enrolled_courses = $enrolled_courses_result->fetch_assoc()['count'];
        logError("Enrolled courses count: " . $enrolled_courses);
    } catch (Exception $e) {
        logError("Error getting enrolled courses: " . $e->getMessage());
    }

    // Calculate GPA
    $gpa = 0;
    try {
        $gpa_sql = "SELECT AVG(g.score) as gpa 
                FROM grades g 
                JOIN enrollments e ON g.enrollment_id = e.id 
                WHERE e.student_id = ?";
        $stmt = $conn->prepare($gpa_sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $gpa_result = $stmt->get_result();
        $gpa_row = $gpa_result->fetch_assoc();
        $gpa = $gpa_row['gpa'] ? round($gpa_row['gpa'] / 25, 2) : 0; // Convert to 4.0 scale
        logError("GPA calculated: " . $gpa);
    } catch (Exception $e) {
        logError("Error calculating GPA: " . $e->getMessage());
    }

    // Get recent grades count
    $recent_grades = 0;
    try {
        $recent_grades_sql = "SELECT COUNT(*) as count 
                          FROM grades g 
                          JOIN enrollments e ON g.enrollment_id = e.id 
                          WHERE e.student_id = ?";
        $stmt = $conn->prepare($recent_grades_sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $recent_grades_result = $stmt->get_result();
        $recent_grades = $recent_grades_result->fetch_assoc()['count'];
        logError("Recent grades count: " . $recent_grades);
    } catch (Exception $e) {
        logError("Error getting recent grades: " . $e->getMessage());
    }

    // Get account status (always active for now)
    $account_status = 'Active';

    // Get performance data for chart
    $performance_data = [];
    try {
        $performance_sql = "SELECT c.name as course_name, g.score 
                        FROM grades g 
                        JOIN enrollments e ON g.enrollment_id = e.id 
                        JOIN courses c ON e.course_id = c.id 
                        WHERE e.student_id = ? 
                        ORDER BY c.name 
                        LIMIT 5";
        $stmt = $conn->prepare($performance_sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $performance_result = $stmt->get_result();

        while ($row = $performance_result->fetch_assoc()) {
            $performance_data[] = [
                'course' => $row['course_name'],
                'score' => (float)$row['score']
            ];
        }
        
        // If no performance data, add dummy data for testing
        if (empty($performance_data)) {
            $performance_data = [
                ['course' => 'Mathematics', 'score' => 85],
                ['course' => 'Physics', 'score' => 78],
                ['course' => 'Chemistry', 'score' => 92],
                ['course' => 'Biology', 'score' => 88],
                ['course' => 'Computer Science', 'score' => 95]
            ];
        }
        
        logError("Performance data count: " . count($performance_data));
    } catch (Exception $e) {
        logError("Error getting performance data: " . $e->getMessage());
    }

    $dashboard_data = [
        'enrolled_courses' => $enrolled_courses,
        'current_gpa' => $gpa,
        'recent_grades' => $recent_grades,
        'account_status' => $account_status,
        'performance_data' => $performance_data
    ];

    echo json_encode(['success' => true, 'data' => $dashboard_data]);

} catch (Exception $e) {
    logError("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

