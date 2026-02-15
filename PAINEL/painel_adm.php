<?php
require_once 'db.php';

// Indicators
$faturamento = 0;
$aguardando = 0;
$aberto = 0;

if (!$conn->connect_error) {
    $res = $conn->query("SELECT SUM(valor_total) as total FROM ordem_servico WHERE status = 'Concluído'");
    if ($res && $row = $res->fetch_assoc()) $faturamento = $row['total'] ?? 0;

    $res = $conn->query("SELECT COUNT(*) as total FROM ordem_servico WHERE status = 'Aguardando peças'");
    if ($res && $row = $res->fetch_assoc()) $aguardando = $row['total'] ?? 0;

    $res = $conn->query("SELECT COUNT(*) as total FROM ordem_servico WHERE status IN ('Aberto', 'Em andamento')");
    if ($res && $row = $res->fetch_assoc()) $aberto = $row['total'] ?? 0;
}

// History
$history = [];
if (!$conn->connect_error) {
    $sql = "SELECT os.id_ordem_servico, v.placa, c.nome as cliente, os.relato as servico, os.status, m.nome as mecanico
            FROM ordem_servico os
            JOIN Veiculo v ON os.id_veiculo = v.id_veiculo
            JOIN Cliente c ON v.id_cliente = c.id_cliente
            JOIN mecanico m ON os.id_mecanico = m.id_mecanico
            ORDER BY os.id_ordem_servico DESC LIMIT 5";
    $res = $conn->query($sql);
    while ($res && $row = $res->fetch_assoc()) {
        $history[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - SISTEMA MECANICA</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="painel_adm.css">
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
            <li><a href="painel_adm.php" class="active"><i class='bx bxs-grid-alt'></i> Painel</a></li>
            <li><a href="ordens_servico.php"><i class='bx bx-list-ul'></i> Ordens de serviço</a></li>
            <li><a href="painel_cliente.php"><i class='bx bx-user'></i> Clientes</a></li>
        </ul>
        <div class="sidebar-footer">
            Desenvolvido por StiloDev
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Painel</h2>
                <p>Hello, <span id="welcome-name">Usuário</span>. Bem-Vindo à <span>Tratto Mecânica</span></p>
            </div>
            <div class="header-right">
                <div class="logout-container" onclick="logout()">
                    <i class='bx bx-log-out'></i>
                    <span>Sair</span>
                </div>
            </div>
        </div>

        <div class="indicators">
            <div class="card card-green">
                <div class="card-content">
                    <h3>Faturamento (Concluído)</h3>
                    <div class="value">R$ <?php echo number_format($faturamento, 2, ',', '.'); ?></div>
                </div>
            </div>
            <div class="card card-yellow">
                <div class="card-content">
                    <h3>Aguardando peça</h3>
                    <div class="value"><?php echo str_pad($aguardando, 2, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>
            <div class="card card-red">
                <div class="card-content">
                    <h3>Serviços em aberto</h3>
                    <div class="value"><?php echo str_pad($aberto, 2, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>
        </div>

        <div class="history-section">
            <div class="section-header">
                <h3>Histórico de serviços</h3>
                <a href="ordens_servico.php" class="view-all">Ver todas</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>PLACA</th>
                        <th>CLIENTE</th>
                        <th>SERVIÇO</th>
                        <th>STATUS</th>
                        <th>MECÂNICO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">Nenhum serviço encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['placa']); ?></td>
                                <td><?php echo htmlspecialchars($item['cliente']); ?></td>
                                <td><?php echo htmlspecialchars($item['servico']); ?></td>
                                <td><span class="status-tag status-<?php echo strtolower(str_replace(' ', '-', $item['status'])); ?>"><?php echo htmlspecialchars($item['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['mecanico']); ?></td>
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
    </script>
</body>
</html>
