<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (
    isset($_POST['student_id'], $_POST['last_name'], $_POST['first_name'], $_POST['middle_name'], 
    $_POST['gender'], $_POST['course_name'], $_POST['year_section'])
) {

    $student_id = $conn->real_escape_string($_POST['student_id']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $course_name = $conn->real_escape_string($_POST['course_name']);
    $year_section = $conn->real_escape_string($_POST['year_section']);

    $stmt = $conn->prepare("CALL edit_student(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssss", 
        $student_id, 
        $last_name, 
        $first_name, 
        $middle_name, 
        $gender, 
        $year_section, 
        $course_name
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => "Student updated successfully."]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Missing required fields."]);
}

$conn->close();
?>
