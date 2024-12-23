<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $election_name = $_POST['election_name'];
    $start_datetime = date('Y-m-d H:i:s', strtotime($_POST['election_start_date'] . ' ' . $_POST['election_start_time']));
    $end_datetime = date('Y-m-d H:i:s', strtotime($_POST['election_end_date'] . ' ' . $_POST['election_end_time']));
    $course_name = $_POST['course_name'];

    if (strtotime($start_datetime) >= strtotime($end_datetime)) {
        echo json_encode(['status' => 'error', 'message' => 'Start datetime must be earlier than end datetime']);
        exit;
    }

    try {
        $stmt = $conn->prepare("CALL add_election_event(?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("ssss", $election_name, $start_datetime, $end_datetime, $course_name);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        echo json_encode(['status' => 'success', 'message' => 'Election event added successfully!']);
        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['election_name'])) {
    $election_name = $_GET['election_name'];
    
    try {
        $sql = "SELECT 
            candidate_id,
            election_name,
            course_name,
            position_name,
            name,
            year_section 
            FROM view_election_candidates 
            WHERE election_name = ?";
            
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $election_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $candidates = array();
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($candidates);
        
        $stmt->close();
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
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
            <div class="title-container">
                <h1>Election Analytics: Add Election Event</h1>
            </div>

            <div class="container">
                <div class="card p-4">
                    <h4 class="mb-4">Add Election Event</h4>
                    <div id="response-message" class="alert d-none"></div>
                    <form id="add-election-event-form">

                        <div class="col-md-4 mb-2">
                            <label for="election_name" class="form-label">Election Name</label>
                            <select class="form-select" id="election_name" name="election_name" required>
                                <option value="" disabled selected>Select Election</option>
                                <?php
                                $sql = "SELECT DISTINCT election_name FROM view_election_candidates ORDER BY election_name";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['election_name']) . "'>" . 
                                             htmlspecialchars($row['election_name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        

                        <!-- Date and Time Fields -->
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label for="election_start_date" class="form-label">Election Start Date</label>
                                <input type="date" class="form-control" id="election_start_date" name="election_start_date" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="election_start_time" class="form-label">Election Start Time</label>
                                <input type="time" class="form-control" id="election_start_time" name="election_start_time" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="election_end_date" class="form-label">Election End Date</label>
                                <input type="date" class="form-control" id="election_end_date" name="election_end_date" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="election_end_time" class="form-label">Election End Time</label>
                                <input type="time" class="form-control" id="election_end_time" name="election_end_time" required>
                            </div>
                        </div>

                        <!-- Course Selection -->
                        <div class="mb-3">
                            <label for="course_name" class="form-label">Course</label>
                            <select class="form-select" id="course_name" name="course_name" required>
                                <option value="" disabled selected>Select Course</option>
                                <?php
                                $sql = "SELECT course_id, course_name FROM view_course";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['course_name'] . "'>" . $row['course_name'] . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>No elections available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-success" style="width: 400px; height: 45px">Add Election Event</button>
                        </div>

                       <div class="mt-4">
                           <h5>Candidates List</h5>
                           <div class="table-responsive">
                               <table class="table table-bordered table-hover">
                                   <thead class="table-primary">
                                       <tr>
                                           <th>Candidate ID</th>
                                           <th>Name</th>
                                           <th>Position</th>
                                           <th>Course</th>
                                           <th>Year & Section</th>
                                       </tr>
                                   </thead>
                                   <tbody id="candidatesTableBody">
                                       <tr>
                                           <td colspan="5" class="text-center">Select an election to view candidates</td>
                                       </tr>
                                   </tbody>
                               </table>
                           </div>
                       </div>
                       
                    
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById('add-election-event-form');
        const responseMessage = document.getElementById('response-message');

        form.addEventListener('submit', function (e) {
            e.preventDefault(); 

            const formData = new FormData(form);

            fetch('facilitator_add_election_event.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    responseMessage.classList.remove('d-none', 'alert-success', 'alert-danger');
                    responseMessage.classList.add(data.status === 'success' ? 'alert-success' : 'alert-danger');
                    responseMessage.textContent = data.message;

                    if (data.status === 'success') {
                        form.reset();
                    }
                })
                .catch(error => {
                    responseMessage.classList.remove('d-none', 'alert-success');
                    responseMessage.classList.add('alert-danger');
                    responseMessage.textContent = 'An error occurred. Please try again.';
                    console.error('Error:', error);
                });
        });
    </script>

    <script>
        const hamBurger = document.querySelector(".toggle-btn");
            hamBurger.addEventListener("click", function () {
                document.querySelector("#sidebar").classList.toggle("expand");
            });


            document.addEventListener('DOMContentLoaded', function () {
            const electionNameSelect = document.getElementById('election_name');
            const candidatesTableBody = document.getElementById('candidates-table').getElementsByTagName('tbody')[0];

            electionNameSelect.addEventListener('change', function () {
                const selectedElectionId = electionNameSelect.value;

                if (selectedElectionId) {
                    fetch('facilitator_add_election_event.php?election_id=' + selectedElectionId)
                        .then(response => response.json())
                        .then(data => {
                            candidatesTableBody.innerHTML = '';

                            data.forEach(candidate => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${candidate.candidate_id}</td>
                                    <td>${candidate.name}</td>
                                    <td>${candidate.position}</td>
                                `;
                                candidatesTableBody.appendChild(row);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching candidates:', error);
                        });
                } else {
                    candidatesTableBody.innerHTML = '';
                }
            });
        });

    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const electionSelect = document.getElementById('election_name');
        const candidatesTableBody = document.getElementById('candidatesTableBody');

        function fetchCandidates(electionName) {
            candidatesTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading candidates...</td></tr>';
    
            fetch(`facilitator_add_election_event.php?election_name=${encodeURIComponent(electionName)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (data.length === 0) {
                        candidatesTableBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center">No candidates found for ${electionName}</td>
                            </tr>`;
                        return;
                    }
                    data.sort((a, b) => a.position_name.localeCompare(b.position_name));
                    const rows = data.map(candidate => `
                        <tr>
                            <td>${candidate.candidate_id}</td>
                            <td>${candidate.name}</td>
                            <td>${candidate.position_name}</td>
                            <td>${candidate.course_name}</td>
                            <td>${candidate.year_section}</td>
                        </tr>
                    `).join('');
    
                    candidatesTableBody.innerHTML = rows;
                })
                .catch(error => {
                    console.error('Error:', error);
                    candidatesTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-danger">
                                Error loading candidates: ${error.message}
                            </td>
                        </tr>`;
                });
        }

        electionSelect.addEventListener('change', function() {
            const selectedElection = this.value;
            if (selectedElection) {
                fetchCandidates(selectedElection);
            } else {
                candidatesTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">Select an election to view candidates</td>
                    </tr>`;
            }
        });
    });
    </script>
    

</body>
</html>

