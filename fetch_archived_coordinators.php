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

$sql = "SELECT * FROM view_archive_coordinators"; 
$result = $conn->query($sql);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "SQL Error: " . $conn->error]);
    exit;
}

$coordinators = array();
while ($row = $result->fetch_assoc()) {
    $coordinators[] = $row;
}

$result->free();
$conn->close();

header('Content-Type: application/json');
echo json_encode($coordinators, JSON_PRETTY_PRINT);
?>
