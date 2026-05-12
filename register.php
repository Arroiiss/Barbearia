<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $idade = $_POST['idade'];
    $cpf = $_POST['cpf'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    if (!validarCPF($cpf)) {
        $error = "CPF inválido. Por favor, verifique os números digitados.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (nome_completo, idade, cpf, senha) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $idade, $cpf, $senha]);
            header("Location: index.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "Erro ao cadastrar: CPF já existente ou dados inválidos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Barbearia</title>
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
        .register-container { 
            max-width: 500px; 
            width: 100%;
            background-color: rgba(20, 20, 20, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
        }
        .btn-custom {
            background-color: #2c2c2c;
            color: #d4af37;
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
<div class="container register-container p-5 shadow-lg rounded-3">
    <div class="text-center mb-4">
        <h2 class="mb-0 fw-bold" style="letter-spacing: 3px;">NOVO CLIENTE</h2>
        <small style="color: #d4af37;">JUNTE-SE À BARBER SHOP</small>
    </div>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger bg-danger text-white border-0 py-2"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label small text-uppercase">Nome Completo</label>
            <input type="text" name="nome" class="form-control" placeholder="Ex: João Silva" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small text-uppercase">Idade</label>
                <input type="number" name="idade" class="form-control" placeholder="Sua idade" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small text-uppercase">CPF</label>
                <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label small text-uppercase">Escolha uma Senha</label>
            <input type="password" name="senha" class="form-control" placeholder="******" required>
        </div>
        <button type="submit" class="btn btn-custom w-100 py-2 mt-3">Confirmar Cadastro</button>
    </form>
    <div class="text-center mt-4">
        <p class="small">Já é de casa? <a href="index.php" class="text-link">Fazer Login</a></p>
    </div>
</div>

<script>
document.querySelector('input[name="cpf"]').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    let formatted = value;
    if (value.length > 3) formatted = value.slice(0, 3) + '.' + value.slice(3);
    if (value.length > 6) formatted = formatted.slice(0, 7) + '.' + formatted.slice(7);
    if (value.length > 9) formatted = formatted.slice(0, 11) + '-' + formatted.slice(11);
    
    e.target.value = formatted;
});
</script>
</body>
</html>