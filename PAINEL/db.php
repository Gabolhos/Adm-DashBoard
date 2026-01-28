<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'mecanica';

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // Try without database to allow initialization
    $conn = @new mysqli($host, $user, $pass);
}
?>
