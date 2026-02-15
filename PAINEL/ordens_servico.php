<?php
require_once 'db.php';

// Handle Deletion
if (isset($_GET['delete'])) {
    $id_del = intval($_GET['delete']);
    if (!$conn->connect_error) {
        $conn->query("DELETE FROM ordem_servico_pecas WHERE id_ordem_servico = $id_del");
        $conn->query("DELETE FROM ordem_servico_servicos WHERE id_ordem_servico = $id_del");
        $conn->query("DELETE FROM ordem_servico WHERE id_ordem_servico = $id_del");
        header("Location: ordens_servico.php");
        exit;
    }
}

// Fetch Orders
$orders = [];
if (!$conn->connect_error) {
    $sql = "SELECT os.id_ordem_servico, c.nome as cliente, CONCAT(v.marca, ' ', v.modelo) as veiculo, os.relato as servico, os.status, os.valor_total
            FROM ordem_servico os
            JOIN Veiculo v ON os.id_veiculo = v.id_veiculo
            JOIN Cliente c ON v.id_cliente = c.id_cliente
            ORDER BY os.id_ordem_servico DESC";
    $res = $conn->query($sql);
    while ($res && $row = $res->fetch_assoc()) {
        $orders[] = $row;
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
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .btn-create {
            background-color: var(--green);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-create:hover { opacity: 0.9; }

        .orders-table {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .action-btns {
            display: flex;
            gap: 10px;
        }
        .btn-edit { color: var(--blue); font-size: 20px; }
        .btn-delete { color: var(--red); font-size: 20px; cursor: pointer; border: none; background: none; }
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
                <p>Hello, <span id="welcome-name">Usuário</span>. Bem-Vindo à <span>Tratto Mecânica</span></p>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <a href="nova_ordem_servico.php" class="btn-create"><i class='bx bx-plus'></i> Criar O.S.</a>
                <div class="logout-container" onclick="logout()">
                    <i class='bx bx-log-out'></i>
                    <span>Sair</span>
                </div>
            </div>
        </div>

        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>CLIENTE</th>
                        <th>VEÍCULO</th>
                        <th>DESCRIÇÃO DO SERVIÇO</th>
                        <th>STATUS</th>
                        <th>VALOR TOTAL</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">Nenhuma ordem de serviço encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['cliente']); ?></td>
                                <td><?php echo htmlspecialchars($order['veiculo']); ?></td>
                                <td><?php echo htmlspecialchars($order['servico']); ?></td>
                                <td><span class="status-tag status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                <td>R$ <?php echo number_format($order['valor_total'], 2, ',', '.'); ?></td>
                                <td class="action-btns">
                                    <a href="nova_ordem_servico.php?id=<?php echo (int)$order['id_ordem_servico']; ?>" class="btn-edit"><i class='bx bx-edit-alt'></i></a>
                                    <button onclick="confirmDelete(<?php echo (int)$order['id_ordem_servico']; ?>)" class="btn-delete"><i class='bx bx-trash'></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script type="module">
        import { auth } from "../firebase-config.js";
        import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

        window.logout = () => {
            signOut(auth).then(() => {
                window.location.href = "../index.php";
            });
        };

        onAuthStateChanged(auth, (user) => {
            if (user) {
                fetch(`get_user_info.php?uid=${user.uid}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Role-based redirection
                            if (data.is_admin === 0) {
                                window.location.href = 'painel_cliente.php';
                                return;
                            }

                            if (data.nome) {
                                document.getElementById('user-name-display').innerText = data.nome;
                                const welcomeName = document.getElementById('welcome-name');
                                if (welcomeName) welcomeName.innerText = data.nome;
                            }
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

        window.confirmDelete = (id) => {
            if (confirm("Tem certeza que deseja excluir esta ordem de serviço?")) {
                window.location.href = `ordens_servico.php?delete=${id}`;
            }
        };
    </script>
</body>
</html>
