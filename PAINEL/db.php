<?php
mysqli_report(MYSQLI_REPORT_OFF);

define('DB_HOST', 'blackbird.kangaroo.srv.br');
define('DB_USER', 'stilodev_firebase');
define('DB_PASS', 'firebase123');
define('DB_NAME', 'stilodev_mec');

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Fallback to localhost if remote fails
    $conn = @new mysqli('localhost', 'root', '', 'mecanica');
    if ($conn->connect_error) {
         $conn = @new mysqli('127.0.0.1', 'root', '');
    }
}

// Configuração PDO para compatibilidade com o código fornecido pelo usuário
$pdo = null;
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Fallback PDO localhost
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=mecanica;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        // $pdo continua null
    }
}
?>
