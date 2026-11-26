-- Tabelas para registrar pedidos e seus produtos.
-- Execute este arquivo uma única vez no banco fogoebraza.
-- A tabela fogo deve estar usando InnoDB e id_cliente deve ser INT.

USE fogoebraza;

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT NOT NULL AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    forma_recebimento VARCHAR(20) NOT NULL,
    endereco_entrega VARCHAR(200) DEFAULT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status_pedido VARCHAR(30) NOT NULL DEFAULT 'Recebido',
    PRIMARY KEY (id_pedido),
    INDEX idx_pedidos_cliente (id_cliente),
    CONSTRAINT fk_pedidos_cliente
        FOREIGN KEY (id_cliente) REFERENCES fogo (id_cliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS itens_pedido (
    id_item INT NOT NULL AUTO_INCREMENT,
    id_pedido INT NOT NULL,
    id_produto INT NOT NULL,
    nome_produto VARCHAR(100) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_item),
    INDEX idx_itens_pedido (id_pedido),
    CONSTRAINT fk_itens_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedidos (id_pedido)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
