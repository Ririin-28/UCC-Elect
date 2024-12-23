<?php
session_start();
include('../db_connection.php');

$student_id = $_SESSION['student_id'];
$election_id = $_POST['election_id'] ?? null;
$votes = $_POST['vote'] ?? null;

if (!$election_id || !$votes || !is_array($votes)) {
    die("Invalid vote submission.");
}

try {
    $conn->begin_transaction();

    foreach ($_POST['vote'] as $position => $candidate_id) {
        $stmt = $conn->prepare("CALL cast_vote(?, ?, ?, ?)");
        $stmt->bind_param("iiii", $election_id, $student_id, $candidate_id, $position_id);
        if (!$stmt->execute()) {
            throw new Exception("Error recording vote for $position.");
        }
    }
    

    $conn->commit();
    header("Location: voter_election_event.php?success=1");
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>
