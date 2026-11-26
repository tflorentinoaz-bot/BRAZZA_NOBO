<?php
session_start();

// Somente clientes conectados podem consultar seus pedidos.
if (empty($_SESSION["id_cliente"])) {
    header("Location: login.html?erro=login");
    exit;
}

$servidor = "localhost";
$usuario_bd = "root";
$senha_bd = "";
$banco = "fogoebraza";

$conexao = mysqli_connect($servidor, $usuario_bd, $senha_bd, $banco);

if (!$conexao) {
    die("Não foi possível conectar ao banco de dados.");
}

mysqli_set_charset($conexao, "utf8mb4");
$id_cliente = (int) $_SESSION["id_cliente"];
$id_pedido = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

$pedido = null;
$itens = [];
$pedidos = [];
$erro = "";

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function moedaHistorico($valor) {
    return "R$ " . number_format((float) $valor, 2, ",", ".");
}

if ($id_pedido) {
    // O ID do cliente também é verificado para impedir acesso a pedidos de outras contas.
    $sql = "SELECT id_pedido, data_pedido, forma_recebimento, endereco_entrega,
                   valor_total, status_pedido
            FROM pedidos
            WHERE id_pedido = ? AND id_cliente = ?";
    $stmt = mysqli_prepare($conexao, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $id_pedido, $id_cliente);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $pedido = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
    }

    if ($pedido) {
        $sql_itens = "SELECT nome_produto, preco_unitario, quantidade, subtotal
                      FROM itens_pedido
                      WHERE id_pedido = ?
                      ORDER BY id_item ASC";
        $stmt_itens = mysqli_prepare($conexao, $sql_itens);

        if ($stmt_itens) {
            mysqli_stmt_bind_param($stmt_itens, "i", $id_pedido);
            mysqli_stmt_execute($stmt_itens);
            $resultado_itens = mysqli_stmt_get_result($stmt_itens);

            while ($item = mysqli_fetch_assoc($resultado_itens)) {
                $itens[] = $item;
            }

            mysqli_stmt_close($stmt_itens);
        }
    } else {
        $erro = "Pedido não encontrado ou não pertence à sua conta.";
    }
} else {
    $sql = "SELECT id_pedido, data_pedido, forma_recebimento, valor_total, status_pedido
            FROM pedidos
            WHERE id_cliente = ?
            ORDER BY data_pedido DESC, id_pedido DESC";
    $stmt = mysqli_prepare($conexao, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_cliente);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        while ($linha = mysqli_fetch_assoc($resultado)) {
            $pedidos[] = $linha;
        }

        mysqli_stmt_close($stmt);
    } else {
        $erro = "Não foi possível consultar seus pedidos.";
    }
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus pedidos - Sabor e Braza</title>
    <link rel="stylesheet" href="CSS_BRAZZA/style.css">
    <style>
        .pedidos-conteudo {
            width: 90%;
            max-width: 1000px;
            margin: 35px auto;
            padding: 25px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ccc;
            box-sizing: border-box;
        }
        .pedidos-conteudo h2 {
            color: #b22222;
            text-align: center;
            margin-bottom: 25px;
        }
        .pedido-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 16px;
        }
        .pedido-card h3 { margin-top: 0; color: #333; }
        .pedido-card p { margin: 8px 0; }
        .botao-pedido, .voltar-loja {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 16px;
            background: #b22222;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .botao-pedido:hover, .voltar-loja:hover { background: #8f1b1b; }
        .mensagem-pedidos {
            padding: 14px;
            background: #f5f5f5;
            border-radius: 6px;
        }
        .erro-pedidos { color: #b22222; font-weight: bold; }
        .tabela-itens {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        .tabela-itens th, .tabela-itens td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .tabela-itens th { background: #f2f2f2; }
        @media (max-width: 600px) {
            .pedidos-conteudo { padding: 16px; }
            .tabela-itens { font-size: 13px; }
            .tabela-itens th, .tabela-itens td { padding: 7px 5px; }
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
        <a href="carrinho.php">CARRINHO</a>
        <a href="meus_pedidos.php">MEUS PEDIDOS</a>
        <a href="sobre.html">SOBRE NÓS</a>
    </nav>

    <main class="pedidos-conteudo">
        <?php if ($id_pedido): ?>
            <h2>DETALHES DO PEDIDO</h2>

            <?php if ($erro !== ""): ?>
                <p class="erro-pedidos"><?php echo escapar($erro); ?></p>
            <?php else: ?>
                <div class="pedido-card">
                    <h3>Pedido #<?php echo (int) $pedido["id_pedido"]; ?></h3>
                    <p><strong>Data:</strong> <?php echo date("d/m/Y H:i", strtotime($pedido["data_pedido"])); ?></p>
                    <p><strong>Recebimento:</strong>
                        <?php echo $pedido["forma_recebimento"] === "retirada" ? "Retirada na loja" : "Entrega na residência"; ?>
                    </p>
                    <?php if ($pedido["forma_recebimento"] === "entrega"): ?>
                        <p><strong>Endereço:</strong> <?php echo escapar($pedido["endereco_entrega"]); ?></p>
                    <?php endif; ?>
                    <p><strong>Situação:</strong> <?php echo escapar($pedido["status_pedido"]); ?></p>
                    <p><strong>Total:</strong> <?php echo moedaHistorico($pedido["valor_total"]); ?></p>
                </div>

                <h3>Produtos do pedido</h3>
                <?php if (empty($itens)): ?>
                    <p>Não há produtos registrados neste pedido.</p>
                <?php else: ?>
                    <table class="tabela-itens">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Valor unitário</th>
                                <th>Quantidade</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td><?php echo escapar($item["nome_produto"]); ?></td>
                                    <td><?php echo moedaHistorico($item["preco_unitario"]); ?></td>
                                    <td><?php echo (int) $item["quantidade"]; ?></td>
                                    <td><?php echo moedaHistorico($item["subtotal"]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>

            <a class="voltar-loja" href="meus_pedidos.php">VOLTAR AOS MEUS PEDIDOS</a>
        <?php else: ?>
            <h2>MEUS PEDIDOS</h2>

            <?php if ($erro !== ""): ?>
                <p class="erro-pedidos"><?php echo escapar($erro); ?></p>
            <?php elseif (empty($pedidos)): ?>
                <p class="mensagem-pedidos">Você ainda não realizou nenhum pedido.</p>
                <a class="voltar-loja" href="brazza.html">IR À LOJA</a>
            <?php else: ?>
                <?php foreach ($pedidos as $linha): ?>
                    <div class="pedido-card">
                        <h3>Pedido #<?php echo (int) $linha["id_pedido"]; ?></h3>
                        <p><strong>Data:</strong> <?php echo date("d/m/Y H:i", strtotime($linha["data_pedido"])); ?></p>
                        <p><strong>Recebimento:</strong>
                            <?php echo $linha["forma_recebimento"] === "retirada" ? "Retirada na loja" : "Entrega na residência"; ?>
                        </p>
                        <p><strong>Situação:</strong> <?php echo escapar($linha["status_pedido"]); ?></p>
                        <p><strong>Total:</strong> <?php echo moedaHistorico($linha["valor_total"]); ?></p>
                        <a class="botao-pedido" href="meus_pedidos.php?id=<?php echo (int) $linha["id_pedido"]; ?>">VER DETALHES</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
