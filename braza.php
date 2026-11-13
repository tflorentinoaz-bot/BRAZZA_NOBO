<?php

$servidor = "localhost";
$usuario = "root";
$senha_bd = "";
$banco = "fogoebraza";

$fogoebraza = mysqli_connect ($servidor, $usuario, $senha_bd, $banco);


if (!$fogoebraza) {
    die("Erro de conexão: ". mysqli_connect_error());
}

$nome = $_POST['nome'];
$usuario = $_POST['usuario'];
$email = $_POST['email'];
$cfp_cnpj = $_POST['cpf_cnpj'];
$telefone = $_POST['telefone'];
$endereco = $_POST['endereco'];
$senha = $_POST['senha'];
$confirmar_senha = $_POST['confirmar_senha'];
$termo_concordancia = $_POST['termo_concordancia'];

if('discordo' == "nao"){
    echo "<h1> Termo de concordância é obrigatório!</h3>";
    echo "<a href='part2.html'>Voltar</a>";
}
else {
$sql = "INSERT INTO fogo (nome, usuario, email, cpf_cnpj, telefone, endereco, senha, confirmar_senha, termo_concordancia)
VALUES ('$nome', '$usuario', '$email', '$cfp_cnpj', '$telefone', '$endereco', '$senha', '$confirmar_senha', '$termo_concordancia')";
 if(mysqli_query($fogoebraza, $sql)) {
    echo "<h1> Usuario cadastrado com sucesso!</h3>";
    echo "<a href='brazza.html'>Voltar</a>";
} 
}

mysqli_close($fogoebraza);


