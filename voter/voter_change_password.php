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

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        $stmt = $conn->prepare("CALL change_student_password(?, ?, ?, @status)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $studentID, $old_password, $new_password);

        if (!$stmt->execute()) {
            $error = "Failed to execute the stored procedure.";
        } else {
            $result = $conn->query("SELECT @status AS status");
            if ($result && $row = $result->fetch_assoc()) {
                $status = $row['status'];

                if ($status === 'Password updated successfully.') {
                    $success = $status;
                } else {
                    $error = $status;
                }
            } else {
                $error = "Failed to retrieve procedure status.";
            }
        }

        $stmt->close();
    }
}

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
            top: 40%;
            transform: translateY(-50%) scale(0.8);
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
                <h1>Change Password</h1>
            </div>

            <div class="content container mt-4">
                <div class="row">
                    <div class="col-6 mx-auto">
                        <div class="card p-4">
                            <h5 class="card-title mb-4">Update Your Password</h5>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <label for="old_password">Old Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="old_password" name="old_password" required>
                                    <img src="https://cdn-icons-png.flaticon.com/512/709/709612.png" 
                                         alt="Eye Icon" class="eye-icon" width="20" 
                                         onclick="togglePassword('old_password')">
                                </div>

                                <label for="new-password">New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="new_password" name="new_password" required>
                                    <img src="https://cdn-icons-png.flaticon.com/512/709/709612.png" 
                                         alt="Eye Icon" class="eye-icon" width="20" 
                                         onclick="togglePassword('new_password')">
                                </div>

                                <label for="confirm-password">Confirm New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <img src="https://cdn-icons-png.flaticon.com/512/709/709612.png" 
                                         alt="Eye Icon" class="eye-icon" width="20" 
                                         onclick="togglePassword('confirm_password')">
                                </div>

                                <div class="buttons">
                                    <button type="reset" class="btn btn-secondary">Clear Entries</button>
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            if (field.type === "password") {
                field.type = "text";
            } else {
                field.type = "password";
            }
        }
        const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
            document.querySelector("#sidebar").classList.toggle("expand");
        });
    </script>
</body>
</html>