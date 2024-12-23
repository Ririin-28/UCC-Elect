<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coord_id = $_POST['coordinator_id']; 


    $sql = "CALL restore_coordinator(?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $coord_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Coordinator restored successfully.']);
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
