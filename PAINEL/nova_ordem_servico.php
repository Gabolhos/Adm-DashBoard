<?php
require_once 'db.php';

$edit_mode = false;
$os_data = [];
$os_pecas = [];
$os_servicos = [];

// Fetch Vehicles
$vehicles = [];
if (!$conn->connect_error) {
    $res = $conn->query("SELECT v.id_veiculo, v.marca, v.modelo, v.placa, c.nome as cliente FROM Veiculo v JOIN Cliente c ON v.id_cliente = c.id_cliente");
    while ($res && $row = $res->fetch_assoc()) $vehicles[] = $row;
}

// Fetch Services
$services = [];
if (!$conn->connect_error) {
    $res = $conn->query("SELECT id_servico, descricao, valor FROM Servico");
    while ($res && $row = $res->fetch_assoc()) $services[] = $row;
}

// Fetch Mechanics
$mecanicos = [];
if (!$conn->connect_error) {
    $res = $conn->query("SELECT id_mecanico, nome FROM mecanico");
    while ($res && $row = $res->fetch_assoc()) $mecanicos[] = $row;
}

// Load existing OS if editing
if (isset($_GET['id'])) {
    $id_os = intval($_GET['id']);
    if (!$conn->connect_error) {
        $res = $conn->query("SELECT * FROM ordem_servico WHERE id_ordem_servico = $id_os");
        if ($res && $row = $res->fetch_assoc()) {
            $os_data = $row;
            $edit_mode = true;

            // Load pieces
            $res_p = $conn->query("SELECT p.nome, p.preco, osp.quantidade FROM ordem_servico_pecas osp JOIN Peca p ON osp.id_peca = p.id_peca WHERE osp.id_ordem_servico = $id_os");
            while ($res_p && $row_p = $res_p->fetch_assoc()) $os_pecas[] = $row_p;

            // Load services
            $res_s = $conn->query("SELECT id_servico FROM ordem_servico_servicos WHERE id_ordem_servico = $id_os");
            while ($res_s && $row_s = $res_s->fetch_assoc()) $os_servicos[] = $row_s['id_servico'];
        }
    }
}

