<?php

session_start();

if (!isset($_SESSION['coord_id'])) {
    header("Location: ../login/ucc-elect_coordinator_login.php");
    exit;
}

$coordID = $_SESSION['coord_id'];

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
    <title>UCC-Elect: Coordinator</title>
    <link rel="icon" href="../images/UCC-Elect_Logo.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/coordinator_dashboard.css">
</head>

<style>
    .alert-info {
        background-color: #80ef80;
        color: black;
        border: none;              
    }

    .upload-btn {
        background-color: #10812f;
        color: white;    
    }

    .upload-btn:hover,
    .upload-btn:focus,
    .upload-btn:active {
        background-color: #10712d !important;      
        color: white;  
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

        <!-- Main Content -->
        <div class="main-content container-fluid g-0">

            <!-- Title Container -->
            <div class="title-container">
                <h1>Coordinator: Import Candidates List</h1>
            </div>

            <!-- Reminder and Upload Form -->
            <div class="container mt-3">
                <div class="alert alert-info">
                    <strong>Reminder:</strong> Please upload an Excel or CSV file with the following format:
                    <ul>
                        <li><strong>Header:</strong> Election Name, Last Name, First Name, Middle Name, Position, Course, Year-Section </li>
                        <li><strong>Example:</strong> IS-2024, Dela Cruz, Juan, Fernandez, President, BSCS, 1-A</li>
                        <li>Ensure no special characters are used and all fields are properly filled.</li>
                    </ul>
                </div>

                <div class="upload-container border p-4 rounded shadow">
                    <?php
                        if (isset($_SESSION['message'])) {
                        echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
                        unset($_SESSION['message']);
                        }
                    ?>
                    <form action="import_candidate.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="import_file" class="form-label">Upload File</label>
                            <input type="file" name="import_file" id="import_file" class="form-control" accept=".xls, .csv, .xlsx" required>
                        </div>
                        <div class="mt-4 text-center">
                            <button type="submit" name="save_excel_data" class="btn btn-success" style="width: 400px; height: 45px">Import</button>
                        </div>
                    </form>
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
