<?php
session_start();

// Somente clientes autenticados podem finalizar uma compra.
if (empty($_SESSION["id_cliente"])) {
    header("Location: login.html?erro=login");
    exit;
}

// Produtos e preços oficiais do catálogo.
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

if (empty($_SESSION["carrinho"]) || !is_array($_SESSION["carrinho"])) {
    header("Location: carrinho.php");
    exit;
}

// Calcula o total novamente no servidor.
$total_centavos = 0;
$itens_validos = [];
foreach ($_SESSION["carrinho"] as $id => $quantidade) {
    $id = (int) $id;
    $quantidade = (int) $quantidade;

    if (isset($produtos[$id]) && $quantidade >= 1 && $quantidade <= 99) {
        $itens_validos[$id] = $quantidade;
        $total_centavos += $produtos[$id]["preco"] * $quantidade;
    }
}

if (empty($itens_validos)) {
    header("Location: carrinho.php");
    exit;
}

function moedaPedido($valor) {
    return "R$ " . number_format($valor / 100, 2, ",", ".");
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $forma = $_POST["forma_recebimento"] ?? "";
    $endereco = trim($_POST["endereco_entrega"] ?? "");

    if (!in_array($forma, ["retirada", "entrega"], true)) {
        $erro = "Selecione uma forma de recebimento.";
    } elseif ($forma === "entrega" && $endereco === "") {
        $erro = "Informe o endereço para entrega.";
    } elseif (strlen($endereco) > 200) {
        $erro = "O endereço deve ter no máximo 200 caracteres.";
    } else {
        $servidor = "localhost";
        $usuario_bd = "root";
        $senha_bd = "";
        $banco = "fogoebraza";

        $conexao = mysqli_connect($servidor, $usuario_bd, $senha_bd, $banco);

        if (!$conexao) {
            $erro = "Não foi possível conectar ao banco de dados.";
        } else {
            mysqli_set_charset($conexao, "utf8mb4");
            $id_cliente = (int) $_SESSION["id_cliente"];
            $valor_total = $total_centavos / 100;
            $status = "Recebido";
            $endereco_salvo = ($forma === "entrega") ? $endereco : null;

            mysqli_begin_transaction($conexao);

            $sql_pedido = "INSERT INTO pedidos
                (id_cliente, forma_recebimento, endereco_entrega, valor_total, status_pedido)
                VALUES (?, ?, ?, ?, ?)";
            $stmt_pedido = mysqli_prepare($conexao, $sql_pedido);

            if (!$stmt_pedido) {
                mysqli_rollback($conexao);
                $erro = "Não foi possível preparar o registro do pedido.";
            } else {
                mysqli_stmt_bind_param(
                    $stmt_pedido,
                    "issds",
                    $id_cliente,
                    $forma,
                    $endereco_salvo,
                    $valor_total,
                    $status
                );

                if (mysqli_stmt_execute($stmt_pedido)) {
                    $id_pedido = mysqli_insert_id($conexao);
                    mysqli_stmt_close($stmt_pedido);

                    $sql_item = "INSERT INTO itens_pedido
                        (id_pedido, id_produto, nome_produto, preco_unitario, quantidade, subtotal)
                        VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_item = mysqli_prepare($conexao, $sql_item);

                    if ($stmt_item) {
                        $falha_item = false;

                        foreach ($itens_validos as $id_produto => $quantidade) {
                            $nome_produto = $produtos[$id_produto]["nome"];
                            $preco = $produtos[$id_produto]["preco"] / 100;
                            $subtotal = ($produtos[$id_produto]["preco"] * $quantidade) / 100;

                            mysqli_stmt_bind_param(
                                $stmt_item,
                                "iisdid",
                                $id_pedido,
                                $id_produto,
                                $nome_produto,
                                $preco,
                                $quantidade,
                                $subtotal
                            );

                            if (!mysqli_stmt_execute($stmt_item)) {
                                $falha_item = true;
                                break;
                            }
                        }

                        mysqli_stmt_close($stmt_item);

                        if (!$falha_item) {
                            mysqli_commit($conexao);
                            $_SESSION["ultimo_pedido"] = [
                                "id" => $id_pedido,
                                "total" => $valor_total,
                                "forma" => $forma
                            ];
                            unset($_SESSION["carrinho"]);
                            mysqli_close($conexao);
                            header("Location: pedido_confirmado.php");
                            exit;
                        }
                    }

                    mysqli_rollback($conexao);
                    $erro = "Não foi possível salvar os produtos do pedido. Nenhuma compra foi registrada.";
                } else {
                    mysqli_stmt_close($stmt_pedido);
                    mysqli_rollback($conexao);
                    $erro = "Não foi possível registrar o pedido.";
                }
            }

            mysqli_close($conexao);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar compra - Sabor e Braza</title>
    <link rel="stylesheet" href="CSS_BRAZZA/style.css">
    <style>
        .finalizar-conteudo {
            width: 90%;
            max-width: 800px;
            margin: 35px auto;
            padding: 25px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ccc;
        }
        .finalizar-conteudo h2 { color: #b22222; text-align: center; }
        .opcao-recebimento { display: block; margin: 15px 0; }
        .finalizar-conteudo textarea {
            width: 100%; max-width: 100%; min-height: 90px;
            padding: 10px; border: 1px solid #bbb; border-radius: 5px;
            box-sizing: border-box;
        }
        .finalizar-conteudo button, .voltar-carrinho {
            display: inline-block; margin-top: 18px; padding: 11px 18px;
            background: #b22222; color: #fff; border: 0; border-radius: 5px;
            text-decoration: none; font-weight: bold; cursor: pointer;
        }
        .erro-pedido { color: #b22222; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h1>Sabor e Braza</h1>
        <p>TUDO PARA VOCÊ!!!</p>
    </header>
    <nav>
        <a href="brazza.html">INÍCIO</a>
        <a href="carrinho.php">CARRINHO</a>
        <a href="sobre.html">SOBRE NÓS</a>
    </nav>
    <main class="finalizar-conteudo">
        <h2>FINALIZAR COMPRA</h2>
        <p>Total do pedido: <strong><?php echo moedaPedido($total_centavos); ?></strong></p>

        <?php if ($erro !== ""): ?>
            <p class="erro-pedido"><?php echo htmlspecialchars($erro, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>

        <form method="post" action="finalizar_pedido.php">
            <p>Como deseja receber seu pedido?</p>
            <label class="opcao-recebimento">
                <input type="radio" name="forma_recebimento" value="retirada" required>
                Retirar na loja
            </label>
            <label class="opcao-recebimento">
                <input type="radio" name="forma_recebimento" value="entrega" required>
                Entrega na residência
            </label>

            <label for="endereco_entrega">Endereço para entrega (preencha somente se escolher entrega):</label>
            <textarea id="endereco_entrega" name="endereco_entrega" maxlength="200"
                placeholder="Rua, número, bairro, cidade e complemento"></textarea>

            <button type="submit">CONFIRMAR PEDIDO</button>
            <a class="voltar-carrinho" href="carrinho.php">VOLTAR AO CARRINHO</a>
        </form>
    </main>
    <footer>
        <h2>CONTATO:</h2><br>
        (55) 31 99999-9999<br>
        fogo.braza@gmail.com<br>
        © Sabor e Braza 2026
    </footer>
</body>
</html>
