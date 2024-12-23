<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_POST['coord_id'])) {
    $coord_id = $_POST['coord_id'];

    $stmt = $conn->prepare("CALL delete_coordinator(?)");
    $stmt->bind_param("s", $coord_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Coordinator deleted successfully."]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Missing required coord_id."]);
}

$conn->close();
?>
