<?php
require_once 'db.php';

// Deletion logic
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if (!$conn->connect_error) {
        $conn->query("DELETE FROM ordem_servico_pecas WHERE id_ordem_servico = $delete_id");
        $conn->query("DELETE FROM ordem_servico_servicos WHERE id_ordem_servico = $delete_id");
        $conn->query("DELETE FROM ordem_servico WHERE id_ordem_servico = $delete_id");
        header("Location: ordens_servico.php");
        exit;
    }
}

// Fetch all orders
$orders = [];
if (!$conn->connect_error) {
    $sql = "SELECT
                os.id_ordem_servico,
                os.status,
                os.valor_total,
                v.marca, v.modelo,
                c.nome as cliente,
                (SELECT s.descricao FROM ordem_servico_servicos oss
                 JOIN Servico s ON oss.id_servico = s.id_servico
                 WHERE oss.id_ordem_servico = os.id_ordem_servico LIMIT 1) as servico
            FROM ordem_servico os
            JOIN Veiculo v ON os.id_veiculo = v.id_veiculo
            JOIN Cliente c ON v.id_cliente = c.id_cliente
            ORDER BY os.id_ordem_servico DESC";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $orders[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordens de Serviço - SISTEMA MECANICA</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="painel_adm.css">
    <style>
        .hidden { display: none !important; }
        .orders-header { background: var(--white); padding: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-create { background: var(--green); color: var(--white); padding: 10px 20px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; font-weight: bold; }
        .btn-create i { margin-right: 5px; }
        .order-card { background: var(--white); padding: 20px; border-radius: 10px; margin-bottom: 20px; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .order-card .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-bottom: 10px; }
        .order-card .total { position: absolute; top: 20px; right: 20px; text-align: right; }
        .order-card .total span { font-size: 12px; color: #666; }
        .order-card .total p { font-size: 18px; font-weight: bold; }
        .order-card h3 { font-size: 20px; margin-bottom: 5px; }
        .order-card .client-name { font-size: 12px; color: #888; margin-bottom: 15px; }
        .order-card .description-row { background: #f8f8f8; padding: 10px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; color: #666; font-size: 14px; }
        .actions { display: flex; gap: 10px; }
        .actions a { color: #888; font-size: 18px; text-decoration: none; }
        .actions a:hover { color: var(--text-color); }
        .actions a.delete:hover { color: var(--red); }
    </style>
</head>
<body class="hidden">
    <div class="sidebar">
        <div class="profile-section">
            <img src="../IMG/profile-1.jpg" alt="Admin">
            <div class="profile-info">
                <h4 id="user-email">Carregando...</h4>
                <p>Administrador</p>
            </div>
        </div>
        <ul class="menu">
            <li><a href="painel_adm.php"><i class='bx bxs-grid-alt'></i> Painel</a></li>
            <li><a href="ordens_servico.php" class="active"><i class='bx bx-list-ul'></i> Ordens de serviço</a></li>
            <li><a href="painel_cliente.php"><i class='bx bx-user'></i> Clientes</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="orders-header">
            <div>
                <h2>Ordens de serviço</h2>
                <p>Bem-Vindo Usuário à <span>Tratto Mecânica</span></p>
            </div>
            <a href="nova_ordem_servico.php" class="btn-create"><i class='bx bx-plus'></i> Criar O.S.</a>
        </div>

        <div class="orders-list">
            <?php if (empty($orders)): ?>
                <div class="order-card">
                    <p style="text-align: center;">Nenhuma ordem de serviço encontrada.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <?php
                        $status_class = '';
                        $status_text = $order['status'];
                        if ($order['status'] == 'Em andamento') {
                            $status_class = 'execucao';
                            $status_text = 'EM EXECUÇÃO';
                        } elseif ($order['status'] == 'Aberto') {
                            $status_class = 'aberto';
                        } elseif ($order['status'] == 'Aguardando peças') {
                            $status_class = 'aguardando';
                        }
                        ?>
                        <span class="status-badge status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>

                        <div class="total">
                            <span>Total</span>
                            <p>R$ <?php echo number_format($order['valor_total'], 2, ',', '.'); ?></p>
                        </div>

                        <h3><?php echo $order['marca'] . ' ' . $order['modelo']; ?></h3>
                        <p class="client-name"><?php echo $order['cliente']; ?></p>

                        <div class="description-row">
                            <span><?php echo $order['servico'] ?? 'Descrição serviço'; ?></span>
                            <div class="actions">
                                <a href="nova_ordem_servico.php?id=<?php echo $order['id_ordem_servico']; ?>" title="Editar"><i class='bx bx-edit-alt'></i></a>
                                <a href="?delete_id=<?php echo $order['id_ordem_servico']; ?>" class="delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta ordem?')"><i class='bx bx-trash'></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer_auth.php'; ?>
</body>
</html>
