<?php
// Habilitar erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'PAINEL/db.php';

header('Content-Type: application/json');

// Captura JSON
$input = json_decode(file_get_contents('php://input'), true);

$uid = $input['uid'] ?? $_POST['uid'] ?? null;
$email = $input['email'] ?? $_POST['email'] ?? null;
$nome = $input['nome'] ?? $_POST['nome'] ?? null;

if ($uid && $email) {
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro de conexão com o banco de dados.']);
        exit;
    }

    try {
        // Define admin por padrão para um email específico
        $is_admin = (in_array($email, ['admin@mecanica.com', 'jadir@mecanica.com'])) ? 1 : 0;

        // Usamos placeholders únicos para o UPDATE pois algumas versões do PDO não permitem reutilizar nomes sem ATTR_EMULATE_PREPARES
        $sql = "INSERT INTO usuarios (firebase_uid, email, nome, is_admin) VALUES (:uid, :email, :nome, :is_admin)
                ON DUPLICATE KEY UPDATE email = :email_up, nome = IFNULL(:nome_up, nome)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $uid,
            ':email' => $email,
            ':nome' => $nome,
            ':is_admin' => $is_admin,
            ':email_up' => $email,
            ':nome_up' => $nome
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Usuário processado com sucesso!']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro no Banco: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados incompletos (UID ou Email ausentes).']);
}
?>
