<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - SISTEMA MECANICA</title>
    <style>
        :root { --primary: #4a90e2; --error: #e74c3c; --success: #2ecc71; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 380px; }
        h2 { text-align: center; color: #333; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; border: none; background: var(--primary); color: white; font-weight: bold; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }

        #message { padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; font-size: 14px; display: none; }
        .error { background: #fdeaea; color: var(--error); border: 1px solid var(--error); }
        .success { background: #e9f7ef; color: var(--success); border: 1px solid var(--success); }

        .btn-link { display: block; text-align: center; margin-top: 15px; color: var(--primary); text-decoration: none; font-size: 14px; }
        .hidden { display: none !important; }

        #btnIrParaLogin { background: #333 !important; color: white !important; padding: 12px; border-radius: 6px; text-align: center; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Criar Conta</h2>
    <div id="message"></div>

    <form id="registerForm">
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" id="email" required placeholder="seu@email.com">
        </div>
        <div class="form-group">
            <label>Senha (mínimo 6 caracteres)</label>
            <input type="password" id="password" required placeholder="******">
        </div>
        <button type="submit" id="btnCadastrar">Cadastrar</button>
    </form>

    <a href="index.php" id="btnIrParaLogin" class="btn-link hidden">Cadastrado com Sucesso! Ir para o Login</a>
    <a href="index.php" id="linkVoltar" class="btn-link">Já tem uma conta? Entrar</a>
</div>

<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
  import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

  const firebaseConfig = {
    apiKey: "AIzaSyC642PXZVjV96ORWO3qMcuEYe0lIMdIE9Q",
    authDomain: "mec-projeto-65861.firebaseapp.com",
    projectId: "mec-projeto-65861",
    storageBucket: "mec-projeto-65861.firebasestorage.app",
    messagingSenderId: "625687541988",
    appId: "1:625687541988:web:fc82f6cb314ecc380c14f1",
    measurementId: "G-0B2C05WQ0H"
  };

  const app = initializeApp(firebaseConfig);
  const auth = getAuth(app);

  const registerForm = document.getElementById('registerForm');
  const messageDiv = document.getElementById('message');
  const btnCadastrar = document.getElementById('btnCadastrar');
  const btnIrParaLogin = document.getElementById('btnIrParaLogin');
  const linkVoltar = document.getElementById('linkVoltar');

  registerForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    btnCadastrar.disabled = true;
    btnCadastrar.innerText = "Processando...";
    messageDiv.style.display = "none";

    // 1. Criar no Firebase
    createUserWithEmailAndPassword(auth, email, password)
      .then((userCredential) => {
        const user = userCredential.user;

        // 2. Enviar para o seu PHP
        return fetch('salvar_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                uid: user.uid,
                email: user.email
            })
        });
      })
      .then(response => response.json())
      .then(data => {
        // 3. Sucesso total (Firebase + MySQL)
        messageDiv.className = "success";
        messageDiv.innerText = "Conta criada e salva com sucesso!";
        messageDiv.style.display = "block";

        registerForm.classList.add('hidden');
        linkVoltar.classList.add('hidden');
        btnIrParaLogin.classList.remove('hidden');
      })
      .catch((error) => {
        messageDiv.className = "error";
        messageDiv.style.display = "block";
        btnCadastrar.disabled = false;
        btnCadastrar.innerText = "Cadastrar";

        switch (error.code) {
            case 'auth/email-already-in-use':
                messageDiv.innerText = "Este e-mail já está em uso.";
                break;
            case 'auth/weak-password':
                messageDiv.innerText = "A senha deve ter pelo menos 6 caracteres.";
                break;
            default:
                messageDiv.innerText = "Erro: " + error.message;
        }
      });
  });
</script>

</body>
</html>
