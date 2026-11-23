<?php
// Inicia ou recupera a sessão do visitante.
session_start();

// Define os dados necessários para conectar ao banco de dados.
$servidor = "localhost";
// Define os dados necessários para conectar ao banco de dados.
$usuario_bd = "root";
// Define os dados necessários para conectar ao banco de dados.
$senha_bd = "";
// Define os dados necessários para conectar ao banco de dados.
$banco = "fogoebraza";

// Abre a conexão com o banco de dados.
$conexao = mysqli_connect($servidor, $usuario_bd, $senha_bd, $banco);

// Verifica se a conexão com o banco de dados foi realizada.
if (!$conexao) {
// Interrompe a execução e apresenta uma mensagem de erro.
    die("Erro de conexão: " . mysqli_connect_error());
}

// Configura a conexão para trabalhar corretamente com caracteres especiais.
mysqli_set_charset($conexao, "utf8mb4");

// Recebe os dados enviados pelo formulário.
$usuario = trim($_POST["usuario"] ?? "");
// Recebe os dados enviados pelo formulário.
$senha = $_POST["senha"] ?? "";

// Verifica se a condição foi atendida.
if ($usuario === "" || $senha === "") {
// Redireciona o visitante para outra página.
    header("Location: login.html?erro=campos");
// Encerra a execução para que o código não continue.
    exit;
}

// Procura o usuário cadastrado.
$sql = "SELECT usuario, senha FROM fogo WHERE usuario = ?";
// Prepara a consulta para executar os comandos com segurança.
$stmt = mysqli_prepare($conexao, $sql);
// Vincula os valores informados aos parâmetros da consulta.
mysqli_stmt_bind_param($stmt, "s", $usuario);
// Executa a consulta preparada no banco de dados.
mysqli_stmt_execute($stmt);
// Armazena o resultado retornado pela consulta ao banco.
$resultado = mysqli_stmt_get_result($stmt);
// Armazena o resultado retornado pela consulta ao banco.
$dados = mysqli_fetch_assoc($resultado);

// Libera a consulta preparada.
mysqli_stmt_close($stmt);
// Fecha a conexão com o banco de dados.
mysqli_close($conexao);

// Confere a senha usando o hash salvo no cadastro.
if ($dados && password_verify($senha, $dados["senha"])) {
    session_regenerate_id(true);
// Guarda ou limpa informações da sessão do visitante.
    $_SESSION["usuario"] = $dados["usuario"];

    // Após o login correto, abre a página inicial da loja.
    header("Location: brazza.html");
// Encerra a execução para que o código não continue.
    exit;
}

// Usuário ou senha incorretos.
header("Location: brazza.html?erro=login");
// Encerra a execução para que o código não continue.
exit;
?>