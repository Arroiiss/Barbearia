<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Ações (Insert, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $id = $_POST['id'] ?? null;

    if ($id) { // Update
        $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ? WHERE id = ?");
        $stmt->execute([$nome, $descricao, $preco, $id]);
    } else { // Insert
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $descricao, $preco]);
    }
    header("Location: admin_produtos.php");
    exit();
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin_produtos.php");
    exit();
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch();
}

$stmt = $pdo->query("SELECT * FROM produtos");
$produtos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('img/logo.png') no-repeat center center fixed; 
            background-size: cover;
            color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .card, .table {
            background-color: rgba(30, 30, 30, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }
        .table {
            background: transparent !important;
            margin-bottom: 0;
        }
        .table thead th {
            background-color: rgba(0, 0, 0, 0.3);
            color: #d4af37;
            text-transform: uppercase;
            border-bottom: 2px solid #d4af37;
            padding: 15px;
        }
        .table td {
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            vertical-align: middle;
            color: #fff !important;
        }
        .table tbody tr:hover {
            background-color: rgba(212, 175, 55, 0.1) !important;
        }
        .form-label {
            color: #d4af37;
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
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">BARBER SHOP ADMIN</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="admin_dashboard.php">Clientes</a>
            <a class="nav-link active" href="admin_produtos.php">Produtos</a>
            <a class="nav-link" href="admin_agenda.php">Agenda</a>
            <a class="nav-link" href="admin_vendas.php">Dashboard</a>
            <a class="nav-link ms-lg-3 btn btn-outline-warning btn-sm px-3" href="logout.php" style="color: #d4af37 !important;">SAIR</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card p-4 shadow-lg" style="border-top: 4px solid #d4af37;">
                <h4 class="fw-bold text-uppercase mb-4" style="color: #d4af37;"><?php echo $editProduct ? 'Editar' : 'Novo'; ?> Produto</h4>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $editProduct['id'] ?? ''; ?>">
                    <div class="mb-3">
                        <label class="form-label small text-uppercase">Nome do Serviço</label>
                        <input type="text" name="nome" class="form-control" value="<?php echo $editProduct['nome'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-uppercase">Descrição Curta</label>
                        <textarea name="descricao" class="form-control" rows="3"><?php echo $editProduct['descricao'] ?? ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-uppercase">Preço (R$)</label>
                        <input type="number" step="0.01" name="preco" class="form-control" value="<?php echo $editProduct['preco'] ?? ''; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-custom w-100 py-2"><?php echo $editProduct ? 'Atualizar Dados' : 'Cadastrar Serviço'; ?></button>
                    <?php if ($editProduct): ?>
                        <a href="admin_produtos.php" class="btn btn-outline-secondary w-100 mt-2 btn-sm text-uppercase">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-lg table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $p['nome']; ?></td>
                            <td style="color: #d4af37;">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-custom px-3">Editar</a>
                                <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger border-0 ms-1" onclick="return confirm('Excluir este serviço?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($produtos)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">Nenhum serviço cadastrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>