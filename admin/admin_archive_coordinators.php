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
    .id-column { width: 180px; }
    .name-column { width: 450px; }
    .action-column { width: 250px; }

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
            <!-- Title Container -->
            <div class="title-container">
                <h1>Archive: Coordinators</h1>
                <div class="col">
                    <div class="search-container">
                        <input type="text" class="form-control search-input" placeholder="Search..." id="searchInput">
                        <i class="bi bi-search search-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Coordinators Table -->
            <div class="container mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Archived Coordinators List</h2>
                </div>

                <table class="table table-bordered text-center" id="archiveCoordinatorsTable">
                    <thead>
                        <tr>
                            <th class="id-column">Coordinator ID</th>
                            <th class="name-column">Name</th>
                            <th class="action-column">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">Restore Coordinator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to restore <span id="restoreCoordinatorName" class="fw-bold"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmRestore" class="btn btn-success">Restore</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function () {
            function fetchArchivedCoordinators() {
    $.ajax({
        url: '../fetch_archived_coordinators.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            let rows = '';
            if (data.length > 0) {
                data.forEach(function (coordinator) {
                    rows += `<tr>
                        <td>${coordinator.coord_id}</td>
                        <td>${coordinator.name}</td>
                        <td>
                            <button 
                                class="btn btn-success btn-sm restore-btn" 
                                data-id="${coordinator.coord_id}" 
                                data-name="${coordinator.name}">
                                Restore
                            </button>
                        </td>
                    </tr>`;
                });
            } else {
                rows = "<tr><td colspan='3'>No archived coordinators found</td></tr>";
            }
            $('#archiveCoordinatorsTable tbody').html(rows);
        },
        error: function () {
            $('#archiveCoordinatorsTable tbody').html(
                "<tr><td colspan='3'>Error fetching data</td></tr>"
            );
        }
    });
}
            fetchArchivedCoordinators();


      $(document).on('click', '.restore-btn', function () {
        const coordinatorId = $(this).data('id');
        const coordinatorName = $(this).data('name');
        $('#restoreCoordinatorName').text(coordinatorName);
        $('#confirmRestore').data('id', coordinatorId);
        $('#restoreModal').modal('show');
    });

    $('#confirmRestore').click(function () {
        const coordinatorId = $(this).data('id');

        $.ajax({
            url: '../restore_coordinator.php',
            type: 'POST',
            data: { coordinator_id: coordinatorId },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    alert(data.message);
                    $('#restoreModal').modal('hide');
                    fetchArchivedCoordinators();
                } else {
                    alert(data.message);
                }
            },
            error: function () {
                alert('Error restoring coordinator');
            }
        });
    });
});

        const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("expand");
        });

        document.getElementById("searchInput").addEventListener("keyup", function() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toLowerCase();
            table = document.getElementById("archiveCoordinatorsTable");
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
