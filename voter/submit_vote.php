<?php
include('../db_connection.php');

session_start();

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to vote.']);
    exit();
}

$student_id = $_SESSION['student_id'];

$election_id = $_POST['election_id'] ?? null;

if ($election_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Election ID not provided.']);
    exit();
}

$votes = $_POST['vote'] ?? null;
if ($votes === null || empty($votes)) {
    echo json_encode(['status' => 'error', 'message' => 'No votes selected.']);
    exit();
}

try {
    foreach ($votes as $position => $candidate_id) {
        $transformed_position = strtolower(str_replace('_', ' ', $position));

        $position_query = "SELECT position_id FROM positions WHERE position_name = ?";
        $stmt_position = $conn->prepare($position_query);
        $stmt_position->bind_param("s", $transformed_position);
        $stmt_position->execute();
        $result_position = $stmt_position->get_result();
        $position_row = $result_position->fetch_assoc();

        if (!$position_row) {
            echo "<div class='alert alert-warning'>Invalid position: $transformed_position. Skipping this position.</div>";
            continue;
        }
        $position_id = $position_row['position_id'];

        $validate_candidate_query = "SELECT COUNT(*) AS count FROM candidate WHERE candidate_id = ? AND position_id = ?";
        $stmt_validate = $conn->prepare($validate_candidate_query);
        $stmt_validate->bind_param("ii", $candidate_id, $position_id);
        $stmt_validate->execute();
        $result_validate = $stmt_validate->get_result();
        $row_validate = $result_validate->fetch_assoc();

        if ($row_validate['count'] == 0) {
            echo "<div class='alert alert-warning'>Invalid candidate selection for position: $position. Skipping this position.</div>";
            continue;
        }

        $check_vote_query = "SELECT COUNT(*) AS count FROM votes WHERE election_id = ? AND student_id = ? AND position_id = ?";
        $stmt_check_vote = $conn->prepare($check_vote_query);
        $stmt_check_vote->bind_param("iii", $election_id, $student_id, $position_id);
        $stmt_check_vote->execute();
        $result_check_vote = $stmt_check_vote->get_result();
        $row_check_vote = $result_check_vote->fetch_assoc();

        if ($row_check_vote['count'] > 0) {
            $stmt_update = $conn->prepare("CALL UpdateVote(?, ?, ?, ?)");
            $stmt_update->bind_param("iiii", $candidate_id, $election_id, $student_id, $position_id);
            if (!$stmt_update->execute()) {
                echo "<div class='alert alert-warning'>Failed to update vote for position: $position. Error: " . $stmt_update->error . "</div>";
                continue;
            }
            echo "<div class='alert alert-info'>Your vote for position: $position has been updated.</div>";
        } else {
            $stmt_insert = $conn->prepare("CALL InsertVote(?, ?, ?, ?)");
            $stmt_insert->bind_param("iiii", $election_id, $student_id, $candidate_id, $position_id);
            if (!$stmt_insert->execute()) {
                echo "<div class='alert alert-warning'>Failed to insert vote for position: $position. Error: " . $stmt_insert->error . "</div>";
                continue;
            }
            echo "<div class='alert alert-info'>Your vote for position: $position has been saved.</div>";
        }
    }

    echo "<div class='alert alert-success'>Your votes have been successfully submitted for all positions you voted for.</div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>An error occurred: " . $e->getMessage() . "</div>";
}