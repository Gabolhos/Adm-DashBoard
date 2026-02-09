<?php
require_once 'db.php';

// Fetch Indicators
$faturamento = 0;
$aguardando_pecas = 0;
$servicos_aberto = 0;

if (!$conn->connect_error) {
    $res = $conn->query("SELECT SUM(valor_total) as total FROM ordem_servico WHERE status = 'Concluído'");
    if ($res) {
        $row = $res->fetch_assoc();
        $faturamento = $row['total'] ?? 0;
    }

    $res = $conn->query("SELECT COUNT(*) as total FROM ordem_servico WHERE status = 'Aguardando peças'");
    if ($res) {
        $row = $res->fetch_assoc();
        $aguardando_pecas = $row['total'] ?? 0;
    }

    $res = $conn->query("SELECT COUNT(*) as total FROM ordem_servico WHERE status IN ('Aberto', 'Em andamento')");
    if ($res) {
        $row = $res->fetch_assoc();
        $servicos_aberto = $row['total'] ?? 0;
    }
}

// Fetch History
$history = [];
if (!$conn->connect_error) {
    $sql = "SELECT
                v.placa,
                c.nome as cliente,
                os.status,
                m.nome as mecanico,
                (SELECT s.descricao FROM ordem_servico_servicos oss
                 JOIN Servico s ON oss.id_servico = s.id_servico
                 WHERE oss.id_ordem_servico = os.id_ordem_servico LIMIT 1) as servico
            FROM ordem_servico os
            JOIN Veiculo v ON os.id_veiculo = v.id_veiculo
            JOIN Cliente c ON v.id_cliente = c.id_cliente
            JOIN mecanico m ON os.id_mecanico = m.id_mecanico
            ORDER BY os.id_ordem_servico DESC
            LIMIT 5";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $history[] = $row;
        }
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
    <style>
        .hidden { display: none !important; }
    </style>
</head>
<body class="hidden"> <!-- Esconde o conteúdo até o login ser verificado -->
    <div class="sidebar">
        <div class="profile-section">
            <img src="../IMG/profile-1.jpg" alt="Admin">
            <div class="profile-info">
                <h4 id="user-email">Carregando...</h4>
                <p>Administrador</p>
            </div>
        </div>
        <ul class="menu">
            <li><a href="painel_adm.php" class="active"><i class='bx bxs-grid-alt'></i> Painel</a></li>
            <li><a href="ordens_servico.php"><i class='bx bx-list-ul'></i> Ordens de serviço</a></li>
            <li><a href="painel_cliente.php"><i class='bx bx-user'></i> Clientes</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Painel</h2>
                <p>Bem-Vindo Usuário à <span>Tratto Mecânica</span></p>
            </div>
            <div class="logout-btn" id="btnLogout">
                <i class='bx bx-log-out'></i>
                <span style="font-size: 12px; display: block; text-align: center;">Sair</span>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card revenue">
                <h3>Faturamento (Concluído)</h3>
                <p class="value-green">R$ <?php echo number_format($faturamento, 2, ',', '.'); ?></p>
            </div>
            <div class="card waiting">
                <h3>Aguardando peça</h3>
                <p><?php echo str_pad($aguardando_pecas, 2, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="card open">
                <h3>Serviços em aberto</h3>
                <p><?php echo str_pad($servicos_aberto, 2, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>

        <div class="history-section">
            <div class="history-header">
                <h3>Histórico de serviços</h3>
                <a href="ordens_servico.php">Ver todas</a>
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
                            <td colspan="5" style="text-align: center;">Nenhum serviço encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td><span class="placa-badge"><?php echo $item['placa']; ?></span></td>
                                <td><?php echo $item['cliente']; ?></td>
                                <td><?php echo $item['servico'] ?? 'N/A'; ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = $item['status'];
                                    if ($item['status'] == 'Em andamento') {
                                        $status_class = 'execucao';
                                        $status_text = 'EM EXECUÇÃO';
                                    } elseif ($item['status'] == 'Aberto') {
                                        $status_class = 'aberto';
                                    } elseif ($item['status'] == 'Aguardando peças') {
                                        $status_class = 'aguardando';
                                    }
                                    ?>
                                    <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td><?php echo $item['mecanico']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Firebase Auth Check -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
        import { getAuth, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

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
                document.getElementById('user-email').innerText = user.email;
                document.body.classList.remove('hidden');
            } else {
                window.location.href = "../index.php";
            }
        });

        document.getElementById('btnLogout').addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = "../index.php";
            });
        });
    </script>
</body>
</html>
