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

// Recebe os dados do formulário.
$nome = trim($_POST["nome"] ?? "");
// Recebe os dados enviados pelo formulário.
$usuario = trim($_POST["usuario"] ?? "");
// Recebe e prepara informações enviadas pelo formulário.
$email = trim($_POST["email"] ?? "");
$cpf_cnpj = trim($_POST["cpf_cnpj"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");
$endereco = trim($_POST["endereco"] ?? "");
// Recebe os dados enviados pelo formulário.
$senha = $_POST["senha"] ?? "";
$confirmar_senha = $_POST["confirmar_senha"] ?? "";
// Guarda a opção de concordância informada no cadastro.
$termo_concordancia = $_POST["termo_concordancia"] ?? "";

// Se a pessoa discordar, o cadastro é cancelado.
// O redirecionamento abre o formulário novamente, sem preencher os campos.
if ($termo_concordancia !== "sim") {
// Redireciona o visitante para outra página.
    header("Location: cadastrar.html?erro=termos");
// Encerra a execução para que o código não continue.
    exit;
}

// Confere se todos os campos foram preenchidos.
if (
// Recebe e prepara informações enviadas pelo formulário.
    $nome === "" || $usuario === "" || $email === "" ||
    $cpf_cnpj === "" || $telefone === "" || $endereco === "" ||
// Recebe os dados enviados pelo formulário.
    $senha === "" || $confirmar_senha === ""
) {
// Redireciona o visitante para outra página.
    header("Location: cadastrar.html?erro=campos");
// Encerra a execução para que o código não continue.
    exit;
}

// Confere se as duas senhas são iguais.
if ($senha !== $confirmar_senha) {
// Redireciona o visitante para outra página.
    header("Location: cadastrar.html?erro=senhas");
// Encerra a execução para que o código não continue.
    exit;
}

// Verifica se o nome de usuário já está cadastrado.
$sql_verificar = "SELECT usuario FROM fogo WHERE usuario = ?";
// Prepara a consulta para executar comandos no banco.
$stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
// Vincula os valores informados aos parâmetros da consulta.
mysqli_stmt_bind_param($stmt_verificar, "s", $usuario);
// Executa a consulta preparada no banco de dados.
mysqli_stmt_execute($stmt_verificar);
// Armazena o resultado retornado pela consulta ao banco.
$resultado = mysqli_stmt_get_result($stmt_verificar);

// Verifica se a condição foi atendida.
if (mysqli_num_rows($resultado) > 0) {
// Libera a consulta preparada.
    mysqli_stmt_close($stmt_verificar);
// Redireciona o visitante para outra página.
    header("Location: cadastrar.html?erro=usuario");
// Encerra a execução para que o código não continue.
    exit;
}
// Libera a consulta preparada.
mysqli_stmt_close($stmt_verificar);

// Protege a senha antes de gravá-la no banco.
$senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

// Não é necessário guardar confirmar_senha no banco.
$sql = "INSERT INTO fogo
        (nome, usuario, email, cpf_cnpj, telefone, endereco, senha, termo_concordancia)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

// Prepara a consulta para executar os comandos com segurança.
$stmt = mysqli_prepare($conexao, $sql);

// Verifica se a condição foi atendida.
if (!$stmt) {
// Interrompe a execução e apresenta uma mensagem de erro.
    die("Erro ao preparar o cadastro: " . mysqli_error($conexao));
}

// Vincula os valores informados aos parâmetros da consulta.
mysqli_stmt_bind_param(
// Prepara a consulta para executar comandos no banco.
    $stmt,
    "ssssssss",
// Recebe e prepara informações enviadas pelo formulário.
    $nome,
// Recebe e prepara informações enviadas pelo formulário.
    $usuario,
// Recebe e prepara informações enviadas pelo formulário.
    $email,
    $cpf_cnpj,
    $telefone,
    $endereco,
// Recebe e prepara informações enviadas pelo formulário.
    $senha_criptografada,
// Guarda a opção de concordância informada no cadastro.
    $termo_concordancia
);

// Verifica se a condição foi atendida.
if (mysqli_stmt_execute($stmt)) {
// Libera a consulta preparada.
    mysqli_stmt_close($stmt);
// Fecha a conexão com o banco de dados.
    mysqli_close($conexao);

    // Depois do cadastro, a pessoa precisa fazer login.
    header("Location: login.html?cadastro=sucesso");
// Encerra a execução para que o código não continue.
    exit;
} else {
// Libera a consulta preparada.
    mysqli_stmt_close($stmt);
// Fecha a conexão com o banco de dados.
    mysqli_close($conexao);
// Interrompe a execução e apresenta uma mensagem de erro.
    die("Não foi possível concluir o cadastro. Verifique a tabela do banco de dados.");
}
?>