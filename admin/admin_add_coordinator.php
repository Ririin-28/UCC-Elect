<?php

session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/ucc-elect_administrator_login.php");
    exit;
}

$adminID = $_SESSION['admin_id'];

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

    $coord_id = $_POST['coord_id'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $first_name = $_POST['first_name'] ?? null;
    $middle_name = !empty($_POST['middle_name']) ? $_POST['middle_name'] : null;
    $email = $_POST['email'] ?? null;
    $contact_number = $_POST['contact_number'] ?? null;
    $coord_password = $coord_id;

    if (!$coord_id || !$last_name || !$first_name || !$email || !$contact_number) {
        echo json_encode([
            "status" => "error",
            "message" => "All fields are required."
        ]);
        exit;
    }

    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $coord_id)) {
        echo json_encode([
            "status" => "error",
            "message" => "Coordinator ID must follow the format: '40440xxx-C'"
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email address."
        ]);
        exit;
    }

    if (!preg_match('/^[0-9]{11}$/', $contact_number)) {
        echo json_encode([
            "status" => "error",
            "message" => "Contact Number must be 11 digits."
        ]);
        exit;
    }

    try {
        $stmt = $conn->prepare("CALL add_coordinator(?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Statement preparation failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssss",
            $coord_id,
            $last_name,
            $first_name,
            $middle_name,
            $email,
            $contact_number,
            $coord_password
        );

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Coordinator added successfully."
            ]);
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    } finally {
        $conn->close();
    }
    exit;
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
                <h1>Approvals: Add Coordinator</h1>
            </div>

            <div class="container">
                <div class="card p-4">
                    <form id="add-coordinator-form">
                        <div class="col-md-6 mb-3">
                            <label for="coord_id" class="form-label">Coordinator ID</label>
                            <input type="text" class="form-control" id="coord_id" name="coord_id" placeholder="Enter coordinator ID (e.g. 40440123-C)" required>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-4">
                                <label for="last-name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="first-name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle-name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Enter Middle Name">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Coordinator Email" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Enter Contact Number" required>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                   <button type="submit" class="btn btn-success" style="width: 400px; height: 45px">Add Coordinator</button>
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

        document.getElementById("add-coordinator-form").addEventListener("submit", function (event) {
            event.preventDefault(); 

            const formData = new FormData(this);

            fetch("admin_add_coordinator.php", {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === "success") {
                        alert(data.message);
                        this.reset(); 
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred: " + error.message);
                });
        });

  function validateCoordinatorID() {
  const coordinatorID = document.getElementById("coord_id").value;
  const pattern = /^40440\d{3}-C$/;

  if (pattern.test(coordinatorID)) {
    document.getElementById("coord_id").setCustomValidity("");
  } else {
    let message = "Invalid Coordinator ID format. Please use 40440xxx-C.";
    if (coordinatorID.length < 9) {
      message = "Coordinator ID is too short. It should be 10 characters long (40440xxx-C).";
    } else if (coordinatorID.length > 9) {
      message = "Coordinator ID is too long. It should be 10 characters long (40440xxx-C).";
    } else if (!coordinatorID.startsWith("404")) {
      message = "Coordinator ID must start with '404'.";
    } else if (!coordinatorID.endsWith("-F")) {
      message = "Coordinator ID must end with '-C'.";
    } else {
      message = "Coordinator ID contains invalid characters. Please use only digits (0-9) in the middle section.";
    }
    document.getElementById("coord_id").setCustomValidity(message);
  }
}

document.getElementById("coord_id").addEventListener("input", validateCoordinatorID);
    </script>
</body>

</html>
