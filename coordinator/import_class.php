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

            $student_id = $row[0];
            $last_name = $row[1];
            $first_name = $row[2];
            $middle_name = $row[3];
            $gender = $row[4];
            $course_name = $row[5];
            $year_section = $row[6];

            if (empty($student_id) || empty($last_name) || empty($first_name)) {
                continue; 
            }

            $student_password = $student_id;

            $stmt = $con->prepare("CALL import_class_list(?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param(
                    'ssssssss',
                    $student_id,
                    $last_name,
                    $first_name,
                    $middle_name,
                    $gender,
                    $course_name,
                    $year_section,
                    $student_password
                );

                $stmt->execute();
                $stmt->close();
            }
            $count++;
        }

        $_SESSION['message'] = "Import Complete.";
        header('Location: coordinator_import_class.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Invalid file type. Please upload an Excel file.";
        header('Location: coordinator_import_class.php');
        exit(0);
    }
}
?>
