<?php
// Inicia ou recupera a sessão do visitante.
session_start();
// Guarda ou remove informações da sessão do usuário.
$_SESSION = array();
session_destroy();

// Redireciona o visitante para outra página.
header("Location: brazza.html");
// Encerra a execução para impedir que o código continue.
exit;
?>