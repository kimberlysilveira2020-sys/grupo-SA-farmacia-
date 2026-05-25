-- =========================================
-- TABELA DE CLIENTES
-- =========================================
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100) UNIQUE, -- Garantir que não haja e-mails duplicados para o login
    senha VARCHAR(255),        -- Campo essencial para o futuro login no site (armazenar hash)
    endereco VARCHAR(150),
    INDEX idx_cliente_cpf (cpf)  -- Otimiza a busca de clientes pelo CPF
);

-- =========================================
-- TABELA DE FUNCIONÁRIOS
-- =========================================
CREATE TABLE funcionarios (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cargo VARCHAR(50) NOT NULL,
    salario DECIMAL(10,2) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100) UNIQUE, -- Para o funcionário logar no painel administrativo
    senha VARCHAR(255),        -- Senha criptografada para o painel
    CONSTRAINT chk_salario CHECK (salario >= 0) -- Impede salários negativos
);

-- =========================================
-- TABELA DE FORNECEDORES
-- =========================================
CREATE TABLE fornecedores (
    id_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    nome_empresa VARCHAR(100) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100)
);

-- =========================================
-- TABELA DE PRODUTOS
-- =========================================
CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    validade DATE NOT NULL,
    id_fornecedor INT NOT NULL,

    CONSTRAINT fk_produtos_fornecedores 
        FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
        ON DELETE RESTRICT ON UPDATE CASCADE, -- Protege o fornecedor se houver produtos vinculados
        
    CONSTRAINT chk_preco CHECK (preco > 0), -- O preço de venda deve ser maior que zero
    CONSTRAINT chk_estoque CHECK (quantidade_estoque >= 0), -- O estoque nunca pode ser negativo
    INDEX idx_produto_categoria (categoria) -- Otimiza filtros de busca no site por categoria
);

-- =========================================
-- TABELA DE VENDAS
-- =========================================
CREATE TABLE vendas (
    id_venda INT AUTO_INCREMENT PRIMARY KEY,
    data_venda DATETIME DEFAULT CURRENT_TIMESTAMP, -- Pega automaticamente data e hora exata da venda
    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    id_cliente INT,      -- Pode ser NULL caso seja uma venda para "Cliente Não Identificado"
    id_funcionario INT NOT NULL,

    CONSTRAINT fk_vendas_clientes 
        FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
        ON DELETE RESTRICT ON UPDATE CASCADE,
        
    CONSTRAINT fk_vendas_funcionarios 
        FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario)
        ON DELETE RESTRICT ON UPDATE CASCADE,
        
    CONSTRAINT chk_valor_total CHECK (valor_total >= 0)
);

-- =========================================
-- TABELA ITENS_VENDA
-- =========================================
CREATE TABLE itens_venda (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_venda INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_itens_venda 
        FOREIGN KEY (id_venda) REFERENCES vendas(id_venda)
        ON DELETE CASCADE ON UPDATE CASCADE, -- Se a venda principal sumir, os itens somem junto
        
    CONSTRAINT fk_itens_produto 
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
        ON DELETE RESTRICT ON UPDATE CASCADE,
        
    CONSTRAINT chk_quantidade CHECK (quantidade > 0), -- Impede vender 0 ou itens negativos
    CONSTRAINT chk_subtotal CHECK (subtotal >= 0)
);

-- =========================================
-- TABELA DE ESTOQUE (OPÇÃO ROBUSTA PARA A SA)
-- =========================================
CREATE TABLE estoque (
    id_estoque INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,         -- Chave Estrangeira para saber o produto
    id_fornecedor INT NOT NULL,      -- Chave Estrangeira para saber quem forneceu esse lote
    numero_lote VARCHAR(50) NOT NULL, -- Essencial para farmácias (Anvisa)
    quantidade INT NOT NULL DEFAULT 0,
    data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Criando as regras de Chave Estrangeira (Foreign Keys)
    CONSTRAINT fk_estoque_produtos 
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT fk_estoque_fornecedores 
        FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Restrição de segurança
    CONSTRAINT chk_qtd_estoque CHECK (quantidade >= 0)
);

-- ============================================================================
-- CARGA DE DADOS INICIAIS (Apenas os dados base do seu grupo, higienizados)
-- ============================================================================

INSERT INTO clientes (nome, cpf, telefone, email, senha, endereco) VALUES
('Maria Silva', '123.456.789-00', '(11)99999-1111', 'maria@email.com', '$2y$10$ExemploHashSenhaCliente1', 'Rua A, 100'),
('João Souza', '987.654.321-00', '(11)98888-2222', 'joao@email.com', '$2y$10$ExemploHashSenhaCliente2', 'Rua B, 200');

INSERT INTO funcionarios (nome, cargo, salario, telefone, email, senha) VALUES
('Carlos Mendes', 'Atendente', 2500.00, '(11)97777-3333', 'carlos@farmavida.com', '$2y$10$HashSenhaFuncionario1'),
('Ana Lima', 'Farmacêutica', 4500.00, '(11)96666-4444', 'ana@farmavida.com', '$2y$10$HashSenhaFuncionario2');

INSERT INTO fornecedores (nome_empresa, cnpj, telefone, email) VALUES
('MedDistribuidora', '12.345.678/0001-99', '(11)95555-5555', 'contato@med.com');

INSERT INTO produtos (nome, categoria, preco, quantidade_estoque, validade, id_fornecedor) VALUES
('Paracetamol', 'Medicamento', 15.50, 100, '2027-12-31', 1),
('Vitamina C', 'Suplemento', 25.00, 50, '2026-10-15', 1);

INSERT INTO vendas (id_venda, data_venda, valor_total, id_cliente, id_funcionario) VALUES
(1, '2026-05-20 14:30:00', 40.50, 1, 1);

INSERT INTO itens_venda (id_venda, id_produto, quantidade, subtotal) VALUES
(1, 1, 1, 15.50),
(1, 2, 1, 25.00);