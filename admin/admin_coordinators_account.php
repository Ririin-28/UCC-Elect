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
    /* --------------- Table --------------- */
    .table-bordered {
        text-align: center;
    }

    .facilitator-id-column {
        width: 160px;
    }

    .name-column {
        width: 350px;
    }

    .email-column {
        width: 210px;
    }

    .contact-column {
        width: 160px;
    }

    .action-column {
        width: 200px;
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
    <!-- Title Container -->
    <div class="title-container">
        <h1>Accounts: Coordinator</h1>
        <div class="col">
            <div class="search-container">
                <input type="text" class="form-control search-input" placeholder="Search..." id="searchInput">
                <i class="bi bi-search search-icon"></i>
            </div>
        </div>
    </div>

    <!-- Coordinators List -->
    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Coordinators List</h2>
        </div>

        <table class="table table-bordered text-center" id="coordinatorTable">
            <thead>
                <tr>
                    <th class="coord-id-column">Coordinator ID</th>
                    <th class="name-column">Name</th>
                    <th class="email-column">Email</th>
                    <th class="contact-column">Contact #</th>
                    <th class="action-column">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Coordinator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_coord_id" name="coord_id">
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="edit_middle_name" name="middle_name">
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="edit_contact_number" name="contact_number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Coordinator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this coordinator?
                <input type="hidden" id="delete_coord_id" name="coord_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    function fetchCoordinators() {
        $.ajax({
            url: '../fetch_coordinator.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                let rows = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(function (coordinator) {
                        rows += `<tr>
                                    <td>${coordinator.coord_id}</td>
                                    <td>${coordinator.name}</td>
                                    <td>${coordinator.email}</td>
                                    <td>${coordinator.contact_number}</td>
                                    <td>
                                        <button class='btn btn-success btn-sm edit-btn' data-id='${coordinator.coord_id}'>Edit</button>
                                        <button class='btn btn-danger btn-sm delete-btn' data-id='${coordinator.coord_id}'>Delete</button>
                                    </td>
                                </tr>`;
                    });
                } else {
                    rows = "<tr><td colspan='5'>No Coordinators found</td></tr>";
                }
                $('#coordinatorTable tbody').html(rows);
            },
            error: function (xhr, status, error) {
                $('#coordinatorTable tbody').html(
                    `<tr><td colspan='5'>Error fetching coordinators: ${xhr.responseText || error}</td></tr>`
                );
            },
        });
    }

    fetchCoordinators();

   
    $(document).on("click", ".edit-btn", function () {
        const coord_id = $(this).data("id");
        $.ajax({
            url: '../get_coordinator.php',
            type: 'GET',
            data: { coord_id: coord_id },
            dataType: 'json',
            success: function (data) {
                if (data.error) {
                    alert(data.error);
                } else {
                    $("#edit_coord_id").val(data.coord_id);
                    $("#edit_last_name").val(data.last_name);
                    $("#edit_first_name").val(data.first_name);
                    $("#edit_middle_name").val(data.middle_name);
                    $("#edit_email").val(data.email);
                    $("#edit_contact_number").val(data.contact_number);
                    $("#editModal").modal("show");
                }
            },
            error: function () {
                alert("Failed to fetch coordinator data.");
            },
        });
    });

    $("#editForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
            url: "../edit_coordinator.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.error) {
                    alert(response.error);
                } else {
                    alert(response.success || "Coordinator updated successfully.");
                    $("#editModal").modal("hide");
                    fetchCoordinators();
                }
            },
            error: function () {
                alert("Failed to update coordinator details.");
            },
        });
    });

    $(document).on("click", ".delete-btn", function () {
        const coord_id = $(this).data("id");
        $("#delete_coord_id").val(coord_id);
        $("#deleteModal").modal("show");
    });

    $("#confirmDeleteBtn").on("click", function () {
        const coord_id = $("#delete_coord_id").val();
        $.ajax({
            url: "../delete_coordinator.php",
            type: "POST",
            data: { coord_id },
            dataType: "json",
            success: function (response) {
                if (response.error) {
                    alert(response.error);
                } else {
                    alert(response.success || "Coordinator deleted successfully.");
                    $("#deleteModal").modal("hide");
                    fetchCoordinators();
                }
            },
            error: function () {
                alert("Failed to delete Coordinator.");
            },
        });
    });
});
</script>

    <script>

        const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
            document.querySelector("#sidebar").classList.toggle("expand");
        });

        document.getElementById("searchInput").addEventListener("keyup", function() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toLowerCase();
            table = document.getElementById("coordinatorTable");
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
