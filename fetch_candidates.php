<?php
include 'db_connection.php';

if (isset($_GET['election_name'])) {
    $election_name = $_GET['election_name'];

    $sql = "SELECT * FROM view_election_candidates WHERE election_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $election_name);
    $stmt->execute();
    $result = $stmt->get_result();

    $candidates = [];
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }

    echo json_encode($candidates);
}
?>
