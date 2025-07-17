<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT t.*, s.full_name as student_full_name, s.matricule_number FROM transcripts t JOIN users s ON t.student_id = s.id WHERE t.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Transcript not found']);
            }
        } else {
            $searchQuery = $_GET['search'] ?? '';

            $sql = "SELECT t.*, s.full_name as student_full_name, s.matricule_number FROM transcripts t JOIN users s ON t.student_id = s.id WHERE 1=1";
            $params = [];
            $types = '';

            if (!empty($searchQuery)) {
                $sql .= " AND (s.full_name LIKE ? OR s.matricule_number LIKE ?)";
                $params[] = '%' . $searchQuery . '%';
                $params[] = '%' . $searchQuery . '%';
                $types .= 'ss';
            }

            $sql .= " ORDER BY s.full_name, t.generated_at DESC";

            $stmt = $conn->prepare($sql);
            if ($types) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $transcripts = [];
            while ($row = $result->fetch_assoc()) {
                $transcripts[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $transcripts]);
        }
        break;
        
    case 'POST':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $student_id = $input['student_id'] ?? null;
            
            if (empty($student_id)) {
                echo json_encode(['success' => false, 'message' => 'Student ID is required']);
                break;
            }
            
            // Check if student exists
            $stmt_check = $conn->prepare("SELECT id, full_name, matricule_number FROM users WHERE id = ? AND role = 'student'");
            $stmt_check->bind_param("i", $student_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Student not found']);
                break;
            }
            
            $student = $result_check->fetch_assoc();
            
            // Generate transcript file
            $transcript_content = generateTranscriptContent($conn, $student_id, $student);
            
            // Create transcripts directory if it doesn't exist
            $transcript_dir = '../../transcripts/';
            if (!file_exists($transcript_dir)) {
                if (!mkdir($transcript_dir, 0777, true)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to create transcripts directory']);
                    break;
                }
            }
            
            // Generate unique filename
            $filename = 'transcript_' . $student['matricule_number'] . '_' . date('Y-m-d_H-i-s') . '.html';
            $file_path = $transcript_dir . $filename;
            
            // Save transcript to file
            if (file_put_contents($file_path, $transcript_content) === false) {
                echo json_encode(['success' => false, 'message' => 'Failed to save transcript file']);
                break;
            }
            
            // Save transcript record to database
            $stmt = $conn->prepare("INSERT INTO transcripts (student_id, file_path) VALUES (?, ?)");
            $stmt->bind_param("is", $student_id, $filename);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Transcript generated successfully', 
                    'id' => $conn->insert_id,
                    'file_path' => $filename
                ]);
            } else {
                // Delete the file if database insert failed
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                echo json_encode(['success' => false, 'message' => 'Error saving transcript record: ' . $conn->error]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error generating transcript: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        $id = $_GET['transcript_id'] ?? null;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Transcript ID required']);
            break;
        }
        
        // Get file path before deleting record
        $stmt_get = $conn->prepare("SELECT file_path FROM transcripts WHERE id = ?");
        $stmt_get->bind_param("i", $id);
        $stmt_get->execute();
        $result_get = $stmt_get->get_result();
        
        if ($result_get->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Transcript not found']);
            break;
        }
        
        $transcript = $result_get->fetch_assoc();
        $file_path = '../../transcripts/' . $transcript['file_path'];
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM transcripts WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete file if it exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            echo json_encode(['success' => true, 'message' => 'Transcript deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting transcript: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function generateTranscriptContent($conn, $student_id, $student) {
    try {
        // Get student's grades with better error handling
        $stmt = $conn->prepare("
            SELECT g.*, c.name as course_name, c.course_code, c.credits, e.enrollment_date
            FROM grades g 
            JOIN enrollments e ON g.enrollment_id = e.id 
            JOIN courses c ON e.course_id = c.id 
            WHERE e.student_id = ? 
            ORDER BY e.enrollment_date, c.course_code
        ");
        
        if (!$stmt) {
            throw new Exception("Failed to prepare grades query: " . $conn->error);
        }
        
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $grades = [];
        $total_credits = 0;
        $total_grade_points = 0;
        
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
            if ($row['credits']) {
                $total_credits += $row['credits'];
                // Calculate grade points based on letter grade
                $grade_points = 0;
                switch ($row['grade']) {
                    case 'A': $grade_points = 4.0; break;
                    case 'B': $grade_points = 3.0; break;
                    case 'C': $grade_points = 2.0; break;
                    case 'D': $grade_points = 1.0; break;
                    case 'F': $grade_points = 0.0; break;
                }
                $total_grade_points += $grade_points * $row['credits'];
            }
        }
        
        $gpa = $total_credits > 0 ? round($total_grade_points / $total_credits, 2) : 0.00;
        
        // Generate HTML content
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Transcript - ' . htmlspecialchars($student['full_name']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 3px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .student-info { margin-bottom: 30px; }
        .student-info table { width: 100%; }
        .student-info td { padding: 5px 0; }
        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .grades-table th, .grades-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .grades-table th { background-color: #f2f2f2; font-weight: bold; }
        .summary { margin-top: 30px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
        .no-grades { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MENTOR UNIVERSITY</h1>
        <h2>OFFICIAL ACADEMIC TRANSCRIPT</h2>
        <p>This is to certify that this transcript contains a true and complete record of the academic work completed by the student named below.</p>
    </div>
    
    <div class="student-info">
        <h3>STUDENT INFORMATION</h3>
        <table>
            <tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($student['full_name']) . '</td></tr>
            <tr><td><strong>Student ID:</strong></td><td>' . htmlspecialchars($student['matricule_number']) . '</td></tr>
            <tr><td><strong>Transcript Date:</strong></td><td>' . date('F j, Y') . '</td></tr>
        </table>
    </div>
    
    <div class="academic-record">
        <h3>ACADEMIC RECORD</h3>';
        
        if (empty($grades)) {
            $html .= '<div class="no-grades">No academic records found for this student.</div>';
        } else {
            $html .= '<table class="grades-table">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Credits</th>
                        <th>Grade</th>
                        <th>Assessment Type</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($grades as $grade) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($grade['course_code'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($grade['course_name'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($grade['credits'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($grade['grade'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($grade['assessment_type'] ?? 'N/A') . '</td>
                </tr>';
            }
            
            $html .= '</tbody>
            </table>';
        }
        
        $html .= '</div>
    
    <div class="summary">
        <h3>ACADEMIC SUMMARY</h3>
        <table>
            <tr><td><strong>Total Credits Attempted:</strong></td><td>' . $total_credits . '</td></tr>
            <tr><td><strong>Total Credits Earned:</strong></td><td>' . $total_credits . '</td></tr>
            <tr><td><strong>Cumulative GPA:</strong></td><td>' . $gpa . '</td></tr>
        </table>
    </div>
    
    <div class="footer">
        <p>This transcript was generated electronically on ' . date('F j, Y \a\t g:i A') . '</p>
        <p>MENTOR UNIVERSITY - Official Academic Transcript</p>
    </div>
</body>
</html>';
        
        return $html;
    } catch (Exception $e) {
        throw new Exception("Error generating transcript content: " . $e->getMessage());
    }
}

$conn->close();
?>