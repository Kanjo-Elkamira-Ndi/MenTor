<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include "../../includes/db_connect.php";

// Check database connection
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Check if user is admin
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit;
}

// Function to check if column exists in table
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
    return $result->num_rows > 0;
}

// Determine the correct column name for program code
$program_code_column = columnExists($conn, 'programs', 'program_code') ? 'program_code' : 'code';
$program_name_column = columnExists($conn, 'programs', 'program_name') ? 'program_name' : 'name';

$method = $_SERVER["REQUEST_METHOD"];

try {
    switch ($method) {
        case "GET":
            if (isset($_GET["id"]) || isset($_GET["program_id"])) {
                $id = $_GET["id"] ?? $_GET["program_id"];
                $sql = "SELECT p.id as program_id, p.{$program_code_column} as program_code, p.{$program_name_column} as program_name, p.school_id, 
                               p.duration_years, p.degree_type, p.status, p.description, 
                               p.total_credits, p.tuition_fee, s.name as school_name 
                               FROM programs p 
                               LEFT JOIN schools s ON p.school_id = s.id 
                               WHERE p.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    echo json_encode(["success" => true, "data" => $result->fetch_assoc()]);
                } else {
                    echo json_encode(["success" => false, "message" => "Program not found"]);
                }
            } else {
                $search = $_GET["search"] ?? "";
                $school_id = $_GET["school_id"] ?? "";

                $sql = "SELECT p.id as program_id, p.{$program_code_column} as program_code, p.{$program_name_column} as program_name, p.school_id, 
                        p.duration_years, p.degree_type, p.status, p.description, 
                        p.total_credits, p.tuition_fee, s.name as school_name 
                        FROM programs p 
                        LEFT JOIN schools s ON p.school_id = s.id 
                        WHERE 1=1";
                $params = [];
                $types = "";

                if (!empty($school_id)) {
                    $sql .= " AND p.school_id = ?";
                    $params[] = $school_id;
                    $types .= "i";
                }

                if (!empty($search)) {
                    $sql .= " AND (p.{$program_name_column} LIKE ? OR p.{$program_code_column} LIKE ?)";
                    $searchParam = "%$search%";
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $types .= "ss";
                }

                $sql .= " ORDER BY p.{$program_name_column}";

                if (!empty($params)) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $conn->query($sql);
                }

                $programs = [];
                while ($row = $result->fetch_assoc()) {
                    $programs[] = $row;
                }

                echo json_encode(["success" => true, "data" => $programs]);
            }
            break;

        case "POST":
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input) {
                echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
                break;
            }

            $program_code = trim($input["program_code"] ?? "");
            $program_name = trim($input["program_name"] ?? "");
            $school_id = $input["school_id"] ?? null;
            $duration_years = $input["duration_years"] ?? null;
            $degree_type = $input["degree_type"] ?? "";
            $status = $input["status"] ?? "Active";
            $description = $input["description"] ?? "";
            $total_credits = $input["total_credits"] ?? null;
            $tuition_fee = $input["tuition_fee"] ?? null;

            if (empty($program_code) || empty($program_name) || empty($school_id) || empty($duration_years) || empty($degree_type)) {
                echo json_encode(["success" => false, "message" => "Program code, name, school, duration, and degree type are required"]);
                break;
            }

            // Check if program code already exists
            $stmt = $conn->prepare("SELECT id FROM programs WHERE {$program_code_column} = ?");
            $stmt->bind_param("s", $program_code);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo json_encode(["success" => false, "message" => "Program code already exists"]);
                break;
            }

            $total_credits = is_numeric($total_credits) && $total_credits !== "" ? (int)$total_credits : null;
            $tuition_fee = is_numeric($tuition_fee) && $tuition_fee !== "" ? (float)$tuition_fee : null;

            $sql = "INSERT INTO programs ({$program_code_column}, {$program_name_column}, school_id, duration_years, degree_type, status, description, total_credits, tuition_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiisssid", $program_code, $program_name, $school_id, $duration_years, $degree_type, $status, $description, $total_credits, $tuition_fee);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Program created successfully", "id" => $conn->insert_id]);
            } else {
                echo json_encode(["success" => false, "message" => "Error creating program", "error" => $stmt->error]);
            }
            break;

        case "PUT":
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input) {
                echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
                break;
            }
            
            $id = $_GET["id"] ?? $_GET["program_id"] ?? "";

            $program_code = trim($input["program_code"] ?? "");
            $program_name = trim($input["program_name"] ?? "");
            $school_id = $input["school_id"] ?? null;
            $duration_years = $input["duration_years"] ?? null;
            $degree_type = $input["degree_type"] ?? "";
            $status = $input["status"] ?? "Active";
            $description = $input["description"] ?? "";
            $total_credits = $input["total_credits"] ?? null;
            $tuition_fee = $input["tuition_fee"] ?? null;

            if (empty($id) || empty($program_code) || empty($program_name) || empty($school_id) || empty($duration_years) || empty($degree_type)) {
                echo json_encode(["success" => false, "message" => "ID, program code, name, school, duration, and degree type are required"]);
                break;
            }

            // Check for duplicate program code (excluding current program)
            $stmt = $conn->prepare("SELECT id FROM programs WHERE {$program_code_column} = ? AND id != ?");
            $stmt->bind_param("si", $program_code, $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo json_encode(["success" => false, "message" => "Program code already exists"]);
                break;
            }

            $total_credits = is_numeric($total_credits) && $total_credits !== "" ? (int)$total_credits : null;
            $tuition_fee = is_numeric($tuition_fee) && $tuition_fee !== "" ? (float)$tuition_fee : null;

            $sql = "UPDATE programs SET {$program_code_column} = ?, {$program_name_column} = ?, school_id = ?, duration_years = ?, degree_type = ?, status = ?, description = ?, total_credits = ?, tuition_fee = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiisssiid", $program_code, $program_name, $school_id, $duration_years, $degree_type, $status, $description, $total_credits, $tuition_fee, $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(["success" => true, "message" => "Program updated successfully"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Program not found or no changes made"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Error updating program", "error" => $stmt->error]);
            }
            break;

        case "DELETE":
            $id = $_GET["id"] ?? $_GET["program_id"] ?? "";

            if (empty($id)) {
                echo json_encode(["success" => false, "message" => "Program ID required"]);
                break;
            }

            // Check if program has associated courses
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM courses WHERE program_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row["count"] > 0) {
                echo json_encode(["success" => false, "message" => "Cannot delete program with associated courses. Please remove courses first."]);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM programs WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(["success" => true, "message" => "Program deleted successfully"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Program not found"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Error deleting program", "error" => $stmt->error]);
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

$conn->close();
?>