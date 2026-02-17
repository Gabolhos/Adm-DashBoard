<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Cliente - SISTEMA MECANICA</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="painel_adm.css">
    <link rel="stylesheet" href="painel_cliente.css">
    <style>
        .hidden { display: none !important; }
    </style>
</head>
<body class="hidden">
    <div class="sidebar">
        <div class="profile-section">
            <div class="profile-img-container">
                <img src="../IMG/profile-1.jpg" alt="Cliente">
            </div>
            <div class="profile-info">
                <h4 id="user-name-display">Carregando...</h4>
                <p>Cliente</p>
            </div>
        </div>
        <ul class="menu">
            <li><a href="painel_cliente.php" class="active"><i class='bx bxs-grid-alt'></i> Painel</a></li>
            <li><a href="#"><i class='bx bx-car'></i> Novo Veículo</a></li>
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
            <div class="header-right" style="display: flex; align-items: center; gap: 30px;">
                <button class="btn-cadastrar-carro">
                    Cadastrar um carro
                </button>
                <div class="logout-container" id="btnLogout" style="cursor: pointer;">
                    <i class='bx bx-log-out'></i>
                    <span>Sair</span>
                </div>
            </div>
        </div>

        <div class="vehicle-list-section">
            <div class="empty-state-card">
                <p>Nenhum veículo cadastrado</p>
            </div>
        </div>
    </div>

    <!-- Firebase Auth Check -->
    <script type="module">
        import { auth } from "../firebase-config.js";
        import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

        onAuthStateChanged(auth, (user) => {
            if (user) {
                fetch(`get_user_info.php?uid=${user.uid}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.nome) {
                            document.getElementById('user-name-display').innerText = data.nome;
                            const welcomeName = document.getElementById('welcome-name');
                            if (welcomeName) welcomeName.innerText = data.nome;
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

        document.getElementById('btnLogout').addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = "../index.php";
            });
        });
    </script>
</body>
</html>
