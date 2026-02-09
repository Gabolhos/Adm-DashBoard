<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'PAINEL/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$uid = $input['uid'] ?? $_POST['uid'] ?? null;
$email = $input['email'] ?? $_POST['email'] ?? null;

if ($uid && $email) {
    try {
        // Define admin por padrão para um email específico ou se for o primeiro
        $is_admin = ($email === 'admin@mecanica.com' || $email === 'jadir@mecanica.com') ? 1 : 0;

        $sql = "INSERT INTO usuarios (firebase_uid, email, is_admin) VALUES (:uid, :email, :is_admin)
                ON DUPLICATE KEY UPDATE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $uid,
            ':email' => $email,
            ':is_admin' => $is_admin
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Usuário processado com sucesso!']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados incompletos.']);
}
?>
