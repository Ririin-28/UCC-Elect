<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

if (!isset($_SESSION['student_id'])) {
$student_id = $_GET['student_id'] ?? null;
}

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("CALL get_student_details(?)");
    $stmt->bind_param("s", $student_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $student_details = $result->fetch_assoc();

        if ($student_details) {
            echo json_encode(['success' => true, 'details' => $student_details]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No details found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error executing query: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
