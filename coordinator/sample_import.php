<?php
session_start();
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
<body>
    <div class="container"> 
        <div class="row">
            <div class="col-md-12 mt-4">
                <?php
                if(isset($_SESSION['message']))
                {
                    echo "<h5>".$_SESSION['message']."</h5>";
                    unset($_SESSION['message']);

                }
                ?>
                <div class="card">
                    <div class="card-header">
                        <h4>How to Import Excel Data into Database in PHP
                        </h4>
                    </div>
                    <div class="card-body">

                        <form action="code.php" method="POST" enctype="multipart/form-data">

                            <input type="file" name="import_file" class="form-control">
                            <button type="submit" name="save_excel_data" class="btn btn-primary mt-3">Import</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>