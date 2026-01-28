<?php
require_once 'db.php';

// Ensure database is selected
$conn->select_db(DB_NAME);

// Table creation
$queries = [
    "CREATE TABLE IF NOT EXISTS Cliente (
        id_cliente INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        cpf VARCHAR(11) UNIQUE NOT NULL,
        telefone VARCHAR(15) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        endereco VARCHAR(255) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS Veiculo (
        id_veiculo INT AUTO_INCREMENT PRIMARY KEY,
        placa VARCHAR(10) UNIQUE NOT NULL,
        marca VARCHAR(50) NOT NULL,
        modelo VARCHAR(50) NOT NULL,
        ano INT NOT NULL,
        cor VARCHAR(30) NOT NULL,
        id_cliente INT,
        FOREIGN KEY (id_cliente) REFERENCES Cliente(id_cliente)
    )",
    "CREATE TABLE IF NOT EXISTS mecanico (
        id_mecanico INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        telefone VARCHAR(15) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS Servico (
        id_servico INT AUTO_INCREMENT PRIMARY KEY,
        descricao VARCHAR(255) NOT NULL,
        valor DECIMAL(10,2) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS Peca (
        id_peca INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        marca VARCHAR(100),
        descricao VARCHAR(255),
        preco DECIMAL(10,2) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS ordem_servico (
        id_ordem_servico INT AUTO_INCREMENT PRIMARY KEY,
        data_emissao DATE NOT NULL,
        data_conclusao DATE,
        status VARCHAR(50) NOT NULL,
        valor_total DECIMAL(10,2) NOT NULL,
        id_veiculo INT,
        id_mecanico INT,
        relato TEXT,
        FOREIGN KEY (id_veiculo) REFERENCES Veiculo(id_veiculo),
        FOREIGN KEY (id_mecanico) REFERENCES mecanico(id_mecanico)
    )",
    "CREATE TABLE IF NOT EXISTS ordem_servico_pecas (
        id_ordem_servico INT,
        id_peca INT,
        quantidade INT NOT NULL,
        PRIMARY KEY (id_ordem_servico, id_peca),
        FOREIGN KEY (id_ordem_servico) REFERENCES ordem_servico(id_ordem_servico),
        FOREIGN KEY (id_peca) REFERENCES Peca(id_peca)
    )",
    "CREATE TABLE IF NOT EXISTS ordem_servico_servicos (
        id_ordem_servico INT,
        id_servico INT,
        quantidade INT NOT NULL DEFAULT 1,
        FOREIGN KEY (id_ordem_servico) REFERENCES ordem_servico(id_ordem_servico),
        FOREIGN KEY (id_servico) REFERENCES Servico(id_servico)
    )"
];

foreach ($queries as $query) {
    if (!$conn->query($query)) {
        echo "Error creating table: " . $conn->error . "\n";
    }
}

// Dummy Data
$dummy_data = [
    "INSERT IGNORE INTO Cliente (id_cliente, nome, cpf, telefone, email, endereco) VALUES (1, 'Gabriel Henrique', '12345678901', '11999999999', 'gabriel@email.com', 'Rua A, 123')",
    "INSERT IGNORE INTO Veiculo (id_veiculo, placa, marca, modelo, ano, cor, id_cliente) VALUES (1, 'BRA2E19', 'Chevrolet', 'Onix', 2022, 'Prata', 1)",
    "INSERT IGNORE INTO mecanico (id_mecanico, nome, telefone) VALUES (1, 'Jadir Buratto', '11888888888')",
    "INSERT IGNORE INTO Servico (id_servico, descricao, valor) VALUES (1, 'Suspensão', 500.00)",
    "INSERT IGNORE INTO Servico (id_servico, descricao, valor) VALUES (2, 'Troca de Óleo', 150.00)",
    "INSERT IGNORE INTO Servico (id_servico, descricao, valor) VALUES (3, 'Alinhamento', 80.00)",
    "INSERT IGNORE INTO Peca (id_peca, nome, marca, preco) VALUES (1, 'Amortecedor', 'Cofap', 300.00)",
    "INSERT IGNORE INTO ordem_servico (id_ordem_servico, data_emissao, status, valor_total, id_veiculo, id_mecanico, relato) VALUES (1, '2023-10-01', 'Em andamento', 9102.00, 1, 1, 'Barulho na suspensão dianteira')",
    "INSERT IGNORE INTO ordem_servico_servicos (id_ordem_servico, id_servico) VALUES (1, 1)",
    "INSERT IGNORE INTO ordem_servico (id_ordem_servico, data_emissao, status, valor_total, id_veiculo, id_mecanico, relato) VALUES (2, '2023-10-02', 'Aguardando peças', 1500.00, 1, 1, 'Necessário trocar amortecedores')",
    "INSERT IGNORE INTO ordem_servico (id_ordem_servico, data_emissao, status, valor_total, id_veiculo, id_mecanico, relato) VALUES (3, '2023-10-03', 'Aberto', 200.00, 1, 1, 'Revisão geral')",
    "INSERT IGNORE INTO ordem_servico (id_ordem_servico, data_emissao, status, valor_total, id_veiculo, id_mecanico, relato) VALUES (4, '2023-10-04', 'Concluído', 500.00, 1, 1, 'Troca de óleo concluída')"
];

foreach ($dummy_data as $data) {
    $conn->query($data);
}

echo "Database initialized successfully.\n";
?>
