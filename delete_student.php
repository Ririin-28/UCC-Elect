<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    $stmt = $conn->prepare("CALL delete_student(?)");
    $stmt->bind_param("s", $student_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Student deleted successfully."]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Missing required student_id."]);
}

$conn->close();
?>
