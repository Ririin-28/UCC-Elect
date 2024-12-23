<?php
session_start();
session_unset(); 
session_destroy(); 
header("Location: ./login/ucc-elect_student_login.php");
exit;
?>
