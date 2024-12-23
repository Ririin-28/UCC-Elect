<?php
session_start();
include('../db_connection.php');

$sql = "
    SELECT 
        view_election_event.*, 
        elections_identification.election_name 
    FROM 
        view_election_event
    JOIN 
        elections_identification 
    ON 
        view_election_event.election_id = elections_identification.election_id
    ORDER BY 
        view_election_event.start_datetime DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Error fetching election events: " . $conn->error);
}
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
                    <a href="voter_election_event.php" class="sidebar-link">
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

        <!------------------------------------------------------ Main Content ------------------------------------------------------>

        <!-- Main Content -->
        <div class="main-content container-fluid g-0">

            <!-- Title Container -->
            <div class="title-container">
                <h1>UCC-Elect: Election Events</h1>
            </div>

<!-- Content Section -->
<div class="content container mt-4">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format dates for display
        $start_datetime = date('F d, Y h:i A', strtotime($row['start_datetime']));
        $end_datetime = date('F d, Y h:i A', strtotime($row['end_datetime']));
        $status_class = ($row['status'] == 'Active') ? 'text-success' : 'text-danger';
        ?>
        <div class="election-container mb-4">
            <!-- Display election_id and election_name -->
            <div class="election-title">
                Election ID: <?php echo htmlspecialchars($row['election_id']); ?> <br> Election Name: <?php echo htmlspecialchars($row['election_name']); ?>
            </div>
            <div class="election-details">
                <p><strong>Start DateTime:</strong> <?php echo $start_datetime; ?></p>
                <p><strong>End DateTime:</strong> <?php echo $end_datetime; ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($row['course_id']); ?></p>
                <p><strong>Status:</strong> <span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></p>
            </div>
            <?php if ($row['status'] == 'ACTIVE') { ?>
                <a href="voter_e-ballot.php?election_id=<?= $row['election_id']; ?>" 
                   class="btn btn-primary view-ballot-btn">Go to E-Ballot</a>
            <?php } else { ?>
                <button class="btn btn-secondary" disabled>Election Not Active</button>
            <?php } ?>
        </div>
        <?php
    }
} else {
    echo '<div class="alert alert-info">No election events available.</div>';
}
?>

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
