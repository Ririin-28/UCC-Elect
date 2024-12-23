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
<?php
$host = 'localhost';  
$username = 'root';   
$password = '';      
$dbname = 'ucc-elect'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT e.course_id, c.course_name
    FROM election e
    JOIN course c ON e.course_id = c.course_id
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $course_name = htmlspecialchars($row['course_name']);
} else {
    $course_name = 'No results found.';
}

$conn->close();
?>

<?php
$host = 'localhost';  
$username = 'root';   
$password = '';       
$dbname = 'ucc-elect'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT COUNT(*) AS total_students FROM student";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_students = $row['total_students'];
} else {
    $total_students = 0; 
}

$conn->close();
?>

<?php
$host = 'localhost'; 
$username = 'root';  
$password = '';     
$dbname = 'ucc-elect'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT COUNT(*) AS total_votes FROM votes";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_votes = $row['total_votes'];
} else {
    $total_votes = 0; 
}

$conn->close();
?>

<?php
$host = 'localhost';  
$username = 'root'; 
$password = '';     
$dbname = 'ucc-elect'; 


$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT start_datetime, end_datetime, status FROM election LIMIT 1"; 
$result = $conn->query($sql);

$remaining_time = "N/A"; 
$end_time = null;
$status = 'ACTIVE'; 

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $start_datetime = $row['start_datetime'];
    $end_datetime = $row['end_datetime'];
    $status = $row['status'];


    if ($end_datetime) {
        $end_time = strtotime($end_datetime);
        $current_time = new DateTime();
        $end_time_obj = new DateTime($end_datetime); 
        $interval = $current_time->diff($end_time_obj);

        if ($interval->d > 0) {
            $remaining_time = $interval->d . " day(s) " . $interval->h . ":" . $interval->i . ":" . $interval->s;
        } else {
            $remaining_time = $interval->h . ":" . $interval->i . ":" . $interval->s;
        }
        if ($current_time >= $end_time_obj) {
            $status = 'INACTIVE';
            $update_sql = "UPDATE election SET status = 'INACTIVE' WHERE end_datetime = '$end_datetime'";
            if ($conn->query($update_sql) === TRUE) {
            } else {
                echo "Error updating status: " . $conn->error;
            }
        }
    } else {
        $remaining_time = "Invalid End Time";
    }
} else {
    $remaining_time = "No Election Data"; 
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
                <h1>Election Analytics: Live Election</h1>
            </div>

            <!-- Card Containers -->
            <div class="content container mt-4">
                <div class="row g-3 cardBox">
                    
                    <!-- Card 1 -->
                    <div class="col-md-3 card">
                        <div class="text-center p-3">
                            <div>
                               
                                <h5 class="card-title numbers"><?php echo $course_name; ?></h5>
                                <th class="card-text cardName">Course</th>
                            </div>
                            <div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="col-md-3 card">
                        <div class="text-center p-3">
                            <div>
                               
                                <h5 class="card-title numbers"><?php echo $total_students; ?></h5>
                                <p class="card-text cardName">Total Students</p>
                            </div>
                            <div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="col-md-3 card">
                        <div class="text-center p-3">
                            <div>
                                
                                <h5 class="card-title numbers"><?php echo $total_votes; ?></h5>
                                <p class="card-text cardName">Votes Casted</p>
                            </div>
                            <div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="col-md-3 card">
                        <div class="text-center p-3">
                            <div>
                                
                                <h5 class="card-title numbers" id="remaining-time"><?php echo $remaining_time; ?></h5>
                                <p class="card-text cardName">Remaining Time</p>
                            </div>
                            <div>
                            </div>
                        </div>
                    </div>
                <div class="position-section">
                    <h4>President</h4>
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

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 1";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatesp<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatesp<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php

$conn->close();
?> 


                </div>

                <div class="position-section">
                    <h4>Vice President</h4>
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
    WHERE c.position_id = 2
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 2";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatesvp<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatesvp<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php

$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Secretary</h4>
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
    WHERE c.position_id = 3
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 3";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatessec<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatessec<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Treasurer</h4>
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
    WHERE c.position_id = 4
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);
if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 4";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatestrea<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatestrea<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Auditor</h4>
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
    WHERE c.position_id = 5
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 5";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatesaudit<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatesaudit<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Business Manager</h4>
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
    WHERE c.position_id = 6
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";
$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 6";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatesbm<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatesbm<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Creative Committee</h4>
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
    WHERE c.position_id = 7
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 7";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatescc<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatescc<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>First Year Representative</h4>
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
    WHERE c.position_id = 8
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 8";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatesfyr<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatesfyr<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Second Year Representative</h4>
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
    WHERE c.position_id = 9
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 9";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatesyr<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatesyr<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Third Year Representative</h4>
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
    WHERE c.position_id = 10
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 10";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidatestyr<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidatestyr<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 
                </div>

                <div class="position-section">
                    <h4>Fourth Year Representative</h4>
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
    WHERE c.position_id = 11
    GROUP BY c.candidate_id, c.last_name, c.first_name, c.middle_name
