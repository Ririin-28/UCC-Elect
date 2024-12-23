<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_GET['coord_id'])) {
    $coord_id = $_GET['coord_id'];

    $stmt = $conn->prepare("CALL get_coordinator_by_id(?)");
    $stmt->bind_param("s", $coord_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $coordinator = $result->fetch_assoc();

        if ($coordinator) {
            echo json_encode($coordinator);
        } else {
            echo json_encode(["error" => "No coordinator found with the given ID."]);
        }
    } else {
        echo json_encode(["error" => "Error executing query: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "No coordinator ID provided."]);
}

$conn->close();
?>
