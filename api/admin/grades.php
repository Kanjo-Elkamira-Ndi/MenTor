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
        if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
            $course_id = $_GET['course_id'];
            $sql = "SELECT g.id as grade_id, g.*, e.student_id, e.course_id, c.name as course_name, c.course_code, u.full_name as student_name 
                    FROM grades g 
                    JOIN enrollments e ON g.enrollment_id = e.id 
                    JOIN courses c ON e.course_id = c.id 
                    JOIN users u ON e.student_id = u.id 
                    WHERE e.course_id = ? 
                    ORDER BY c.name, g.assessment_type";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $grades = [];
            while ($row = $result->fetch_assoc()) {
                // Add missing fields for frontend compatibility
                $row['grade_type'] = $row['assessment_type'] ?? 'N/A';
                $row['total_score'] = 100; // Default total score since it's not stored in DB
                $row['grade_date'] = date('Y-m-d'); // Default date since it's not stored in DB
                $grades[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $grades]);
        } else {
            // Get all grades
            $sql = "SELECT g.id as grade_id, g.*, e.student_id, e.course_id, c.name as course_name, c.course_code, u.full_name as student_name 
                    FROM grades g 
                    JOIN enrollments e ON g.enrollment_id = e.id 
                    JOIN courses c ON e.course_id = c.id 
                    JOIN users u ON e.student_id = u.id 
                    ORDER BY u.full_name, c.name, g.assessment_type";
            $result = $conn->query($sql);
            
            $grades = [];
            while ($row = $result->fetch_assoc()) {
                // Add missing fields for frontend compatibility
                $row['grade_type'] = $row['assessment_type'] ?? 'N/A';
                $row['total_score'] = 100; // Default total score since it's not stored in DB
                $row['grade_date'] = date('Y-m-d'); // Default date since it's not stored in DB
                $grades[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $grades]);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $course_id = $input['course_id'] ?? '';
        $student_id = $input['student_id'] ?? '';
        $grade_type = $input['grade_type'] ?? '';
        $score = $input['score'] ?? null;
        $total_score = $input['total_score'] ?? null;
        $grade_date = $input['grade_date'] ?? date('Y-m-d');
        
        if (empty($course_id) || empty($student_id) || empty($grade_type) || is_null($score) || is_null($total_score)) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            break;
        }

        // Find enrollment_id based on student_id and course_id
        $stmt_enrollment = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt_enrollment->bind_param("ii", $student_id, $course_id);
        $stmt_enrollment->execute();
        $result_enrollment = $stmt_enrollment->get_result();
        
        if ($result_enrollment->num_rows === 0) {
            // Create enrollment if it doesn't exist
            $enrollment_date = date('Y-m-d');
            $status = 'enrolled';
            $stmt_create_enrollment = $conn->prepare("INSERT INTO enrollments (student_id, course_id, enrollment_date, status) VALUES (?, ?, ?, ?)");
            $stmt_create_enrollment->bind_param("iiss", $student_id, $course_id, $enrollment_date, $status);
            
            if ($stmt_create_enrollment->execute()) {
                $enrollment_id = $conn->insert_id;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error creating enrollment: ' . $conn->error]);
                break;
            }
        } else {
            $enrollment_id = $result_enrollment->fetch_assoc()['id'];
        }
        
        // Map grade_type to assessment_type for database compatibility
        $assessment_type = ($grade_type === 'CA' || $grade_type === 'Quiz' || $grade_type === 'Assignment') ? 'CA' : 'Exam';
        
        // Calculate grade letter based on percentage
        $percentage = ($score / $total_score) * 100;
        $grade_letter = '';
        if ($percentage >= 80) $grade_letter = 'A';
        elseif ($percentage >= 70) $grade_letter = 'B';
        elseif ($percentage >= 60) $grade_letter = 'C';
        elseif ($percentage >= 50) $grade_letter = 'D';
        else $grade_letter = 'F';
        
        $status = ($percentage >= 50) ? 'Passed' : 'Failed';
        
        $stmt = $conn->prepare("INSERT INTO grades (enrollment_id, assessment_type, score, grade, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $enrollment_id, $assessment_type, $score, $grade_letter, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Grade created successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating grade: ' . $conn->error]);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $grade_id = $_GET['grade_id'] ?? '';
        $course_id = $input['course_id'] ?? '';
        $student_id = $input['student_id'] ?? '';
        $grade_type = $input['grade_type'] ?? '';
        $score = $input['score'] ?? null;
        $total_score = $input['total_score'] ?? null;
        $grade_date = $input['grade_date'] ?? date('Y-m-d');
        
        if (empty($grade_id)) {
            echo json_encode(['success' => false, 'message' => 'Grade ID is required']);
            break;
        }
        
        // Map grade_type to assessment_type for database compatibility
        $assessment_type = ($grade_type === 'CA' || $grade_type === 'Quiz' || $grade_type === 'Assignment') ? 'CA' : 'Exam';
        
        // Calculate grade letter based on percentage
        $percentage = ($score / $total_score) * 100;
        $grade_letter = '';
        if ($percentage >= 80) $grade_letter = 'A';
        elseif ($percentage >= 70) $grade_letter = 'B';
        elseif ($percentage >= 60) $grade_letter = 'C';
        elseif ($percentage >= 50) $grade_letter = 'D';
        else $grade_letter = 'F';
        
        $status = ($percentage >= 50) ? 'Passed' : 'Failed';
        
        $stmt = $conn->prepare("UPDATE grades SET assessment_type = ?, score = ?, grade = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sdssi", $assessment_type, $score, $grade_letter, $status, $grade_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Grade updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating grade: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        $grade_id = $_GET['grade_id'] ?? '';
        
        if (empty($grade_id)) {
            echo json_encode(['success' => false, 'message' => 'Grade ID required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM grades WHERE id = ?");
        $stmt->bind_param("i", $grade_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Grade deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting grade: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>