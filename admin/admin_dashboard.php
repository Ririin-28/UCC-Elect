<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/ucc-elect_administrator_login.php");
    exit;
}

$adminID = $_SESSION['admin_id'];

$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$totalAccountsQuery = "SELECT (SELECT COUNT(*) FROM facilitator) + (SELECT COUNT(*) FROM student) AS totalAccounts";
$totalFacilitatorsQuery = "SELECT COUNT(*) AS totalFacilitators FROM facilitator";
$totalStudentsQuery = "SELECT COUNT(*) AS totalStudents FROM student";

$totalAccountsResult = $conn->query($totalAccountsQuery);
$totalFacilitatorsResult = $conn->query($totalFacilitatorsQuery);
$totalStudentsResult = $conn->query($totalStudentsQuery);

$totalAccounts = $totalAccountsResult->fetch_assoc()['totalAccounts'] ?? 0;
$totalFacilitators = $totalFacilitatorsResult->fetch_assoc()['totalFacilitators'] ?? 0;
$totalStudents = $totalStudentsResult->fetch_assoc()['totalStudents'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCC-Elect: Admin</title>
    <link rel="icon" href="../images/UCC-Elect_Logo.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <img src="../images/UCC-Elect_Logo2.png" alt="Toggle Sidebar" class="custom-logo">
                </button>
                <div class="sidebar-logo">
                    <a href="admin_dashboard.php">Admin</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="admin_dashboard.php" class="sidebar-link">
                        <i class="lni lni-layout"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#accounts" aria-expanded="false" aria-controls="accounts">
                        <i class="lni lni-users"></i>
                        <span>Accounts</span>
                    </a>
                       <ul id="accounts" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="admin_students_account.php" class="sidebar-link"><i class=""></i>Students</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="admin_facilitators_account.php" class="sidebar-link"><i class=""></i>Facilitators</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="admin_coordinators_account.php" class="sidebar-link"><i class=""></i>Coordinators</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#approval" aria-expanded="false" aria-controls="approval">
                        <i class="bi bi-person-check"></i>
                        <span>Approvals</span>
                    </a>
                    <ul id="approval" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="admin_pending_requests.php" class="sidebar-link"><i class=""></i>Pending Requests</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="admin_add_facilitator.php" class="sidebar-link"><i class=""></i>Add Facilitator</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="admin_add_coordinator.php" class="sidebar-link"><i class=""></i>Add Coordinator</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#archive" aria-expanded="false" aria-controls="archive">
                        <i class="bi bi-archive"></i>
                        <span>Archive</span>
                    </a>
                    <ul id="archive" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="admin_archive_students.php" class="sidebar-link"><i class=""></i>Archive Students</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="admin_archive_facilitators.php" class="sidebar-link"><i class=""></i>Archive Facilitators</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="admin_archive_coordinators.php" class="sidebar-link"><i class=""></i>Archive Coordinators</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../logout.php" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content container-fluid g-0">
            <!-- Title Container -->
            <div class="title-container">
                <h1>UCC-Elect: Administrator Dashboard</h1>
            </div>

            <!-- Card Containers -->
            <div class="content container mt-4">
                <div class="row g-3 cardBox">
                    <h5>Overview</h5>
                    <!-- Card 1 -->
                    <div class="col-md-3 card">
                        <div class="text-center p-3">
                            <div>
                                <h5 class="card-title numbers" id="totalAccounts"><?php echo $totalAccounts; ?></h5>
                                <p class="card-text cardName">Total Accounts</p>
                            </div>
                            <div>
                                <img src="assets/imgs/studentdbrd.png" alt="" class="img-fluid" style="width: 50px;">
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="col-md-3 card">
                        <div class="text-center p-3">
                            <div>
                                <h5 class="card-title numbers" id="totalFacilitators"><?php echo $totalFacilitators; ?></h5>
                                <p class="card-text cardName">Total Facilitators</p>
                            </div>
                            <div>
                                <img src="assets/imgs/coursedbrd.png" alt="" class="img-fluid" style="width: 50px;">
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="col-md-3 card">
                        <div class="text-center p-3">
                            <div>
                                <h5 class="card-title numbers" id="totalStudents"><?php echo $totalStudents; ?></h5>
                                <p class="card-text cardName">Total Students</p>
                            </div>
                            <div>
                                <img src="assets/imgs/sectiondbrd.png" alt="" class="img-fluid" style="width: 50px;">
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
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
