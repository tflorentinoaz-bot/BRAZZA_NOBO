<?php
session_start();

// Se já estiver conectado, vai direto para a página de entregas.
if (isset($_SESSION["usuario"])) {
    header("Location: entregas.html");
    exit;
}

// Se ainda não estiver conectado, precisa entrar na conta.
header("Location: login.html");
exit;
?>