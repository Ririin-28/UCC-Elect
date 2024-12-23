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

        <!-- Main Content -->
        <div class="main-content container-fluid g-0">
            <!-- Title -->
            <div class="title-container d-flex justify-content-between align-items-center">
                <h1>Students List</h1>
                <div class="search-container">
                    <input type="text" id="searchInput" class="form-control search-input" placeholder="Search...">
                    <i class="bi bi-search search-icon"></i>
                </div>
            </div>

            <!-- Students Table -->
            <div class="container mt-3">
                <table class="table table-bordered text-center" id="studentTable">
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
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_student_id" name="student_id">
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
                        <label for="edit_gender" class="form-label">Gender</label>
                        <select class="form-control" id="edit_gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_course" class="form-label">Course</label>
                        <input type="text" class="form-control" id="edit_course" name="course_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_year_section" class="form-label">Year-Section</label>
                        <input type="text" class="form-control" id="edit_year_section" name="year_section" required>
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
                <h5 class="modal-title" id="deleteModalLabel">Delete Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this student?
                <input type="hidden" id="delete_student_id" name="student_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
       document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const table = document.getElementById("studentTable");
    const editModal = new bootstrap.Modal(document.getElementById("editModal"));
    const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));

    const editForm = document.getElementById("editForm");
    const deleteStudentId = document.getElementById("delete_student_id");
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

    async function fetchStudents() {
        try {
            const response = await fetch("../fetch_student.php");
            const data = await response.json();

            if (Array.isArray(data)) {
                renderTableRows(data);
            } else {
                throw new Error("Unexpected data format");
            }
        } catch (error) {
            table.querySelector("tbody").innerHTML = `<tr><td colspan="6">Error: ${error.message}</td></tr>`;
        }
    }

    function renderTableRows(students) {
        const tbody = table.querySelector("tbody");
        tbody.innerHTML = "";

        if (students.length === 0) {
            tbody.innerHTML = "<tr><td colspan='6'>No students found</td></tr>";
            return;
        }

        students.forEach(student => {
            const row = `
                <tr>
                    <td>${student.student_id}</td>
                    <td>${student.name}</td>
                    <td>${student.gender}</td>
                    <td>${student.course_name}</td>
                    <td>${student.year_section}</td>
                    <td>
                        <button class="btn btn-success btn-sm edit-btn" data-id="${student.student_id}" data-name="${student.name}" data-gender="${student.gender}" data-course="${student.course_name}" data-year-section="${student.year_section}">Edit</button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${student.student_id}">Delete</button>
                    </td>
                </tr>`;
            tbody.innerHTML += row;
        });
    }

    table.addEventListener("click", function (event) {
        const target = event.target;

        if (target.classList.contains("edit-btn")) {
            const studentId = target.dataset.id;
            const name = target.dataset.name.split(" ");
            const [lastName, firstName, middleName] = name;
            const gender = target.dataset.gender;
            const course = target.dataset.course;
            const yearSection = target.dataset.yearSection;

            document.getElementById("edit_student_id").value = studentId;
            document.getElementById("edit_last_name").value = lastName || "";
            document.getElementById("edit_first_name").value = firstName || "";
            document.getElementById("edit_middle_name").value = middleName || "";
            document.getElementById("edit_gender").value = gender;
            document.getElementById("edit_course").value = course;
            document.getElementById("edit_year_section").value = yearSection;

            editModal.show();
        } else if (target.classList.contains("delete-btn")) {
            const studentId = target.dataset.id;
            deleteStudentId.value = studentId;

            deleteModal.show();
        }
    });

    editForm.addEventListener("submit", async function (event) {
        event.preventDefault();

        const formData = new FormData(editForm);
        try {
            const response = await fetch("../edit_student.php", {
                method: "POST",
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alert("Student updated successfully!");
                fetchStudents();
                editModal.hide();
            } else {
                alert("Error updating student: " + result.message);
            }
        } catch (error) {
            console.error("Error:", error);
            alert("An error occurred while updating the student.");
        }
    });
    confirmDeleteBtn.addEventListener("click", async function () {
        const studentId = deleteStudentId.value;

        try {
            const response = await fetch("../delete_student.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ student_id: studentId })
            });
            const result = await response.json();

            if (result.success) {
                alert("Student deleted successfully!");
                fetchStudents(); 
                deleteModal.hide();
            } else {
                alert("Error deleting student: " + result.message);
            }
        } catch (error) {
            console.error("Error:", error);
            alert("An error occurred while deleting the student.");
        }
    });

    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase();
        const rows = table.querySelectorAll("tbody tr");

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });

    fetchStudents(); 
});

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