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

<style>
    .election-container {
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        transition: transform 0.2s;
    }

    .election-container:hover {
        transform: translateY(-2px);
    }

    .election-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
    }

    .election-details {
        font-size: 16px;
        color: #555;
        margin-bottom: 20px;
    }

    .election-details p {
        margin-bottom: 8px;
    }

    .election-details strong {
        color: #333;
    }

    .view-ballot-btn {
        margin-top: 10px;
        padding: 10px 20px;
        font-weight: 500;
    }

    .text-success {
        color: #28a745 !important;
        font-weight: bold;
    }

    .text-danger {
        color: #dc3545 !important;
        font-weight: bold;
    }

    .btn-secondary:disabled {
        cursor: not-allowed;
    }

    .alert {
        border-radius: 10px;
        padding: 15px 20px;
    }
</style>
<style>

    .table .table-bordered{
        text-align: center;
    }
    .id-column {
      width: 180px; 
    }

    .name-column {
      width: 450px; 
    }

    .role-column {
      width: 180px; 
    }

    .action-column {
      width: 250px; 
    }

    .search-container {
      position: relative;
      width: 50%;
      margin-left: auto;
    }

    .search-input {
      height: 40px;
      border-radius: 20px;
      padding-left: 45px;
      border: none;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .search-icon {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #888;
    }

    .title-container h1 {
      font-size: 1.5rem;
      margin: 0;
    }
</style>

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
    
        <!-- Main Content -->
        <div class="main-content container-fluid g-0">

            <!-- Title Container -->
            <div class="title-container">
                <h1>UCC-Elect: Election Events History</h1>
            </div>

<!-- Content Section -->
<div class="content container mt-4">
<?php

include('db_connection.php');

$sql = "
    SELECT 
        election.id, 
        election.election_id, 
        election.course_id, 
        election.start_datetime, 
        election.end_datetime, 
        election.status, 
        elections_identification.election_name 
    FROM 
        election
    JOIN 
        elections_identification 
    ON 
        election.election_id = elections_identification.election_id
    WHERE 
        election.status = 'INACTIVE'
    ORDER BY 
        election.start_datetime DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Error fetching election events: " . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $start_datetime = date('F d, Y h:i A', strtotime($row['start_datetime']));
        $end_datetime = date('F d, Y h:i A', strtotime($row['end_datetime']));
        ?>
        <div class="election-container mb-4">
            <div class="election-title">
                Election ID: <?php echo htmlspecialchars($row['election_id']); ?> <br>
                Election Name: <?php echo htmlspecialchars($row['election_name']); ?>
            </div>
            <div class="election-details">
                <p><strong>Start DateTime:</strong> <?php echo $start_datetime; ?></p>
                <p><strong>End DateTime:</strong> <?php echo $end_datetime; ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($row['course_id']); ?></p>
                <p><strong>Status:</strong> <span class="text-danger"><?php echo htmlspecialchars($row['status']); ?></span></p>
            </div>
            <a href="election_history.php?election_id=<?= $row['election_id']; ?>" 
               class="btn btn-primary view-ballot-btn">Go to History Record</a>
        </div>
        <?php
    }
} else {
    echo '<div class="alert alert-info">No inactive election events available.</div>';
}
?>


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