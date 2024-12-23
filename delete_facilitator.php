<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_POST['facilitator_id'])) {
    $facilitator_id = $_POST['facilitator_id'];

    $stmt = $conn->prepare("CALL delete_facilitator(?)");
    $stmt->bind_param("s", $facilitator_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Facilitator deleted successfully."]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Missing required facilitator_id."]);
}

$conn->close();
?>
