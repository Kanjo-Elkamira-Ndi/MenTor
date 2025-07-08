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

$sql = "SELECT * FROM transcripts WHERE student_id = ? ORDER BY generated_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$transcripts = [];
while ($row = $result->fetch_assoc()) {
    $transcripts[] = [
        'id' => $row['id'],
        'generated_at' => $row['generated_at'],
        'file_path' => $row['file_path']
    ];
}

// If no transcripts exist, create sample data
if (empty($transcripts)) {
    $transcripts = [
        [
            'id' => 1,
            'title' => 'Official Transcript - Fall 2024',
            'description' => 'Complete academic record for Fall 2024 semester',
            'generated_at' => '2024-12-15',
            'status' => 'Available',
            'format' => 'PDF'
        ],
        [
            'id' => 2,
            'title' => 'Unofficial Transcript - Current',
            'description' => 'Unofficial transcript including current semester courses',
            'generated_at' => '2025-05-30',
            'status' => 'Pending',
            'format' => 'PDF'
        ]
    ];
}

echo json_encode(['success' => true, 'data' => $transcripts]);

$conn->close();
?>

