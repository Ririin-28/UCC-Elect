<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facilitator_id = $_POST['facilitator_id'];

    $sql = "CALL restore_facilitator(?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $facilitator_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Facilitator restored successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error executing the procedure.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
    }
    $stmt->close();
    $conn->close();
}
?>
