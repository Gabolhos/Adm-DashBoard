<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - SISTEMA MECANICA</title>
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
                <img src="../IMG/profile-1.jpg" alt="Admin">
            </div>
            <div class="profile-info">
                <h4 id="user-name-display">Carregando...</h4>
                <p>Administrador</p>
            </div>
        </div>
        <ul class="menu">
            <li><a href="painel_adm.php"><i class='bx bxs-grid-alt'></i> Painel</a></li>
            <li><a href="ordens_servico.php"><i class='bx bx-list-ul'></i> Ordens de serviço</a></li>
            <li><a href="painel_cliente.php" class="active"><i class='bx bx-user'></i> Clientes</a></li>
        </ul>
        <div class="sidebar-footer">
            Desenvolvido por StiloDev
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Clientes</h2>
                <p>Bem-Vindo Usuário à <span>Tratto Mecânica</span></p>
            </div>
            <div class="logout-container" id="btnLogout">
                <i class='bx bx-log-out'></i>
                <span>Sair</span>
            </div>
        </div>

        <div class="history-section" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
            <p style="color: #888; font-size: 18px;">Área de Clientes - Em manutenção</p>
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

        document.getElementById('btnLogout').addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = "../index.php";
            });
        });
    </script>
</body>
</html>
