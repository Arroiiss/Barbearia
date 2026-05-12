<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'client') {
    header("Location: index.php");
    exit();
}

// Configurações do Calendário
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDayOfMonth);
$dateComponents = getdate($firstDayOfMonth);
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
$monthName = $meses[(int)$month];
$dayOfWeek = $dateComponents['wday'];

$today = date('Y-m-d');

// Buscar todos os produtos disponíveis
$stmt = $pdo->query("SELECT * FROM produtos");
$produtosDisponiveis = $stmt->fetchAll();

// Buscar agendamentos do usuário com produtos
$stmt = $pdo->prepare("
    SELECT a.*, GROUP_CONCAT(p.nome SEPARATOR ', ') as nomes_produtos 
    FROM agendamentos a 
    LEFT JOIN agendamento_produtos ap ON a.id = ap.agendamento_id 
    LEFT JOIN produtos p ON ap.produto_id = p.id 
    WHERE a.user_id = ? 
    GROUP BY a.id 
    ORDER BY a.data_agendamento, a.hora_agendamento
");
$stmt->execute([$_SESSION['user_id']]);
$meusAgendamentos = $stmt->fetchAll();

// Função para checar regra de 24h
function podeEditar($data, $hora) {
    $agendamento = strtotime("$data $hora");
    $agora = time();
    return ($agendamento - $agora) > (24 * 3600);
}

// Ação de Cancelar Agendamento
if (isset($_GET['cancelar'])) {
    $id_agend = $_GET['cancelar'];
    $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id = ? AND user_id = ?");
    $stmt->execute([$id_agend, $_SESSION['user_id']]);
    $ag = $stmt->fetch();
    
    if ($ag && podeEditar($ag['data_agendamento'], $ag['hora_agendamento'])) {
        $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
        $stmt->execute([$id_agend]);
        header("Location: dashboard.php?msg=cancelado");
        exit();
    } else {
        header("Location: dashboard.php?error=tempo_limite");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Cliente - Barbearia</title>
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
        .card {
            background-color: rgba(30, 30, 30, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .calendar { width: 100%; table-layout: fixed; color: #fff; }
        .calendar td { 
            height: 80px; 
            text-align: center; 
            vertical-align: middle; 
            cursor: pointer; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            transition: all 0.2s;
        }
        .calendar td:hover { background-color: rgba(212, 175, 55, 0.2); }
        .calendar .today { color: #d4af37; font-weight: bold; border: 2px solid #d4af37; }
        .calendar .past { color: #555; cursor: default; }
        .calendar .selected { background-color: #d4af37; color: #000; }
        
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
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .badge { background-color: #d4af37 !important; color: #000; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">BARBER SHOP</a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 text-white">Bem-vindo, <?php echo $_SESSION['user_nome']; ?></span>
            <a class="nav-link btn btn-outline-warning btn-sm px-3" href="logout.php" style="color: #d4af37 !important;">SAIR</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-7">
            <div class="card p-4 shadow-lg mb-4">
                <h3 class="fw-bold" style="color: #d4af37;">RESERVE SEU HORÁRIO</h3>
                <p class="text-muted small text-uppercase">Escolha o dia e os serviços desejados</p>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="?month=<?php echo date('m', strtotime("-1 month", $firstDayOfMonth)); ?>&year=<?php echo date('Y', strtotime("-1 month", $firstDayOfMonth)); ?>" class="btn btn-sm btn-custom"><i class="bi bi-chevron-left"></i> Anterior</a>
                    <h4 class="mb-0 text-uppercase" style="letter-spacing: 2px;"><?php echo $monthName . " " . $year; ?></h4>
                    <a href="?month=<?php echo date('m', strtotime("+1 month", $firstDayOfMonth)); ?>&year=<?php echo date('Y', strtotime("+1 month", $firstDayOfMonth)); ?>" class="btn btn-sm btn-custom">Próximo <i class="bi bi-chevron-right"></i></a>
                </div>

                <table class="table calendar">
                    <thead>
                        <tr class="text-uppercase small" style="color: #d4af37;">
                            <th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <?php
                        for ($i = 0; $i < $dayOfWeek; $i++) echo "<td></td>";
                        
                        $currentDay = 1;
                        while ($currentDay <= $numberDays) {
                            if ($dayOfWeek == 7) {
                                $dayOfWeek = 0;
                                echo "</tr><tr>";
                            }
                            
                            $date = sprintf("%04d-%02d-%02d", $year, $month, $currentDay);
                            $class = ($date == $today) ? "today" : "";
                            if ($date < $today) $class .= " past";
                            
                            echo "<td class='$class' onclick='selecionarDia(\"$date\")'>$currentDay</td>";
                            
                            $currentDay++;
                            $dayOfWeek++;
                        }
                        while ($dayOfWeek < 7) {
                            echo "<td></td>";
                            $dayOfWeek++;
                        }
                        ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-md-5">
            <div id="horarios-container" class="card p-4 shadow-lg d-none mb-4" style="border-top: 4px solid #d4af37;">
                <h5 class="fw-bold text-uppercase">Agendamento: <span id="data-selecionada" style="color: #d4af37;"></span></h5>
                <hr class="border-secondary">
                <h6 class="small text-uppercase mb-3" style="color: #d4af37;">1. Serviços Disponíveis:</h6>
                <div class="mb-4">
                    <?php foreach ($produtosDisponiveis as $prod): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input check-produto" type="checkbox" value="<?php echo $prod['id']; ?>" id="prod_<?php echo $prod['id']; ?>">
                            <label class="form-check-label small" for="prod_<?php echo $prod['id']; ?>">
                                <?php echo $prod['nome']; ?> — <span style="color: #d4af37;">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h6 class="small text-uppercase mb-3" style="color: #d4af37;">2. Escolha o Horário:</h6>
                <div id="lista-horarios" class="d-grid gap-2">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <div class="card p-4 shadow-lg">
                <h5 class="fw-bold text-uppercase mb-3">Minha Agenda</h5>
                <div class="list-group list-group-flush">
                    <?php foreach ($meusAgendamentos as $ag): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold" style="color: #d4af37;"><?php echo date('d/m/Y', strtotime($ag['data_agendamento'])); ?> — <?php echo date('H:i', strtotime($ag['hora_agendamento'])); ?></div>
                                    <small class="text-white-50"><?php echo $ag['nomes_produtos'] ?: 'Sem serviços'; ?></small>
                                </div>
                                <?php if (podeEditar($ag['data_agendamento'], $ag['hora_agendamento'])): ?>
                                    <a href="?cancelar=<?php echo $ag['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Deseja cancelar este horário?')"><i class="bi bi-trash"></i></a>
                                <?php else: ?>
                                    <span class="badge rounded-pill">Confirmado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($meusAgendamentos)): ?>
                        <p class="small text-muted text-center py-3">Você ainda não possui agendamentos.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selecionarDia(data) {
    if (new Date(data + "T23:59:59") < new Date()) {
        return;
    }
    
    // Remover seleção visual anterior
    document.querySelectorAll('.calendar td').forEach(td => td.classList.remove('selected'));
    // Adicionar seleção visual ao clicado (simples busca por texto ou data-atrib se tivesse, mas o click já passa o dado)
    event.target.classList.add('selected');

    document.getElementById('data-selecionada').innerText = data.split('-').reverse().join('/');
    document.getElementById('horarios-container').classList.remove('d-none');
    
    fetch('api_calendario.php?data=' + data)
        .then(response => response.json())
        .then(horarios => {
            const container = document.getElementById('lista-horarios');
            container.innerHTML = '';
            if (horarios.length === 0) {
                container.innerHTML = '<p class="text-danger small text-center">Infelizmente não há horários para este dia.</p>';
                return;
            }
            horarios.forEach(h => {
                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-custom mb-1';
                btn.innerText = h;
                btn.onclick = () => agendar(data, h);
                container.appendChild(btn);
            });
        });
}

function agendar(data, hora) {
    const produtos = Array.from(document.querySelectorAll('.check-produto:checked')).map(cb => cb.value);
    
    if (produtos.length === 0) {
        alert("Nobre cliente, por favor selecione ao menos um serviço.");
        return;
    }
    
    if (confirm("Deseja confirmar seu agendamento para " + data + " às " + hora + "?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'api_calendario.php';
        
        const inData = document.createElement('input');
        inData.type = 'hidden'; inData.name = 'data'; inData.value = data;
        
        const inHora = document.createElement('input');
        inHora.type = 'hidden'; inHora.name = 'hora'; inHora.value = hora;
        
        produtos.forEach(pid => {
            const inProd = document.createElement('input');
            inProd.type = 'hidden'; inProd.name = 'produtos[]'; inProd.value = pid;
            form.appendChild(inProd);
        });
        
        form.appendChild(inData);
        form.appendChild(inHora);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>