";

$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<table class="table table-bordered">
    <thead class="table-primary">
        <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 1;
        $totalVotesQuery = "SELECT COUNT(*) AS total_votes FROM votes WHERE position_id = 11";
        $totalVotesResult = $conn->query($totalVotesQuery);
        $totalVotes = $totalVotesResult->fetch_assoc()['total_votes'];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = $totalVotes > 0 ? round(($row['votes'] / $totalVotes) * 100, 2) : 0;
                $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                ?>
                <tr>
                    <td><?php echo $fullName; ?></td>
                    <td id="candidate<?php echo $counter; ?>Votes"><?php echo $row['votes']; ?></td>
                    <td id="candidate<?php echo $counter; ?>Percentage"><?php echo $percentage; ?>%</td>
                </tr>
                <?php
                $counter++;
            }
        } else {
            echo '<tr><td colspan="3">No candidates found</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
$conn->close();
?> 

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
<script>

    var endTime = <?php echo $end_time ? $end_time * 1000 : 'null'; ?>; 
    
    function updateRemainingTime() {
        if (endTime) {
            var currentTime = new Date().getTime(); 
            var remainingTime = endTime - currentTime; 
            
            if (remainingTime <= 0) {
                document.getElementById('remaining-time').innerText = "00:00:00"; 
            } else {
                var days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
                var hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60)); 
                var seconds = Math.floor((remainingTime % (1000 * 60)) / 1000); 
                var formattedTime = '';
                if (days > 0) {
                    formattedTime += days + " day(s) ";
                }
                formattedTime += hours + ":" + minutes + ":" + seconds + "";
                
                document.getElementById('remaining-time').innerText = formattedTime; 
            }
        }
    }

    setInterval(updateRemainingTime, 1000); 
</script>

<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_p.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatesp' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatesp' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes; 
                        percentageCell.textContent = candidate.percentage + '%'; 
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_vp.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatesvp' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatesvp' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;  
                        percentageCell.textContent = candidate.percentage + '%'; 
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_sec.php', true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatessec' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatessec' + candidate.counter + 'Percentage');
                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;  
                        percentageCell.textContent = candidate.percentage + '%';  
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_audit.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatesaudit' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatesaudit' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;  
                        percentageCell.textContent = candidate.percentage + '%'; 
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_trea.php', true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatestrea' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatestrea' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;
                        percentageCell.textContent = candidate.percentage + '%'; 
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_bm.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatesbm' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatesbm' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;  
                        percentageCell.textContent = candidate.percentage + '%'; 
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_cc.php', true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatescc' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatescc' + candidate.counter + 'Percentage');
hem
                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes; 
                        percentageCell.textContent = candidate.percentage + '%'; 
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_fyr.php', true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatesfyr' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatesfyr' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes; 
                        percentageCell.textContent = candidate.percentage + '%';  
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_syr.php', true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatesyr' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatesyr' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;  
                        percentageCell.textContent = candidate.percentage + '%';  
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>


<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes_tyr.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidatestyr' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidatestyr' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;  
                        percentageCell.textContent = candidate.percentage + '%';  
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>



<script>
    function updateVotesAndPercentages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'update_votes.php', true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                data.forEach(function(candidate) {
                    var voteCell = document.getElementById('candidate' + candidate.counter + 'Votes');
                    var percentageCell = document.getElementById('candidate' + candidate.counter + 'Percentage');

                    if (voteCell && percentageCell) {
                        voteCell.textContent = candidate.votes;  
                        percentageCell.textContent = candidate.percentage + '%'; 
                    } else {
                        console.warn('Element not found for candidate ' + candidate.counter);
                    }
                });
            } else {
                console.error('Failed to fetch updated votes. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while fetching updated votes.');
        };
        xhr.send();
    }

    setInterval(updateVotesAndPercentages, 1000);
</script>

</body>

</html>