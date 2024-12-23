<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login/ucc-elect_student_login.php");
    exit;
}

$studentID = $_SESSION['student_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("CALL get_student_details(?)");
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();

$studentDetails = [];
if ($result->num_rows > 0) {
    $studentDetails = $result->fetch_assoc();
} else {
    die("No details found for this student. Ensure the student ID exists.");
}

$stmt->close();
$conn->close();
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


<style>
        .password-wrapper {
            position: relative;
            height: 40px;
        }
        input[type="password"], input[type="text"] {
            width: 100%;
            height: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .buttons button {
            width: 48%;
        }

    

        .password-wrapper {
            position: relative;
        }

        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%) scale(0.8);
            cursor: pointer;
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
    <div class="main-content container-fluid g-0">
            <div class="title-container">
                <h1>UCC-Elect: Student's Profile</h1>
            </div>

            <!-- Content Section -->
            <div class="content container mt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card p-4">
                            <h5 class="card-title mb-4">Student Information</h5>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>Student ID:</strong> <?php echo htmlspecialchars($studentDetails['student_id']); ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Last Name:</strong> <?php echo htmlspecialchars($studentDetails['last_name']); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>First Name:</strong> <?php echo htmlspecialchars($studentDetails['first_name']); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Middle Name:</strong> <?php echo htmlspecialchars($studentDetails['middle_name']); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Gender:</strong> <?php echo htmlspecialchars($studentDetails['gender']); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Course:</strong> <?php echo htmlspecialchars($studentDetails['course_name']); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Year-Section:</strong> <?php echo htmlspecialchars($studentDetails['year_section']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
            document.querySelector("#sidebar").classList.toggle("expand");
        });
    </script>
</body>

</html>
