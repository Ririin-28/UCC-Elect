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
    die("Database connection failed: " . $conn->connect_error);
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accept'])) {
        $student_id = $_POST['student_id'];
    
        $acceptQuery = "CALL accept_student_request(?)";
        $stmt = $conn->prepare($acceptQuery);
        if (!$stmt) {
            die("Failed to prepare statement: " . $conn->error);
        }
    
        $stmt->bind_param("s", $student_id);
        try {
            $stmt->execute();
            $stmt->close();
            $success = true;
        } catch (mysqli_sql_exception $e) {
            error_log("Error executing procedure: " . $e->getMessage());
            echo "Failed to process the request. Please try again.";
        }
    }
    
    if (isset($_POST['decline'])) {
        $student_id = $_POST['student_id'];
        $declineQuery = "CALL decline_student_request(?)";
        $stmt = $conn->prepare($declineQuery);
        if (!$stmt) {
            die("Stored Procedure Preparation Failed: " . $conn->error);
        }
        $stmt->bind_param("s", $student_id);
        try {
            $stmt->execute();
            $stmt->close();
            header("Location: admin_pending_requests.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            error_log("Error executing decline_student_request: " . $e->getMessage());
            echo "Failed to process request. Please contact admin.";
        }
    }
}

$query = "SELECT * FROM view_pending_requests";
$result = $conn->query($query);
if (!$result) {
    die("Query Failed: " . $conn->error);
}
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

<style>

    /* --------------- Table --------------- */
    .table .table-bordered{
        text-align: center;
    }
    .student-id-column {
        width: 180px;
    }

    .name-column {
        width: 450px;
    }

    .section-column {
        width: 150px; 
    }

    .course-column {
        width: 180px; 
    }
    
    .action-column {
        width: 250px; 
    }

    /* --------------- Dropdown --------------- */

    .dropdown-councils {
        background-color: #10812f;  
        border: none;               
        color: white;             
        outline: none;          
    }

    .dropdown-councils:hover,
    .dropdown-councils:focus,
    .dropdown-councils:active {
        background-color: #10712d !important;
        border: none;              
        box-shadow: none;          
    }

    /* --------------- Search Bar --------------- */

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

<!------------------------------------------------------ Main Content ------------------------------------------------------>
        <div class="main-content container-fluid g-0">
            <div class="title-container">
                <h1>Pending Request</h1>
                <div class="col">
                    <div class="search-container">
                        <input id="searchInput" type="text" class="form-control search-input" placeholder="Search...">
                        <i class="bi bi-search search-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Table -->
<div class="container mt-3">
    <h2>Pending List</h2>  
    <table id="studentTable" class="table table-bordered text-center">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Course</th>
                <th>Year-Section</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['student_id']; ?></td>
                    <td><?php echo $row['last_name'] . ", " . $row['first_name'] . " " . $row['middle_name']; ?></td>
                    <td><?php echo $row['gender']; ?></td>
                    <td><?php echo $row['course_name']; ?></td>
                    <td><?php echo $row['year_section']; ?></td>
                    <td>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#acceptModal" 
                            onclick="setModalAction('accept', '<?php echo $row['student_id']; ?>')">Accept</button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#declineModal" 
                            onclick="setModalAction('decline', '<?php echo $row['student_id']; ?>')">Decline</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<div class="modal fade" id="acceptModal" tabindex="-1" aria-labelledby="acceptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="acceptModalLabel">Confirm Accept</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to <strong>accept</strong> this student's request?
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="student_id" id="accept_student_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="accept" class="btn btn-success">Accept</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="declineModalLabel">Confirm Decline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to <strong>decline</strong> this student's request?
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="student_id" id="decline_student_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="decline" class="btn btn-danger">Decline</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function setModalAction(action, student_id) {
        if (action === 'accept') {
            document.getElementById('accept_student_id').value = student_id;
        } else if (action === 'decline') {
            document.getElementById('decline_student_id').value = student_id;
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
            document.querySelector("#sidebar").classList.toggle("expand");
        });
        document.getElementById("searchInput").addEventListener("keyup", function() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toLowerCase();
            table = document.getElementById("studentTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
