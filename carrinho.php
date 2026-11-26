<?php
// Inicia ou recupera a sessão do visitante.
session_start();

// Lista de produtos disponíveis no catálogo atual do site.
// Os preços ficam no servidor para evitar que sejam alterados pelo navegador.
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

// Garante que o carrinho exista na sessão.
if (!isset($_SESSION["carrinho"]) || !is_array($_SESSION["carrinho"])) {
    $_SESSION["carrinho"] = [];
}

// Atualiza a quantidade ou remove um produto.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $acao = $_POST["acao"] ?? "";

    if ($id && isset($produtos[$id])) {
        if ($acao === "remover") {
            unset($_SESSION["carrinho"][$id]);
        } elseif ($acao === "atualizar") {
            $quantidade = filter_input(INPUT_POST, "quantidade", FILTER_VALIDATE_INT);

            if ($quantidade === false || $quantidade === null || $quantidade < 1) {
                unset($_SESSION["carrinho"][$id]);
            } else {
                $_SESSION["carrinho"][$id] = min($quantidade, 99);
            }
        }
    }

    header("Location: carrinho.php");
    exit;
}

// Calcula o total usando valores inteiros em centavos.
$total = 0;
$total_itens = 0;
foreach ($_SESSION["carrinho"] as $id => $quantidade) {
    if (isset($produtos[$id])) {
        $total += $produtos[$id]["preco"] * $quantidade;
        $total_itens += $quantidade;
    }
}

function moeda($valor) {
    return "R$ " . number_format($valor / 100, 2, ",", ".");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu carrinho - Sabor e Braza</title>
    <link rel="stylesheet" href="CSS_BRAZZA/style.css">
    <style>
        .carrinho-conteudo {
            width: 90%;
            max-width: 1000px;
            margin: 35px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ccc;
        }
        .carrinho-conteudo h2 {
            color: #b22222;
            text-align: center;
            margin-bottom: 25px;
        }
        .item-carrinho {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            padding: 18px 0;
            border-bottom: 1px solid #ddd;
        }
        .item-carrinho h3 { margin-bottom: 7px; }
        .item-carrinho form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .item-carrinho input {
            width: 75px;
            padding: 8px;
            border: 1px solid #bbb;
            border-radius: 5px;
        }
        .carrinho-conteudo button {
            margin: 3px;
        }
        .botao-remover { background: #555; }
        .botao-remover:hover { background: #333; }
        .total-carrinho {
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            margin-top: 25px;
        }
        .acoes-carrinho {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }
        .acoes-carrinho a {
            display: inline-block;
            background: #b22222;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 5px;
            font-weight: bold;
        }
        .aviso-carrinho {
            margin-top: 18px;
            color: #555;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .carrinho-conteudo { padding: 18px; }
            .total-carrinho { text-align: left; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Sabor e Braza</h1>
        <p>TUDO PARA VOCÊ!!!</p>
    </header>

    <nav>
        <a href="brazza.html">INÍCIO</a>
        <a href="entregas.html">ENTREGAS</a>
        <a href="sobre.html">SOBRE NÓS</a>
        <a href="cadastrar.html">CADASTRA-SE</a>
        <a href="login.html">LOGIN</a>
        <a href="carrinho.php">CARRINHO (<?php echo $total_itens; ?>)</a>
    </nav>

    <main class="carrinho-conteudo">
        <h2>MEU CARRINHO</h2>

        <?php if (empty($_SESSION["carrinho"])): ?>
            <p>Seu carrinho está vazio.</p>
            <div class="acoes-carrinho">
                <a href="brazza.html">CONTINUAR COMPRANDO</a>
            </div>
        <?php else: ?>
            <?php foreach ($_SESSION["carrinho"] as $id => $quantidade): ?>
                <?php if (isset($produtos[$id])): ?>
                    <div class="item-carrinho">
                        <div>
                            <h3><?php echo htmlspecialchars($produtos[$id]["nome"], ENT_QUOTES, "UTF-8"); ?></h3>
                            <p>Valor unitário: <?php echo moeda($produtos[$id]["preco"]); ?></p>
                            <strong>Subtotal: <?php echo moeda($produtos[$id]["preco"] * $quantidade); ?></strong>
                        </div>
                        <form method="post" action="carrinho.php">
                            <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
                            <label for="quantidade-<?php echo (int) $id; ?>">Quantidade:</label>
                            <input type="number" id="quantidade-<?php echo (int) $id; ?>" name="quantidade"
                                min="1" max="99" value="<?php echo (int) $quantidade; ?>">
                            <button type="submit" name="acao" value="atualizar">ATUALIZAR</button>
                            <button type="submit" name="acao" value="remover" class="botao-remover">REMOVER</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <p class="total-carrinho">Total: <?php echo moeda($total); ?></p>
            <div class="acoes-carrinho">
                <a href="brazza.html">CONTINUAR COMPRANDO</a>
            </div>
            <p class="aviso-carrinho">Nesta primeira etapa, os itens ficam guardados na sessão do navegador. A finalização e o registro do pedido no banco serão integrados nas próximas etapas.</p>
        <?php endif; ?>
    </main>

    <footer>
        <h2>CONTATO:</h2><br>
        (55) 31 99999-9999<br>
        fogo.braza@gmail.com<br>
        © Sabor e Braza 2026
    </footer>
</body>
</html>
