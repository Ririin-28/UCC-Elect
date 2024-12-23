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
    isset($_POST['facilitator_id'], $_POST['last_name'], $_POST['first_name'], $_POST['middle_name'], 
    $_POST['email'], $_POST['contact_number'])
) {
    $facilitator_id = $conn->real_escape_string($_POST['facilitator_id']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact_number = $conn->real_escape_string($_POST['contact_number']);


    $stmt = $conn->prepare("CALL edit_facilitator(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssss", 
        $facilitator_id, 
        $last_name, 
        $first_name, 
        $middle_name, 
        $email, 
        $contact_number
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => "Facilitator updated successfully."]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Missing required fields."]);
}

$conn->close();
?>

