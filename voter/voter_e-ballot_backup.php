<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$positionOrder = [
    'President',
    'Vice President',
    'Secretary',
    'Auditor',
    'Treasurer',
    'Business Manager',
    'Creative Committee',
    'First Year Representative',
    'Second Year Representative',
    'Third Year Representative',
    'Fourth Year Representative'
];

$orderClause = "FIELD(position_name, '" . implode("','", $positionOrder) . "')";

$sql = "SELECT * FROM view_for_eballot
        ORDER BY $orderClause, position_name, name";
$result = $conn->query($sql);

$candidates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $position = $row['position_name'];
        if (!isset($candidates[$position])) {
            $candidates[$position] = [];
        }
        $candidates[$position][] = [
            'candidate_id' => $row['candidate_id'],
            'name' => $row['name'],
            'course_name' => $row['course_name'],
            'year_section' => $row['year_section']
        ];
    }
}

$orderedCandidates = [];
foreach ($positionOrder as $position) {
    if (isset($candidates[$position])) {
        $orderedCandidates[$position] = $candidates[$position];
    }
}

foreach ($candidates as $position => $candidateList) {
    if (!isset($orderedCandidates[$position])) {
        $orderedCandidates[$position] = $candidateList;
    }
}

$candidates = $orderedCandidates;

?>
<?php

include('../db_connection.php');

$election_id = $_GET['election_id'] ?? null; 

if ($election_id === null) {
    die("Error: Election ID not provided.");
}

$stmt = $conn->prepare("SELECT * FROM election WHERE election_id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: Election ID not found.");
}

$candidates = [];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCC-Elect: Voter</title>
    <link rel="icon" href="../images/UCC-Elect_Logo.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/voter_dashboard.css">
</head>

<body>
    <div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <img src="../images/UCC-Elect_Logo2.png" alt="Toggle Sidebar" class="custom-logo">
                </button>
                <div class="sidebar-logo">
                    <a href="voter_dashboard.php">Student</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="voter_dashboard.php" class="sidebar-link">
                        <i class="bi bi-person-circle"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="voter_e-ballot.php" class="sidebar-link">
                        <i class="bi bi-envelope-paper"></i>
                        <span>E-Ballot</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="voter_change_password.php" class="sidebar-link">
                        <i class="bi bi-key"></i>
                        <span>Change Password</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../logout.php" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

    <div class="main-content container-fluid g-0">
        <div class="title-container">
            <h1>UCC-Elect: Official Ballot</h1>
        </div>

        <div class="content container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card ballot-card">
                        <div class="card-header text-center bg-primary text-white">
                            <h2 class="mb-0">OFFICIAL BALLOT</h2>
                            <p class="mb-0">Student Council Election</p>
                        </div>
                        <div class="card-body">
                           <form id="ballotForm" method="POST" action="submit_vote.php">
                           <input type="hidden" name="election_id" value="<?= htmlspecialchars($row['election_id']) ?>">
                               <?php if (empty($candidates)): ?>
                                   <div class="alert alert-info">No candidates available at this time.</div>
                               <?php else: ?>
                                   <?php foreach ($candidates as $position => $candidateList): ?>
                                       <div class="position-section mb-4">
                                           <div class="position-header bg-light p-2 rounded">
                                               <h4 class="position-title mb-1"><?= strtoupper(htmlspecialchars($position)) ?></h4>
                                               <p class="text-muted small mb-0">Vote for one (1) candidate only</p>
                                           </div>
                                           <div class="candidates-list mt-2 ps-3">
                                               <?php foreach ($candidateList as $candidate): ?>
                                                   <div class="candidate-option mb-2">
                                                       <div class="form-check">
                                                           <input class="form-check-input" type="radio" 
                                                                  name="vote[<?= htmlspecialchars(strtolower(str_replace(' ', '_', $position))) ?>]" 
                                                                  id="candidate_<?= $candidate['candidate_id'] ?>" 
                                                                  value="<?= htmlspecialchars($candidate['candidate_id']) ?>" required>
                                                           <label class="form-check-label" for="candidate_<?= $candidate['candidate_id'] ?>">
                                                               <strong><?= htmlspecialchars($candidate['name']) ?></strong>
                                                               <small class="text-muted">
                                                                   (<?= htmlspecialchars($candidate['course_name']) ?> - 
                                                                   <?= htmlspecialchars($candidate['year_section']) ?>)
                                                               </small>
                                                           </label>
                                                       </div>
                                                   </div>
                                               <?php endforeach; ?>
                                           </div>
                                       </div>
                                   <?php endforeach; ?>
                           
                                   <div class="text-center mt-4">
                                       <button type="submit" class="btn btn-primary btn-lg px-5">Cast Vote</button>
                                   </div>
                               <?php endif; ?>
                           </form>
                           
                        </div>
                        <div class="card-footer text-center">
                            <small class="text-muted">Please review your choices carefully before submitting</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
            document.querySelector("#sidebar").classList.toggle("expand");
        });
    </script>
</body>

</html>
