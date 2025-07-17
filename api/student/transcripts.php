<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../../includes/db_connect.php';

// Check if user is student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$student_id = $_SESSION['user_id'];

try {
    // Get student info
    $stmt = $conn->prepare("SELECT full_name, matricule_number FROM users WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    if (!$student) {
        throw new Exception("Student not found");
    }

    // Get transcripts
    $stmt = $conn->prepare("
        SELECT t.id, t.generated_at, t.file_path 
        FROM transcripts t 
        WHERE t.student_id = ? 
        ORDER BY t.generated_at DESC
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $transcripts = [];
    while ($row = $result->fetch_assoc()) {
        $date = new DateTime($row['generated_at']);
        $formattedDate = $date->format('F j, Y');
        
        $transcripts[] = [
            'id' => $row['id'],
            'title' => 'Official Transcript',
            'description' => 'Academic transcript generated on ' . $formattedDate,
            'generated_at' => $formattedDate,
            'file_path' => '../../transcripts/' . $row['file_path'],
            'status' => 'Available',
            'format' => 'HTML'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $transcripts,
        'student' => [
            'name' => $student['full_name'],
            'matricule' => $student['matricule_number']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>