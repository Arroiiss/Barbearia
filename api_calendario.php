<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode([]));
}

// GET: Buscar horários disponíveis
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['data'])) {
    $data = $_GET['data'];
    
    // Buscar configurações de funcionamento
    $stmt = $pdo->query("SELECT * FROM configuracoes_horario LIMIT 1");
    $config = $stmt->fetch();
    
    $abertura = strtotime($config['hora_abertura']);
    $fechamento = strtotime($config['hora_fechamento']);
    
    // Buscar agendamentos existentes no dia
    $stmt = $pdo->prepare("SELECT hora_agendamento FROM agendamentos WHERE data_agendamento = ?");
    $stmt->execute([$data]);
    $ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $disponiveis = [];
    $atual = $abertura;
    
    while ($atual < $fechamento) {
        $horaStr = date('H:i:00', $atual);
        if (!in_array($horaStr, $ocupados)) {
            $disponiveis[] = date('H:i', $atual);
        }
        $atual = strtotime("+1 hour", $atual); // Intervalo de 1 hora
    }
    
    echo json_encode($disponiveis);
    exit();
}

// POST: Realizar agendamento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['data']) && isset($_POST['hora'])) {
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $produtos = $_POST['produtos'] ?? [];
    $user_id = $_SESSION['user_id'];

    // Verificar novamente se está livre
    $stmt = $pdo->prepare("SELECT id FROM agendamentos WHERE data_agendamento = ? AND hora_agendamento = ?");
    $stmt->execute([$data, $hora]);
    if ($stmt->fetch()) {
        header("Location: dashboard.php?error=ja_ocupado");
        exit();
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO agendamentos (user_id, data_agendamento, hora_agendamento) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $data, $hora]);
        $agendamento_id = $pdo->lastInsertId();

        if (!empty($produtos)) {
            $stmtProd = $pdo->prepare("INSERT INTO agendamento_produtos (agendamento_id, produto_id) VALUES (?, ?)");
            foreach ($produtos as $p_id) {
                $stmtProd->execute([$agendamento_id, $p_id]);
            }
        }
        $pdo->commit();
        header("Location: dashboard.php?msg=sucesso");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: dashboard.php?error=erro_db");
    }
    exit();
}
?>