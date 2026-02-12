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
        .btn-create {
            background-color: var(--green);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-create i { margin-right: 8px; font-size: 18px; }

        .order-card {
            background-color: var(--white);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .order-card .status-badge {
            display: inline-block;
            margin-bottom: 15px;
        }
        .order-card .total-info {
            position: absolute;
            top: 25px;
            right: 25px;
            text-align: right;
        }
        .order-card .total-info span { font-size: 12px; color: var(--grey-text); font-weight: 600; }
        .order-card .total-info p { font-size: 20px; font-weight: 700; color: var(--dark-text); }

        .order-card h3 { font-size: 20px; font-weight: 700; color: var(--dark-text); margin-bottom: 5px; }
        .order-card .client-name { font-size: 12px; color: var(--grey-text); font-weight: 500; margin-bottom: 20px; }

        .order-card .description-row {
            background-color: #f1f5f9;
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
        }
        .actions { display: flex; gap: 12px; }
        .actions a {
            background-color: #e2e8f0;
            color: #475569;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: 0.2s;
        }
        .actions a:hover { background-color: #cbd5e1; }
        .actions a.delete { color: #ef4444; }
        .actions a.delete:hover { background-color: #fee2e2; }
    </style>
</head>
<body class="hidden">
    <div class="sidebar">
        <div class="profile-section">
            <div class="profile-img-container">
                <img src="../IMG/profile-1.jpg" alt="Admin">
            </div>
            <div class="profile-info">
                <h4 id="user-name-display">Carregando...</h4>
                <p>Administrador</p>
            </div>
        </div>
        <ul class="menu">
            <li><a href="painel_adm.php"><i class='bx bxs-grid-alt'></i> Painel</a></li>
            <li><a href="ordens_servico.php" class="active"><i class='bx bx-list-ul'></i> Ordens de serviço</a></li>
            <li><a href="painel_cliente.php"><i class='bx bx-user'></i> Clientes</a></li>
        </ul>
        <div class="sidebar-footer">
            Desenvolvido por StiloDev
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Ordens de serviço</h2>
                <p>Bem-Vindo Usuário à <span>Tratto Mecânica</span></p>
            </div>
            <a href="nova_ordem_servico.php" class="btn-create"><i class='bx bx-plus'></i> Criar O.S.</a>
        </div>

        <div class="orders-list">
            <?php if (empty($orders)): ?>
                <div class="order-card">
                    <p style="text-align: center; color: var(--grey-text);">Nenhuma ordem de serviço encontrada.</p>
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
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>

                        <div class="total-info">
                            <span>Total</span>
                            <p>R$ <?php echo number_format($order['valor_total'], 2, ',', '.'); ?></p>
                        </div>

                        <h3><?php echo $order['marca'] . ' ' . $order['modelo']; ?></h3>
                        <p class="client-name"><?php echo $order['cliente']; ?></p>

                        <div class="description-row">
                            <span><?php echo $order['servico'] ?? 'Descrição serviço'; ?></span>
                            <div class="actions">
                                <a href="nova_ordem_servico.php?id=<?php echo $order['id_ordem_servico']; ?>">Editar</a>
                                <a href="?delete_id=<?php echo $order['id_ordem_servico']; ?>" class="delete" onclick="return confirm('Deseja excluir esta ordem?')"><i class='bx bx-trash'></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Firebase Auth Check -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
        import { getAuth, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "AIzaSyC642PXZVjV96ORWO3qMcuEYe0lIMdIE9Q",
            authDomain: "mec-projeto-65861.firebaseapp.com",
            projectId: "mec-projeto-65861",
            storageBucket: "mec-projeto-65861.firebasestorage.app",
            messagingSenderId: "625687541988",
            appId: "1:625687541988:web:fc82f6cb314ecc380c14f1"
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        onAuthStateChanged(auth, (user) => {
            if (user) {
                fetch(`get_user_info.php?uid=${user.uid}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.nome) {
                            document.getElementById('user-name-display').innerText = data.nome;
                        } else {
                            document.getElementById('user-name-display').innerText = user.email;
                        }
                    })
                    .catch(() => {
                        document.getElementById('user-name-display').innerText = user.email;
                    });
                document.body.classList.remove('hidden');
            } else {
                window.location.href = "../index.php";
            }
        });
    </script>
</body>
</html>
