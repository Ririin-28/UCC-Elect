<?php

session_start();

if (!isset($_SESSION['coord_id'])) {
    header("Location: ../login/ucc-elect_coordinator_login.php");
    exit;
}

$coordinatorID = $_SESSION['coord_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); 

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ucc-elect";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo json_encode([
            "status" => "error",
            "message" => "Database connection failed: " . $conn->connect_error
        ]);
        exit;
    }

    $student_id = $_POST['student_id'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $first_name = $_POST['first_name'] ?? null;
    $middle_name = !empty($_POST['middle_name']) ? $_POST['middle_name'] : null;
    $gender = $_POST['gender'] ?? null;
    $course_description = $_POST['course_description'] ?? null;
    $year_section = $_POST['yearandsec'] ?? null;

    if (!$student_id || !$last_name || !$first_name || !$gender || !$course_description || !$year_section) {
        echo json_encode([
            "status" => "error",
            "message" => "All required fields must be filled."
        ]);
        exit;
    }

    $student_password = $student_id;

    try {
        $stmt = $conn->prepare("CALL add_student(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssss",
            $student_id,
            $last_name,
            $first_name,
            $middle_name,
            $gender,
            $course_description,
            $year_section,
            $student_password
        );

        $response = [];
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Student added successfully!';
            $response['student_id'] = $student_id;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error adding student: ' . $stmt->error;
        }
    } catch (mysqli_sql_exception $e) {
        $response = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    } finally {
        $stmt->close();
        $conn->close();
    }

    echo json_encode($response);
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCC-Elect: Coordinator</title>
    <link rel="icon" href="../images/UCC-Elect_Logo.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/coordinator_dashboard.css">
</head>


<style>

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
                    <a href="coordinator_import_candidate.php">Coordinator</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="coordinator_import_candidate.php" class="sidebar-link">
                        <i class="bi bi-file-earmark-medical"></i>
                        <span>Import Candidates List</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#registration" aria-expanded="false" aria-controls="registration">
                        <i class="bi bi-person-add"></i>
                        <span>Voters Registration</span>
                    </a>
                    <ul id="registration" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="coordinator_import_class.php" class="sidebar-link"><i class=""></i>Import Class List</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="coordinator_add_voter.php" class="sidebar-link"><i class=""></i>Add Student</a>
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
                <h1>Voters Registration: Add Student</h1>
            </div>
            <div class="container">
                <div class="card p-4">
                    <h4 class="mb-4">Add Student</h4>
                    <div class="container mt-4">
                        <form id="add-student-form" method="POST">
                            <!-- Voter Name -->
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student ID</label>
                                <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter Student ID (e.g. 202xxxxx-N)" required>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-md-4">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Enter Middle Name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="" disabled selected>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="course_description" class="form-label">Course</label>
                                    <select class="form-select" id="course_description" name="course_description" required>
                                        <option value="" disabled selected>Select Course</option>
                                        <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                                        <option value="Bachelor of Science in Information Systems">Bachelor of Science in Information Systems</option>
                                        <option value="Bachelor of Science in Computer Science">Bachelor of Science in Computer Science</option>
                                        <option value="Bachelor of Science in Entertainment and Multimedia Computing">Bachelor of Science in Entertainment and Multimedia Computing</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="yearandsec" class="form-label">Year-Section</label>
                                    <input type="text" class="form-control" id="yearandsec" name="yearandsec" placeholder="Enter Year-Section (e.g. 1-A)" required>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <button type="submit" class="btn btn-success" style="width: 400px; height: 45px">Add Student
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalMessage">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showModal(status, message) {
        const modalLabel = document.getElementById("statusModalLabel");
        const modalMessage = document.getElementById("modalMessage");

        if (status === "success") {
            modalLabel.textContent = "Success!";
            modalLabel.classList.add("text-success");
            modalLabel.classList.remove("text-danger");
        } else {
            modalLabel.textContent = "Error!";
            modalLabel.classList.add("text-danger");
            modalLabel.classList.remove("text-success");
        }
        modalMessage.textContent = message;

        const statusModal = new bootstrap.Modal(document.getElementById("statusModal"));
        statusModal.show();
    }

    document.getElementById("add-student-form").addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const response = await fetch("coordinator_add_voter.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.json();

        showModal(result.status, result.message);

        if (result.status === "success") {
            this.reset();
    }
});

</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateStudentID() {
            const studentID = document.getElementById("student_id").value;
            const pattern = /^202\d{5}-N$/;

            if (pattern.test(studentID)) {
                document.getElementById("student_id").setCustomValidity("");
            } else {
                let message = "Invalid Student ID format. Please use 202xxxxx-N.";
                if (studentID.length < 9) {
                    message = "Student ID is too short. It should be 10 characters long (202xxxxx-N)."
                } else if (studentID.length > 9) {
                    message = "Student ID is too long. It should be 10 characters long (202xxxxx-N)."
                } else if (!studentID.startsWith("202")) {
                    message = "Student ID must start with '202'."
                } else if (!studentID.endsWith("-N")) {
                    message = "Student ID must end with '-N'."
                } else {
                    message = "Student ID contains invalid characters. Please use only digits (0-9) in the middle section."
                }
                document.getElementById("student_id").setCustomValidity(message);
            }
        }
        document.getElementById("student_id").addEventListener("input", validateStudentID);

        function validateYearAndSection() {
            const yearAndSection = document.getElementById("yearandsec").value;
            const pattern = /^[1-4]-[A-D]$/;
            if (pattern.test(yearAndSection)) {
                document.getElementById("yearandsec").setCustomValidity("");
            } else {
                let message = "Invalid Year-Section format. Please use 1-A, 2-B, 3-C, or 4-D";
                if (yearAndSection.length < 3) {
                    message = "Year-Section is too short. It should be 3 characters long (e.g., 1-A)."
                } else if (yearAndSection.length > 3) {
                    message = "Year-Section is too long. It should be 3 characters long (e.g., 1-A)."
                } else if (!yearAndSection.includes("-")) {
                    message = "Year-Section must include a hyphen ('-')."
                } else if (!/^[1-4]$/.test(yearAndSection.split("-")[0])) {
                    message = "Year must be a number between 1 and 4."
                } else if (!/^[A-D]$/.test(yearAndSection.split("-")[1])) {
                    message = "Section must be a letter between A and D."
                }
                document.getElementById("yearandsec").setCustomValidity(message);
            }
        }
        document.getElementById("yearandsec").addEventListener("input", validateYearAndSection);
    </script>

<script>
    const hamBurger = document.querySelector(".toggle-btn");
    hamBurger.addEventListener("click", function () {
        document.querySelector("#sidebar").classList.toggle("expand");
});
</script>
</body>

</html>
