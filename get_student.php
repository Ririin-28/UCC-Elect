<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    $stmt = $conn->prepare("CALL get_student_by_id(?)");
    $stmt->bind_param("s", $student_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();

        if ($student) {
            echo json_encode($student);
        } else {
            echo json_encode(["error" => "No student found with the given ID."]);
        }
    } else {
        echo json_encode(["error" => "Error executing query: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "No student ID provided."]);
}

$conn->close();
?>
