<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];

    $sql = "CALL restore_student(?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Student restored successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error executing the procedure.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
    }
    $stmt->close();
    $conn->close();
}
?>
