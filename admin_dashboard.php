<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->query("SELECT id, nome_completo, idade, cpf FROM users WHERE role = 'client'");
$clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('img/logo.png') no-repeat center center fixed; 
            background-size: cover;
            color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
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
        .nav-link.active {
            border-bottom: 2px solid #d4af37;
        }
        .table-container {
            background-color: rgba(30, 30, 30, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            border-top: 4px solid #d4af37;
        }
        .table {
            color: #fff !important;
            margin-bottom: 0;
        }
        .table thead th {
            background-color: rgba(0, 0, 0, 0.3);
            color: #d4af37;
            text-transform: uppercase;
            border-bottom: 2px solid #d4af37;
            letter-spacing: 1px;
            padding: 15px;
        }
        .table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            vertical-align: middle;
            background: transparent !important;
        }
        .table tbody tr:hover {
            background-color: rgba(212, 175, 55, 0.1) !important;
        }
        .btn-custom {
            background-color: #2c2c2c;
            color: #d4af37;
            border: 1px solid #d4af37;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .btn-custom:hover {
            background-color: #d4af37;
            color: #1a1a1a;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">BARBER SHOP ADMIN</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link active" href="admin_dashboard.php">Clientes</a>
            <a class="nav-link" href="admin_produtos.php">Produtos</a>
            <a class="nav-link" href="admin_agenda.php">Agenda</a>
            <a class="nav-link" href="admin_vendas.php">Dashboard</a>
            <a class="nav-link ms-lg-3 btn btn-outline-warning btn-sm px-3" href="logout.php" style="color: #d4af37 !important;">SAIR</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="fw-bold mb-4" style="color: #d4af37; letter-spacing: 2px;">GESTÃO DE CLIENTES</h2>
    <div class="table-container shadow-lg">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome Completo</th>
                        <th>Idade</th>
                        <th>CPF</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td class="text-white-50">#<?php echo $c['id']; ?></td>
                        <td class="fw-bold text-white"><?php echo $c['nome_completo']; ?></td>
                        <td class="text-white"><?php echo $c['idade']; ?> anos</td>
                        <td class="text-white"><?php echo $c['cpf']; ?></td>
                        <td class="text-center">
                            <a href="edit_cliente.php?id=<?php echo $c['id']; ?>" class="btn btn-custom py-1 px-3">Editar</a>
                            <a href="delete_cliente.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-danger border-0 btn-sm ms-2" onclick="return confirm('Deseja excluir este cliente?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>