// Save logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_veiculo = $_POST['id_veiculo'];
    $status = $_POST['status'];
    $relato = $_POST['relato'] ?? '';
    $mao_de_obra = floatval($_POST['mao_de_obra']);
    $id_mecanico = $mecanicos[0]['id_mecanico'] ?? 1;

    $pecas = $_POST['peca_nome'] ?? [];
    $pecas_qtd = $_POST['peca_qtd'] ?? [];
    $pecas_valor = $_POST['peca_valor'] ?? [];
    $servicos_sel = $_POST['servicos'] ?? [];

    $valor_total = $mao_de_obra;
    for ($i = 0; $i < count($pecas); $i++) {
        $valor_total += floatval($pecas_qtd[$i]) * floatval($pecas_valor[$i]);
    }

    foreach ($servicos_sel as $id_s) {
        foreach ($services as $serv) {
            if ($serv['id_servico'] == $id_s) {
                $valor_total += floatval($serv['valor']);
                break;
            }
        }
    }

    if (!$conn->connect_error) {
        if ($edit_mode) {
            $stmt = $conn->prepare("UPDATE ordem_servico SET status = ?, valor_total = ?, id_veiculo = ?, id_mecanico = ?, relato = ? WHERE id_ordem_servico = ?");
            $stmt->bind_param("sdiiii", $status, $valor_total, $id_veiculo, $id_mecanico, $relato, $id_os);
            $stmt->execute();
            $id_ordem = $id_os;

            // Clean old relations
            $conn->query("DELETE FROM ordem_servico_pecas WHERE id_ordem_servico = $id_ordem");
            $conn->query("DELETE FROM ordem_servico_servicos WHERE id_ordem_servico = $id_ordem");
        } else {
            $data_emissao = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO ordem_servico (data_emissao, status, valor_total, id_veiculo, id_mecanico, relato) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiiis", $data_emissao, $status, $valor_total, $id_veiculo, $id_mecanico, $relato);
            $stmt->execute();
            $id_ordem = $stmt->insert_id;
        }

        // Insert parts
        for ($i = 0; $i < count($pecas); $i++) {
            $nome_peca = $pecas[$i];
            $qtd = intval($pecas_qtd[$i]);
            $valor_un = floatval($pecas_valor[$i]);

            $stmt_p = $conn->prepare("INSERT INTO Peca (nome, preco) VALUES (?, ?)");
            $stmt_p->bind_param("sd", $nome_peca, $valor_un);
            $stmt_p->execute();
            $id_peca = $stmt_p->insert_id;

            $stmt_osp = $conn->prepare("INSERT INTO ordem_servico_pecas (id_ordem_servico, id_peca, quantidade) VALUES (?, ?, ?)");
            $stmt_osp->bind_param("iii", $id_ordem, $id_peca, $qtd);
            $stmt_osp->execute();
        }

        // Insert services
        foreach ($servicos_sel as $id_s) {
            $stmt_oss = $conn->prepare("INSERT INTO ordem_servico_servicos (id_ordem_servico, id_servico) VALUES (?, ?)");
            $stmt_oss->bind_param("ii", $id_ordem, $id_s);
            $stmt_oss->execute();
        }

        header("Location: ordens_servico.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Editar' : 'Nova'; ?> Ordem de Serviço - SISTEMA MECANICA</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="painel_adm.css">
    <style>
        .form-container {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .close-btn {
            position: absolute;
            top: 25px;
            right: 25px;
            font-size: 24px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 700;
        }
        .close-btn:hover { color: var(--red); }

        .form-section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 10px; color: var(--dark-text); }
        .form-group label span { color: var(--red); }

        .form-group select, .form-group textarea, .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: white;
            font-size: 14px;
            color: var(--dark-text);
            outline: none;
        }
        .form-group select:focus, .form-group textarea:focus, .form-group input:focus {
            border-color: var(--green);
        }

        .right-section {
            background-color: #e2e8f0;
            padding: 30px;
            border-radius: 12px;
        }

        .right-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .right-header h3 { font-size: 18px; font-weight: 700; color: var(--dark-text); }

        .btn-add-peca {
            background-color: #cbd5e1;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            color: var(--dark-text);
            transition: 0.2s;
        }
        .btn-add-peca i { margin-right: 8px; font-size: 16px; }
        .btn-add-peca:hover { background-color: #94a3b8; }

        .pecas-list {
            background-color: transparent;
            border: 2px dashed #94a3b8;
            border-radius: 12px;
            padding: 25px;
            min-height: 250px;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
        }
        .no-peca {
            text-align: center;
            color: #64748b;
            font-weight: 600;
            margin: auto;
        }

        .peca-item {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 40px;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dotted #94a3b8;
        }
        .peca-item input { padding: 8px; border: none; background: transparent; border-bottom: 1px solid #cbd5e1; border-radius: 0; }
        .remove-peca { color: var(--red); cursor: pointer; font-weight: 700; text-align: center; font-size: 18px; }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .mao-obra-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .mao-obra-container label { font-size: 14px; font-weight: 700; color: var(--dark-text); }
        .mao-obra-container input { width: 120px; text-align: center; }

        .btn-group { display: flex; flex-direction: column; gap: 12px; }
        .btn-save {
            background-color: var(--green);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            transition: 0.2s;
        }
        .btn-save:hover { opacity: 0.9; }
        .btn-cancel {
            background-color: #cbd5e1;
            color: var(--dark-text);
            text-decoration: none;
            padding: 14px 40px;
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            transition: 0.2s;
        }
        .btn-cancel:hover { background-color: #94a3b8; }

        .total-box {
            margin-top: 30px;
            background-color: #cbd5e1;
            padding: 20px 25px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-box span { font-size: 16px; font-weight: 700; color: var(--dark-text); }
        .total-box .total-value { color: var(--green); font-size: 24px; font-weight: 800; }

        .services-selection { margin-bottom: 25px; }
        .services-selection label { font-weight: 700; margin-bottom: 12px; display: block; color: var(--dark-text); font-size: 14px; }
        .service-checkbox { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: var(--dark-text); }
        .service-checkbox input { width: 18px; height: 18px; cursor: pointer; }
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
        <div class="form-container">
            <a href="ordens_servico.php" class="close-btn">X</a>

            <div class="left-section">
                <form id="osForm" method="POST">
                <h2 class="form-section-title">DADOS DO VEÍCULO</h2>

                <div class="form-group">
                    <label>Selecione o Veículo <span>*</span></label>
                    <select name="id_veiculo" required>
                        <option value="">Buscar Veículo...</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?php echo $v['id_veiculo']; ?>" <?php echo (isset($os_data['id_veiculo']) && $os_data['id_veiculo'] == $v['id_veiculo']) ? 'selected' : ''; ?>>
                                <?php echo $v['marca'] . ' ' . $v['modelo'] . ' (' . $v['placa'] . ') - ' . $v['cliente']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status da OS</label>
                    <select name="status">
                        <?php
                        $statuses = ['Aberto', 'Em andamento', 'Aguardando peças', 'Concluído', 'Cancelado'];
                        foreach ($statuses as $s):
                        ?>
                            <option value="<?php echo $s; ?>" <?php echo (isset($os_data['status']) && $os_data['status'] == $s) ? 'selected' : ''; ?>>
                                <?php echo $s; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Relato do Cliente (Defeito)</label>
                    <textarea name="relato" rows="6" placeholder="Ex: Barulho ao frear..."><?php echo $os_data['relato'] ?? ''; ?></textarea>
                </div>

                <div class="services-selection">
                    <label>Serviços</label>
                    <?php foreach ($services as $s): ?>
                        <div class="service-checkbox">
                            <input type="checkbox" name="servicos[]" value="<?php echo $s['id_servico']; ?>" data-valor="<?php echo $s['valor']; ?>" <?php echo in_array($s['id_servico'], $os_servicos) ? 'checked' : ''; ?> onchange="calculateTotal()">
                            <span><?php echo $s['descricao']; ?> (R$ <?php echo number_format($s['valor'], 2, ',', '.'); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="right-section">
                <div class="right-header">
                    <h3>PEÇAS & SERVIÇOS</h3>
                    <button type="button" class="btn-add-peca" onclick="addPeca()"><i class='bx bx-plus'></i> Add Peça</button>
                </div>

                <div class="pecas-list" id="pecasContainer">
                    <div class="no-peca" id="noPecaMsg" style="<?php echo !empty($os_pecas) ? 'display:none' : ''; ?>">Nenhuma peça adicionada</div>
                    <?php foreach ($os_pecas as $p): ?>
                        <div class="peca-item">
                            <input type="text" name="peca_nome[]" value="<?php echo $p['nome']; ?>" required>
                            <input type="number" name="peca_qtd[]" value="<?php echo $p['quantidade']; ?>" min="1" oninput="calculateTotal()" required>
                            <input type="number" name="peca_valor[]" value="<?php echo $p['preco']; ?>" step="0.01" oninput="calculateTotal()" required>
                            <span class="remove-peca" onclick="removePeca(this)">x</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-footer">
                    <div class="mao-obra-container">
                        <label>Mão de obra (R$)</label>
                        <?php
                        $mao_obra_val = 0;
                        if ($edit_mode) {
                            $total_pecas = 0;
                            foreach ($os_pecas as $p) $total_pecas += $p['preco'] * $p['quantidade'];
                            $total_servicos = 0;
                            foreach ($services as $s) {
                                if (in_array($s['id_servico'], $os_servicos)) $total_servicos += $s['valor'];
                            }
                            $mao_obra_val = $os_data['valor_total'] - $total_pecas - $total_servicos;
                        }
                        ?>
                        <input type="number" name="mao_de_obra" id="maoDeObra" value="<?php echo $mao_obra_val; ?>" step="0.01" oninput="calculateTotal()">
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn-save">Salvar Ordem</button>
                        <a href="ordens_servico.php" class="btn-cancel">Cancelar</a>
                    </div>
                </div>

                <div class="total-box">
                    <span>Total estimado</span>
                    <span class="total-value" id="totalValue">R$ <?php echo number_format($os_data['valor_total'] ?? 0, 2, ',', '.'); ?></span>
                </div>
                </form>
            </div>
        </div>
    </div>

    <script type="module">
        import { auth } from "../firebase-config.js";
        import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

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

    <script>
        function addPeca() {
            const noPecaMsg = document.getElementById('noPecaMsg');
            if (noPecaMsg) noPecaMsg.style.display = 'none';

            const div = document.createElement('div');
            div.className = 'peca-item';
            div.innerHTML = `
                <input type="text" name="peca_nome[]" placeholder="Nome da peça" required>
                <input type="number" name="peca_qtd[]" value="1" min="1" oninput="calculateTotal()" required>
                <input type="number" name="peca_valor[]" value="0" step="0.01" oninput="calculateTotal()" required>
                <span class="remove-peca" onclick="removePeca(this)">x</span>
            `;
            document.getElementById('pecasContainer').appendChild(div);
            calculateTotal();
        }

        function removePeca(el) {
            el.parentElement.remove();
            if (document.querySelectorAll('.peca-item').length === 0) {
                 document.getElementById('noPecaMsg').style.display = 'block';
            }
            calculateTotal();
        }

        function calculateTotal() {
            let total = parseFloat(document.getElementById('maoDeObra').value) || 0;
            document.getElementsByName('peca_qtd[]').forEach((q, i) => {
                total += (parseFloat(q.value) || 0) * (parseFloat(document.getElementsByName('peca_valor[]')[i].value) || 0);
            });
            document.querySelectorAll('input[name="servicos[]"]:checked').forEach(s => {
                total += parseFloat(s.getAttribute('data-valor')) || 0;
            });
            document.getElementById('totalValue').innerText = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        }
    </script>
</body>
</html>
