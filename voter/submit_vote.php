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
    $insert_vote_query = "INSERT INTO votes (election_id, student_id, candidate_id, position_id, timestamp) VALUES (?, ?, ?, ?, NOW())";
    $update_vote_query = "UPDATE votes SET candidate_id = ?, timestamp = NOW() WHERE election_id = ? AND student_id = ? AND position_id = ?"; // For updating existing votes

    $stmt_insert = $conn->prepare($insert_vote_query);
    $stmt_update = $conn->prepare($update_vote_query);

    if (!$stmt_insert || !$stmt_update) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statements: ' . $conn->error]);
        exit();
    }

    $check_votes_count_query = "SELECT COUNT(*) AS count FROM votes WHERE election_id = ? AND student_id = ?";
    $stmt_check_votes_count = $conn->prepare($check_votes_count_query);
    $stmt_check_votes_count->bind_param("ii", $election_id, $student_id);
    $stmt_check_votes_count->execute();
    $result_check_votes_count = $stmt_check_votes_count->get_result();
    $row_check_votes_count = $result_check_votes_count->fetch_assoc();

    if ($row_check_votes_count['count'] == 11) {
        echo json_encode(['status' => 'error', 'message' => 'You have already voted for all positions.']);
        exit();
    }

    $vote_saved = false;

    foreach ($votes as $position => $candidate_id) {
        $transformed_position = strtolower(str_replace('_', ' ', $position));

        $position_query = "SELECT position_id FROM positions WHERE position_name = ?";
        $stmt_position = $conn->prepare($position_query);
        $stmt_position->bind_param("s", $transformed_position);
        $stmt_position->execute();
        $result_position = $stmt_position->get_result();
        $position_row = $result_position->fetch_assoc();

        if (!$position_row) {
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
            continue; 
        }

        $check_vote_query = "SELECT COUNT(*) AS count FROM votes WHERE election_id = ? AND student_id = ? AND position_id = ?";
        $stmt_check_vote = $conn->prepare($check_vote_query);
        $stmt_check_vote->bind_param("iii", $election_id, $student_id, $position_id);
        $stmt_check_vote->execute();
        $result_check_vote = $stmt_check_vote->get_result();
        $row_check_vote = $result_check_vote->fetch_assoc();

        if ($row_check_vote['count'] > 0) {
            $stmt_update->bind_param("iiii", $candidate_id, $election_id, $student_id, $position_id);
            if (!$stmt_update->execute()) {
                continue; 
            }
        } else {
            $stmt_insert->bind_param("iiii", $election_id, $student_id, $candidate_id, $position_id);
            if (!$stmt_insert->execute()) {
                continue; 
            }
        }

        $vote_saved = true;
    }

    if ($vote_saved) {
        echo json_encode(['status' => 'success', 'message' => 'Vote Submitted Successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No votes were saved.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
