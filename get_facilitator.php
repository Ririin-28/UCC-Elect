<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_GET['facilitator_id'])) {
    $facilitator_id = $_GET['facilitator_id'];

    $stmt = $conn->prepare("CALL get_facilitator_by_id(?)");
    $stmt->bind_param("s", $facilitator_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $facilitator = $result->fetch_assoc();

        if ($facilitator) {
            echo json_encode($facilitator);
        } else {
            echo json_encode(["error" => "No facilitator found with the given ID."]);
        }
    } else {
        echo json_encode(["error" => "Error executing query: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "No facilitator ID provided."]);
}

$conn->close();
?>
