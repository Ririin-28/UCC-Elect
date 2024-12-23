<?php
session_start();
$con = mysqli_connect('localhost', 'root', '', 'ucc-elect');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_POST['save_excel_data'])) {
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $allowed_ext = ['xls', 'csv', 'xlsx'];

    if (in_array($file_ext, $allowed_ext)) {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $count = 0;

        foreach ($data as $row) {
            if ($count == 0) { 
                $count++;
                continue;
            }

            $election_name = $row[0];
            $last_name = $row[1];
            $first_name = $row[2];
            $middle_name  = $row[3];
            $position_name = $row[4];
            $course_name = $row[5];
            $year_section = $row[6];

            if (empty($election_name) || empty($last_name) || empty($first_name) || empty($position_name) || empty($course_name)) {
                continue; 
            }


            $stmt = $con->prepare("CALL import_candidate_list(?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param(
                    'sssssss',
                    $election_name,
                    $last_name,
                    $first_name,
                    $middle_name,
                    $position_name,
                    $course_name,
                    $year_section
                );

                $stmt->execute();
                $stmt->close();
            }
            $count++;
        }

        $_SESSION['message'] = "Import Complete.";
        header('Location: coordinator_import_candidate.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Invalid file type. Please upload an Excel file.";
        header('Location: coordinator_import_candidate.php');
        exit(0);
    }
}
?>
