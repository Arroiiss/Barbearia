<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE cpf = ?");
    $stmt->execute([$cpf]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome_completo'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "CPF ou Senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: url('img/logo.png') no-repeat center center fixed; 
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container { 
            max-width: 400px; 
            width: 100%;
            background-color: rgba(20, 20, 20, 0.85) !important; /* Tom escuro transparente */
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
        }
        .btn-custom {
            background-color: #2c2c2c;
            color: #d4af37; /* Tom dourado/vintage para o texto */
            border: 1px solid #d4af37;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #d4af37;
            color: #1a1a1a;
            border-color: #d4af37;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #d4af37;
            color: #fff;
            box-shadow: none;
        }
        .text-link {
            color: #d4af37;
            text-decoration: none;
        }
        .text-link:hover {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container login-container p-5 shadow-lg rounded-3">
    <div class="text-center mb-4">
        <h2 class="mb-0 fw-bold" style="letter-spacing: 3px;">BARBER SHOP</h2>
        <small style="color: #d4af37;">EST. 2026</small>
    </div>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger bg-danger text-white border-0 py-2"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label small text-uppercase" style="letter-spacing: 1px;">Usuário / CPF</label>
            <input type="text" name="cpf" class="form-control py-2" placeholder="Digite seu acesso" required>
        </div>
        <div class="mb-3">
            <label class="form-label small text-uppercase" style="letter-spacing: 1px;">Senha</label>
            <input type="password" name="senha" class="form-control py-2" placeholder="******" required>
        </div>
        <button type="submit" class="btn btn-custom w-100 py-2 mt-3">Entrar no Recinto</button>
    </form>
    <div class="text-center mt-4">
        <p class="small">Novo por aqui? <a href="register.php" class="text-link">Criar Conta</a></p>
    </div>
</div>
</body>
</html>