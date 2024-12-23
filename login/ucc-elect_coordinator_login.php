<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ucc-elect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coordID = $_POST['coordID'];
    $inputPassword = $_POST['inputPassword'];

    $stmt = $conn->prepare("CALL coord_validate_login(?, ?)");
    $stmt->bind_param("ss", $coordID, $inputPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      session_start();
      $_SESSION['coord_id'] = $coordID;
        echo json_encode(['status' => 'success', 'redirect' => '../coordinator/coordinator_import_candidate.php']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Coordinator ID or Password.']);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="../images/UCC-Elect_Logo.png" type="image/x-icon">
  <title>UCC-Elect: Student Council Voting System</title>
  <link rel="stylesheet" href="../css/ucc-elect_users.css">
  <style>
    .login-btn {
      background-color: black;
      color: white;
      border: 2px solid black;
    }

    .login-btn:hover {
      background-color: white;
      color: black;
      border: 2px solid black;
    }

  </style>
</head>
<body>
  <div class="uppercontainer">
    <img src="../images/UccBack.png" alt="Background" class="Background">
    <div class="upperbar"></div>

    <div class="titlebarcontainer">
      <img src="../images/UccLogo.png" alt="UCC Logo" class="UccLogo">
      <div>
        <h6 class="logoname">University of Caloocan City</h6>
        <h6 class="projectname">Ucc-Elect: Student Council Voting System</h6>
      </div>
    </div>
    <main class="d-flex align-items-center min-vh-100">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="card login-card">
              <div class="row g-0">
                <div class="col-md-5">
                  <img src="../images/UCC_banner.jpg" alt="login" class="login-card-img">
                </div>
                <div class="col-md-7">
                  <div class="card-body">
                    <p class="login-card-description">You are signing in as Coordinator</p>
                    <form id="loginForm">
                      <div class="mb-3">
                        <label for="coordID" class="form-label">Coordinator ID</label>
                        <input type="text" name="coordID" id="coordID" class="form-control" placeholder="Enter Coordinator ID (e.g. 40440123-F)" required>
                      </div>
                      <div class="mb-4 position-relative">
                        <label for="inputPassword" class="form-label">Password</label>
                        <div class="input-group">
                          <input type="password" name="inputPassword" id="inputPassword" class="form-control" placeholder="***********" required>
                          <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility()">
                            <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                          </button>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-block login-btn w-100">Login</button>
                    </form>
                    <div id="loginResponse" class="mt-3"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('inputPassword');
      const icon = document.getElementById('togglePasswordIcon');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
      }
    }

    $(document).ready(function() {
      $('#loginForm').submit(function(event) {
        event.preventDefault();
        var coordID = $('#coordID').val();
        var inputPassword = $('#inputPassword').val();

        $.ajax({
          url: '',
          method: 'POST',
          data: { coordID: coordID, inputPassword: inputPassword },
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              window.location.href = response.redirect;
            } else {
              $('#loginResponse').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
          },
          error: function() {
            $('#loginResponse').html('<div class="alert alert-danger">Error occurred. Please try again later.</div>');
          }
        });
      });
    });


  </script>
</body>
</html>
