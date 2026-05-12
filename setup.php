<?php
require_once 'config.php';

try {
    // Tabela de Usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_completo VARCHAR(255) NOT NULL,
        idade INT,
        cpf VARCHAR(14) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        role ENUM('admin', 'client') DEFAULT 'client'
    )");

    // Tabela de Produtos
    $pdo->exec("CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10, 2) NOT NULL
    )");

    // Tabela de Agendamentos
    $pdo->exec("CREATE TABLE IF NOT EXISTS agendamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        data_agendamento DATE NOT NULL,
        hora_agendamento TIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Tabela de Configurações de Horário
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes_horario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hora_abertura TIME NOT NULL,
        hora_fechamento TIME NOT NULL
    )");

    // Tabela Relacional Agendamentos <-> Produtos
    $pdo->exec("CREATE TABLE IF NOT EXISTS agendamento_produtos (
        agendamento_id INT NOT NULL,
        produto_id INT NOT NULL,
        PRIMARY KEY (agendamento_id, produto_id),
        FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
    )");

    // Inserir Admin padrão se não existir
    $stmt = $pdo->prepare("SELECT id FROM users WHERE cpf = 'admin'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $senhaAdmin = password_hash('123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (nome_completo, cpf, senha, role) VALUES ('Administrador', 'admin', '$senhaAdmin', 'admin')");
    }

    // Inserir Configuração de Horário padrão se não existir
    $stmt = $pdo->query("SELECT id FROM configuracoes_horario");
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO configuracoes_horario (hora_abertura, hora_fechamento) VALUES ('08:00:00', '18:00:00')");
    }

    echo "Banco de dados e tabelas configurados com sucesso!";
} catch (PDOException $e) {
    die("Erro no setup: " . $e->getMessage());
}
?>