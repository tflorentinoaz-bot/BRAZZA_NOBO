-- Atualização da estrutura existente do banco fogoebraza.
-- Faça um backup completo pelo phpMyAdmin antes de executar.
-- Execute este arquivo somente uma vez.

USE fogoebraza;

-- password_hash() precisa de espaço para armazenar o hash completo.
-- A coluna confirmar_senha não é usada pelo PHP e não deve guardar senha repetida.
-- InnoDB permitirá relacionar clientes, pedidos e itens nas próximas etapas.
ALTER TABLE fogo
    MODIFY senha VARCHAR(255) NOT NULL,
    DROP COLUMN confirmar_senha,
    ENGINE = InnoDB;
