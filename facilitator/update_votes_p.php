<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "
    SELECT c.candidate_id, c.last_name, c.first_name, c.middle_name, COUNT(v.vote_id) AS votes
    FROM candidate c
    LEFT JOIN votes v ON c.candidate_id = v.candidate_id
    WHERE c.position_id = 1
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

$candidates = [];
$totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 1";
$totalVotesResult = $conn->query($totalVotesQuery);
$totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

if ($result->num_rows > 0) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
        $candidates[] = [
            'counter' => $counter,
            'votes' => $row['votes'],
            'percentage' => $percentage
        ];
        $counter++;
    }
}

echo json_encode($candidates);

$conn->close();
?>