<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Mês e Ano para o filtro
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

// 1. Total de Vendas (Soma dos preços dos produtos nos agendamentos do mês)
$stmt = $pdo->prepare("
    SELECT SUM(p.preco) as total_faturamento, COUNT(DISTINCT a.id) as total_agendamentos
    FROM agendamentos a
    JOIN agendamento_produtos ap ON a.id = ap.agendamento_id
    JOIN produtos p ON ap.produto_id = p.id
    WHERE MONTH(a.data_agendamento) = ? AND YEAR(a.data_agendamento) = ?
");
$stmt->execute([$mes, $ano]);
$resumo = $stmt->fetch();

// 2. Ranking de Produtos mais vendidos no mês
$stmt = $pdo->prepare("
    SELECT p.nome, COUNT(ap.produto_id) as quantidade, SUM(p.preco) as subtotal
    FROM agendamento_produtos ap
    JOIN produtos p ON ap.produto_id = p.id
    JOIN agendamentos a ON ap.agendamento_id = a.id
    WHERE MONTH(a.data_agendamento) = ? AND YEAR(a.data_agendamento) = ?
    GROUP BY p.id
    ORDER BY quantidade DESC
");
$stmt->execute([$mes, $ano]);
$ranking = $stmt->fetchAll();

// 3. Vendas por dia (para um gráfico simples ou lista)
$stmt = $pdo->prepare("
    SELECT a.data_agendamento, SUM(p.preco) as total_dia
    FROM agendamentos a
    JOIN agendamento_produtos ap ON a.id = ap.agendamento_id
    JOIN produtos p ON ap.produto_id = p.id
    WHERE MONTH(a.data_agendamento) = ? AND YEAR(a.data_agendamento) = ?
    GROUP BY a.data_agendamento
    ORDER BY a.data_agendamento ASC
");
$stmt->execute([$mes, $ano]);
$vendasDiarias = $stmt->fetchAll();

// 4. Média de Idade dos Clientes Agendados no Período
$stmt = $pdo->prepare("
    SELECT AVG(u.idade) as media_idade 
    FROM users u 
    WHERE u.id IN (
        SELECT DISTINCT user_id 
        FROM agendamentos 
        WHERE MONTH(data_agendamento) = ? AND YEAR(data_agendamento) = ?
    )
");
$stmt->execute([$mes, $ano]);
$mediaIdade = $stmt->fetch();

$meses = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard de Vendas</title>
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
        .card {
            background-color: rgba(30, 30, 30, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff !important;
            border-top: 4px solid #d4af37;
        }
        .stat-card { 
            border-left: 5px solid #d4af37 !important; 
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        .table {
            color: #fff !important;
            margin-bottom: 0;
            background: transparent !important;
        }
        .table thead th {
            color: #d4af37;
            text-transform: uppercase;
            border-bottom: 2px solid #d4af37;
            background: rgba(0,0,0,0.2);
        }
        .table td {
            background: transparent !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .form-select {
            background-color: rgba(20, 20, 20, 0.8);
            color: #d4af37;
            border: 1px solid #d4af37;
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
        .list-group-item {
            background-color: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">BARBER SHOP ADMIN</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="admin_dashboard.php">Clientes</a>
            <a class="nav-link" href="admin_produtos.php">Produtos</a>
            <a class="nav-link" href="admin_agenda.php">Agenda</a>
            <a class="nav-link active" href="admin_vendas.php">Dashboard</a>
            <a class="nav-link ms-lg-3 btn btn-outline-warning btn-sm px-3" href="logout.php" style="color: #d4af37 !important;">SAIR</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color: #d4af37; letter-spacing: 2px;">RELATÓRIO DE VENDAS</h2>
        <form class="d-flex gap-2">
            <select name="mes" class="form-select form-select-sm">
                <?php foreach ($meses as $num => $nome): ?>
                    <option value="<?php echo $num; ?>" <?php echo $mes == $num ? 'selected' : ''; ?>><?php echo $nome; ?></option>
                <?php endforeach; ?>
            </select>
            <select name="ano" class="form-select form-select-sm">
                <?php for($i=2025; $i<=2027; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $ano == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-custom px-3">Filtrar</button>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card shadow-lg p-4">
                <h6 class="text-uppercase small fw-bold text-white-50 mb-2">Faturamento Mensal</h6>
                <h2 class="fw-bold" style="color: #d4af37;">R$ <?php echo number_format($resumo['total_faturamento'] ?? 0, 2, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card shadow-lg p-4">
                <h6 class="text-uppercase small fw-bold text-white-50 mb-2">Total de Agendamentos</h6>
                <h2 class="fw-bold" style="color: #d4af37;"><?php echo $resumo['total_agendamentos'] ?? 0; ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card shadow-lg p-4">
                <h6 class="text-uppercase small fw-bold text-white-50 mb-2">Média de Idade</h6>
                <h2 class="fw-bold" style="color: #d4af37;"><?php echo number_format($mediaIdade['media_idade'] ?? 0, 1); ?> <span class="small" style="font-size: 1rem;">anos</span></h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card shadow-lg p-4">
                <h5 class="fw-bold text-uppercase mb-4" style="color: #d4af37;">Serviços Mais Vendidos</h5>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ranking as $item): ?>
                            <tr>
                                <td class="fw-bold text-white"><?php echo $item['nome']; ?></td>
                                <td class="text-center text-white"><?php echo $item['quantidade']; ?></td>
                                <td class="text-end fw-bold" style="color: #d4af37;">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ranking)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted small">Nenhuma venda registrada no período.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-lg p-4">
                <h5 class="fw-bold text-uppercase mb-4" style="color: #d4af37;">Vendas Diárias</h5>
                <div style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($vendasDiarias as $vd): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="fw-bold"><?php echo date('d/m', strtotime($vd['data_agendamento'])); ?></span>
                                <strong style="color: #d4af37;">R$ <?php echo number_format($vd['total_dia'], 2, ',', '.'); ?></strong>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($vendasDiarias)): ?>
                            <li class="list-group-item text-center py-4 text-muted small">Sem dados para exibição.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>