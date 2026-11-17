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

// Recebe os dados do formulário.
$nome = trim($_POST["nome"] ?? "");
$usuario = trim($_POST["usuario"] ?? "");
$email = trim($_POST["email"] ?? "");
$cpf_cnpj = trim($_POST["cpf_cnpj"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");
$endereco = trim($_POST["endereco"] ?? "");
$senha = $_POST["senha"] ?? "";
$confirmar_senha = $_POST["confirmar_senha"] ?? "";
$termo_concordancia = $_POST["termo_concordancia"] ?? "";

// Se a pessoa discordar, o cadastro é cancelado.
// O redirecionamento abre o formulário novamente, sem preencher os campos.
if ($termo_concordancia !== "sim") {
    header("Location: cadastrar.html?erro=termos");
    exit;
}

// Confere se todos os campos foram preenchidos.
if (
    $nome === "" || $usuario === "" || $email === "" ||
    $cpf_cnpj === "" || $telefone === "" || $endereco === "" ||
    $senha === "" || $confirmar_senha === ""
) {
    header("Location: cadastrar.html?erro=campos");
    exit;
}

// Confere se as duas senhas são iguais.
if ($senha !== $confirmar_senha) {
    header("Location: cadastrar.html?erro=senhas");
    exit;
}

// Verifica se o nome de usuário já está cadastrado.
$sql_verificar = "SELECT usuario FROM fogo WHERE usuario = ?";
$stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
mysqli_stmt_bind_param($stmt_verificar, "s", $usuario);
mysqli_stmt_execute($stmt_verificar);
$resultado = mysqli_stmt_get_result($stmt_verificar);

if (mysqli_num_rows($resultado) > 0) {
    mysqli_stmt_close($stmt_verificar);
    header("Location: cadastrar.html?erro=usuario");
    exit;
}
mysqli_stmt_close($stmt_verificar);

// Protege a senha antes de gravá-la no banco.
$senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

// Não é necessário guardar confirmar_senha no banco.
$sql = "INSERT INTO fogo
        (nome, usuario, email, cpf_cnpj, telefone, endereco, senha, termo_concordancia)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    die("Erro ao preparar o cadastro: " . mysqli_error($conexao));
}

mysqli_stmt_bind_param(
    $stmt,
    "ssssssss",
    $nome,
    $usuario,
    $email,
    $cpf_cnpj,
    $telefone,
    $endereco,
    $senha_criptografada,
    $termo_concordancia
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);

    // Depois do cadastro, a pessoa precisa fazer login.
    header("Location: login.html?cadastro=sucesso");
    exit;
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    die("Não foi possível concluir o cadastro. Verifique a tabela do banco de dados.");
}
?>