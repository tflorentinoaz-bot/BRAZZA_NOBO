<?php
session_start();

$pedido = $_SESSION["ultimo_pedido"] ?? null;
unset($_SESSION["ultimo_pedido"]);

if (!$pedido) {
    header("Location: brazza.html");
    exit;
}

$forma = ($pedido["forma"] === "retirada") ? "Retirada na loja" : "Entrega na residência";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado - Sabor e Braza</title>
    <link rel="stylesheet" href="CSS_BRAZZA/style.css">
    <style>
        .confirmacao {
            width: 90%; max-width: 700px; margin: 45px auto; padding: 30px;
            background: #fff; border-radius: 10px; text-align: center;
            box-shadow: 0 2px 8px #ccc;
        }
        .confirmacao h2 { color: #b22222; }
        .confirmacao a {
            display: inline-block; margin-top: 20px; padding: 11px 18px;
            background: #b22222; color: #fff; text-decoration: none;
            border-radius: 5px; font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Sabor e Braza</h1>
        <p>TUDO PARA VOCÊ!!!</p>
    </header>
    <main class="confirmacao">
        <h2>PEDIDO REGISTRADO COM SUCESSO!</h2>
        <p>Número do pedido: <strong><?php echo (int) $pedido["id"]; ?></strong></p>
        <p>Forma de recebimento: <strong><?php echo htmlspecialchars($forma, ENT_QUOTES, "UTF-8"); ?></strong></p>
        <p>Total: <strong>R$ <?php echo number_format((float) $pedido["total"], 2, ",", "."); ?></strong></p>
        <p>Seu pedido foi salvo no banco de dados.</p>
        <a href="brazza.html">VOLTAR À LOJA</a>
    </main>
    <footer>
        <h2>CONTATO:</h2><br>
        (55) 31 99999-9999<br>
        fogo.braza@gmail.com<br>
        © Sabor e Braza 2026
    </footer>
</body>
</html>
