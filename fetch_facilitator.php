<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$sql = "SELECT facilitator_id, name, email, contact_number FROM view_facilitator";
$result = $conn->query($sql);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "SQL Error: " . $conn->error]);
    exit;
}

$facilitators = array();
while ($row = $result->fetch_assoc()) {
    $facilitators[] = $row;
}

header('Content-Type: application/json');
echo json_encode($facilitators);

$conn->close();
?>