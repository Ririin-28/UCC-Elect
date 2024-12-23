<?php

session_start();

if (!isset($_SESSION['facilitator_id'])) {
    header("Location: ../login/ucc-elect_facilitator_login.php");
    exit;
}

$facilitatorID = $_SESSION['facilitator_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$overview = [];
$courseStats = [];

if ($conn->multi_query("CALL get_facilitator_dashboard()")) {
    if ($result = $conn->store_result()) {
        while ($row = $result->fetch_assoc()) {
            $overview = $row;
        }
        $result->free();
    }

    if ($conn->next_result()) {
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                $courseStats[] = $row;
            }
            $result->free();
        }
    }
} else {
    die("Error executing stored procedure: " . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCC-Elect: Facilitator</title>
    <link rel="icon" href="../images/UCC-Elect_Logo.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/facilitator_dashboard.css">
</head>


<body>
<div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <img src="../images/UCC-Elect_Logo2.png" alt="Toggle Sidebar" class="custom-logo">
                </button>
                <div class="sidebar-logo">
                    <a href="facilitator_dashboard.php">Facilitator</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="facilitator_dashboard.php" class="sidebar-link">
                        <i class="lni lni-layout"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#election" aria-expanded="false" aria-controls="election">
                        <i class="bi bi-graph-up"></i>
                        <span>Election Analytics</span>
                    </a>
                    <ul id="election" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="facilitator_live_election.php" class="sidebar-link"><i class=""></i>Live Election</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="facilitator_add_election_event.php" class="sidebar-link"><i class=""></i>Add Election Event</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="facilitator_student-voters_list.php" class="sidebar-link">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Student Voters List</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="facilitator_history.php" class="sidebar-link">
                        <i class="bi bi-archive"></i>
                        <span>History</span>
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

    <!------------------------------------------------------ Main Content ------------------------------------------------------>

    <div class="main-content container-fluid g-0">

<!-- Title Container -->
<div class="title-container">
    <h1>UCC-Elect: Facilitator Dashboard</h1>
</div>

<!-- Card Containers -->
<div class="content container mt-4">
    <div class="row g-3 cardBox">
        <h5>Overview</h5>
        <div class="col-md-3 card">
            <div class="text-center p-3">
                <div>
                    <h5 class="card-title numbers"><?= $overview['total_courses'] ?? 0 ?></h5>
                    <p class="card-text cardName">Total Courses</p>
                </div>
                <div>
                    <img src="assets/imgs/studentdbrd.png" alt="" class="img-fluid" style="width: 50px;">
                </div>
            </div>
        </div>

        <div class="col-md-3 card">
            <div class="text-center p-3">
                <div>
                    <h5 class="card-title numbers"><?= $overview['total_candidates'] ?? 0 ?></h5>
                    <p class="card-text cardName">Total Candidates</p>
                </div>
                <div>
                    <img src="assets/imgs/coursedbrd.png" alt="" class="img-fluid" style="width: 50px;">
                </div>
            </div>
        </div>

        <div class="col-md-3 card">
            <div class="text-center p-3">
                <div>
                    <h5 class="card-title numbers"><?= $overview['total_students'] ?? 0 ?></h5>
                    <p class="card-text cardName">Total Students</p>
                </div>
                <div>
                    <img src="assets/imgs/sectiondbrd.png" alt="" class="img-fluid" style="width: 50px;">
                </div>
            </div>
        </div>

        <div class="col-md-3 card">
            <div class="text-center p-3">
                <div>
                    <?php
                    date_default_timezone_set('Asia/Manila');
                    $date = new DateTime();
                    echo '<h5 class="card-title numbers">' . $date->format('F j, Y') . '</h5>';
                    ?>
                    <p class="card-text cardName">Date Today</p>
                </div>
            </div>
        </div>

        <?php if (!empty($courseStats)): ?>
            <?php foreach ($courseStats as $course): ?>
                <h5><?= htmlspecialchars($course['course_name']) ?></h5>
                <div class="col-md-3 card">
                    <div class="text-center p-3">
                        <div>
                            <h5 class="card-title numbers"><?= $course['total_students'] ?></h5>
                            <p class="card-text cardName">Total Students</p>
                        </div>
                        <div>
                            <img src="assets/imgs/<?= strtolower(str_replace(' ', '', $course['course_name'])) ?>.png" alt="" class="img-fluid" style="width: 50px;">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 card">
                    <div class="text-center p-3">
                        <div>
                            <h5 class="card-title numbers"><?= $course['total_candidates'] ?></h5>
                            <p class="card-text cardName">Total Candidates</p>
                        </div>
                        <div>
                            <img src="assets/imgs/<?= strtolower(str_replace(' ', '', $course['course_name'])) ?>.png" alt="" class="img-fluid" style="width: 50px;">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 card">
                    <div class="text-center p-3">
                        <div>
                            <h5 class="card-title numbers">Inactive</h5>
                            <p class="card-text cardName">Event Status</p>
                        </div>
                        <div>
                            <img src="assets/imgs/<?= strtolower(str_replace(' ', '', $course['course_name'])) ?>.png" alt="" class="img-fluid" style="width: 50px;">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No course data available.</p>
        <?php endif; ?>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const hamBurger = document.querySelector(".toggle-btn");
hamBurger.addEventListener("click", function () {
    document.querySelector("#sidebar").classList.toggle("expand");
});
</script>
</body>

</html>

