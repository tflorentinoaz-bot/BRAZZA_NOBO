<?php
// Inicia ou recupera a sessão do visitante.
session_start();

// Catálogo e preços cadastrados no servidor.
$produtos = [
    1 => ["nome" => "Chapa de Ferro", "preco" => 9990],
    2 => ["nome" => "Chapa Grande", "preco" => 48990],
    3 => ["nome" => "Prensa para smash", "preco" => 8990],
    4 => ["nome" => "Espatula Grande", "preco" => 11990],
    5 => ["nome" => "Forma moleladora", "preco" => 8990],
    6 => ["nome" => "Forma de preparo", "preco" => 3890],
    7 => ["nome" => "Tempero para Carne", "preco" => 1990],
    8 => ["nome" => "Sal de Parrilla", "preco" => 2490],
    9 => ["nome" => "Molho Barbecue", "preco" => 1890],
    10 => ["nome" => "Molho de Alho", "preco" => 1590]
];

// Recebe e valida o identificador do produto.
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || !isset($produtos[$id])) {
    header("Location: brazza.html");
    exit;
}

// Cria o carrinho caso ainda não exista.
if (!isset($_SESSION["carrinho"]) || !is_array($_SESSION["carrinho"])) {
    $_SESSION["carrinho"] = [];
}

// Adiciona uma unidade, respeitando o limite de 99 por produto.
$quantidade_atual = (int) ($_SESSION["carrinho"][$id] ?? 0);
$_SESSION["carrinho"][$id] = min($quantidade_atual + 1, 99);

// Abre o carrinho após adicionar o produto.
header("Location: carrinho.php?adicionado=1");
exit;
?>
