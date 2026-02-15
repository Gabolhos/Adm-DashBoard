<?php
require_once 'db.php';

header('Content-Type: application/json');

$uid = $_GET['uid'] ?? null;

if ($uid) {
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("SELECT nome, is_admin FROM usuarios WHERE firebase_uid = ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            echo json_encode([
                'status' => 'success',
                'nome' => $user['nome'],
                'is_admin' => (int)$user['is_admin']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro de conexão']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'UID não fornecido']);
}
?>
