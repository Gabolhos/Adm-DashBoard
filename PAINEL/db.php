<?php
mysqli_report(MYSQLI_REPORT_OFF);

define('DB_HOST', 'blackbird.kangaroo.srv.br');
define('DB_USER', 'stilodev_gabolhos');
define('DB_PASS', 'qHWx&1jZ#]j97Aep');
define('DB_NAME', 'stilodev_mec');

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Fallback to localhost if remote fails
    $conn = @new mysqli('localhost', 'root', '', 'mecanica');
    if ($conn->connect_error) {
         $conn = @new mysqli('127.0.0.1', 'root', '');
    }
}
?>
