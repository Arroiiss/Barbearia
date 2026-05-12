<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $abertura = $_POST['abertura'];
    $fechamento = $_POST['fechamento'];

    $stmt = $pdo->prepare("UPDATE configuracoes_horario SET hora_abertura = ?, hora_fechamento = ? WHERE id = 1");
    $stmt->execute([$abertura, $fechamento]);
    $success = "Configurações atualizadas com sucesso!";
}

$stmt = $pdo->query("SELECT * FROM configuracoes_horario WHERE id = 1");
$config = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Agenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('img/logo.png') no-repeat center center fixed; 
            background-size: cover;
            color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: rgba(20, 20, 20, 0.95) !important;
            border-bottom: 1px solid #d4af37;
        }
        .navbar-brand, .nav-link {
            color: #d4af37 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .nav-link.active { border-bottom: 2px solid #d4af37; }
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
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-auto">
    <div class="container">
        <a class="navbar-brand" href="#">BARBER SHOP ADMIN</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="admin_dashboard.php">Clientes</a>
            <a class="nav-link" href="admin_produtos.php">Produtos</a>
            <a class="nav-link active" href="admin_agenda.php">Agenda</a>
            <a class="nav-link" href="admin_vendas.php">Dashboard</a>
            <a class="nav-link ms-lg-3 btn btn-outline-warning btn-sm px-3" href="logout.php" style="color: #d4af37 !important;">SAIR</a>
        </div>
    </div>
</nav>

<div class="container d-flex justify-content-center align-items-center flex-grow-1">
    <div class="card p-5 shadow-lg w-100" style="max-width: 500px;">
        <h3 class="fw-bold text-uppercase mb-3" style="color: #d4af37; letter-spacing: 1px;">HORÁRIO DE EXPEDIENTE</h3>
        <p class="text-white-50 small text-uppercase mb-4">Configure os limites de atendimento</p>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success bg-success text-white border-0 py-2 small mb-4"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="form-label small text-uppercase" style="color: #d4af37;">Hora de Abertura</label>
                <input type="time" name="abertura" class="form-control py-2" value="<?php echo $config['hora_abertura']; ?>" required>
            </div>
            <div class="mb-4">
                <label class="form-label small text-uppercase" style="color: #d4af37;">Hora de Encerramento</label>
                <input type="time" name="fechamento" class="form-control py-2" value="<?php echo $config['hora_fechamento']; ?>" required>
            </div>
            <button type="submit" class="btn btn-custom w-100 py-3 mt-2">Salvar Configurações</button>
        </form>
    </div>
</div>
<div class="mt-auto py-3 text-center text-white-50 small">BARBER SHOP © 2026 - ÁREA ADMINISTRATIVA</div>
</body>
</html>