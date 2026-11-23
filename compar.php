<?php
// Inicia ou recupera a sessão do visitante.
session_start();

// Se já estiver conectado, vai direto para a página de entregas.
if (isset($_SESSION["usuario"])) {
// Redireciona o visitante para outra página.
    header("Location: entregas.html");
// Encerra a execução para que o código não continue.
    exit;
}

// Se ainda não estiver conectado, precisa entrar na conta.
header("Location: login.html");
// Encerra a execução para que o código não continue.
exit;
?>