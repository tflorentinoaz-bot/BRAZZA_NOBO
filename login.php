<?php
session_start();

$servidor = "localhost";
$usuario_bd = "root";
$senha_bd = "";
$banco = "fogoebraza";

$conexao = mysqli_connect($servidor, $usuario_bd, $senha_bd, $banco);

if (!$conexao) {
    die("Erro de conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, "utf8mb4");

$usuario = trim($_POST["usuario"] ?? "");
$senha = $_POST["senha"] ?? "";

if ($usuario === "" || $senha === "") {
    header("Location: login.html?erro=campos");
    exit;
}

// Procura o usuário cadastrado.
$sql = "SELECT usuario, senha FROM fogo WHERE usuario = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "s", $usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$dados = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);
mysqli_close($conexao);

// Confere a senha usando o hash salvo no cadastro.
if ($dados && password_verify($senha, $dados["senha"])) {
    session_regenerate_id(true);
    $_SESSION["usuario"] = $dados["usuario"];

    // Após o login correto, abre a página inicial da loja.
    header("Location: brazza.html");
    exit;
}

// Usuário ou senha incorretos.
header("Location: login.html?erro=login");
exit;
?>