<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Firebase + PHP - SISTEMA MECANICA</title>
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
        .register-link { display: block; text-align: center; margin-top: 15px; font-size: 14px; color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Acessar Sistema</h2>
    <div id="message"></div>

    <form id="loginForm">
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" id="email" required placeholder="seu@email.com">
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" id="password" required placeholder="******">
        </div>
        <button type="submit" id="btnEntrar">Entrar</button>
    </form>
    <a href="cadastro.php" class="register-link">Não tem uma conta? Cadastre-se</a>
</div>

<script type="module">
  import { auth } from "./firebase-config.js";
  import { signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

  const loginForm = document.getElementById('loginForm');
  const messageDiv = document.getElementById('message');
  const btnEntrar = document.getElementById('btnEntrar');

  loginForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    btnEntrar.disabled = true;
    btnEntrar.innerText = "Verificando...";
    messageDiv.style.display = "none";

    // 1. Tenta logar no Firebase
    signInWithEmailAndPassword(auth, email, password)
      .then((userCredential) => {
        const user = userCredential.user;

        // 2. Envia como JSON para o PHP
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
      .then(response => {
          if (!response.ok) throw new Error('Falha na resposta do servidor');
          return response.json();
      })
      .then(data => {
        // 3. Sucesso total
        messageDiv.className = "success";
        messageDiv.innerText = "Login realizado! Entrando...";
        messageDiv.style.display = "block";

        setTimeout(() => {
            window.location.href = "PAINEL/painel_adm.php";
        }, 1200);
      })
      .catch((error) => {
        messageDiv.className = "error";
        messageDiv.style.display = "block";
        btnEntrar.disabled = false;
        btnEntrar.innerText = "Entrar";

        if (error.code === 'auth/invalid-credential' || error.code === 'auth/invalid-login-credentials' || error.code === 'auth/wrong-password') {
            messageDiv.innerText = "E-mail ou senha incorretos.";
        } else if (error.code === 'auth/user-not-found') {
            messageDiv.innerText = "Usuário não encontrado.";
        } else {
            messageDiv.innerText = "Erro: " + error.message;
        }
      });
  });
</script>

</body>
</html>
