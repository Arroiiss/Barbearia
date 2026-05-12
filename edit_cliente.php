<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: admin_dashboard.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $idade = $_POST['idade'];
    $cpf = $_POST['cpf'];

    if (!validarCPF($cpf)) {
        $error = "CPF inválido. Por favor, verifique os números digitados.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nome_completo = ?, idade = ?, cpf = ? WHERE id = ?");
        $stmt->execute([$nome, $idade, $cpf, $id]);
        header("Location: admin_dashboard.php");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('img/logo.png') no-repeat center center fixed; 
            background-size: cover;
            color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background-color: rgba(30, 30, 30, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff !important;
            border-top: 4px solid #d4af37;
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
        .btn-custom {
            background-color: #2c2c2c;
            color: #d4af37;
            border: 1px solid #d4af37;
            text-transform: uppercase;
            font-weight: bold;
        }
        .btn-custom:hover {
            background-color: #d4af37;
            color: #1a1a1a;
        }
        .btn-outline-secondary {
            color: #fff;
            border-color: rgba(255,255,255,0.3);
        }
        .btn-outline-secondary:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 500px;">
    <div class="card p-5 shadow-lg">
        <h3 class="fw-bold text-uppercase mb-3" style="color: #d4af37; letter-spacing: 1px;">EDITAR CLIENTE</h3>
        <p class="text-white-50 small text-uppercase mb-4">Atualize as informações do cadastro</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger bg-danger text-white border-0 py-2 small mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-uppercase" style="color: #d4af37;">Nome Completo</label>
                <input type="text" name="nome" class="form-control py-2" value="<?php echo $cliente['nome_completo']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small text-uppercase" style="color: #d4af37;">Idade</label>
                <input type="number" name="idade" class="form-control py-2" value="<?php echo $cliente['idade']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small text-uppercase" style="color: #d4af37;">CPF</label>
                <input type="text" name="cpf" class="form-control py-2" value="<?php echo $cliente['cpf']; ?>" required>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-custom py-2">Salvar Alterações</button>
                <a href="admin_dashboard.php" class="btn btn-outline-secondary py-2 small text-uppercase" style="letter-spacing: 1px;">Cancelar</a>
            </div>
        </form>
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