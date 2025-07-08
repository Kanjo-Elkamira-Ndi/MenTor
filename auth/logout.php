<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Destroy all session data
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Logout successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
}
?>

