<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

require_once("conexao.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(405, 'erro', 'Método não permitido.');
}

/* ===================== DADOS DO FORM ===================== */
$tipoBruto      = $_POST['tipo']         ?? '';
$nomeBruto      = $_POST['nome']         ?? '';
$emailBruto     = $_POST['email']        ?? '';
$telefoneBruto  = $_POST['telefone']     ?? '';
$documentoBruto = $_POST['documento']    ?? '';
$senhaBruta     = $_POST['senha']        ?? '';
$confirmacao    = $_POST['confirmacao']  ?? '';

/* ===================== VALIDAÇÕES ===================== */
if ($tipoBruto !== 'usuario' && $tipoBruto !== 'vendedor') {
    resposta(400, 'erro', 'Tipo de conta inválido.');
}

if (trim($nomeBruto) === '' || trim($emailBruto) === '' || trim($telefoneBruto) === '' || $senhaBruta === '') {
    resposta(400, 'erro', 'Preencha todos os campos obrigatórios.');
}

if (!filter_var(trim($emailBruto), FILTER_VALIDATE_EMAIL)) {
    resposta(400, 'erro', 'E-mail inválido.');
}

if (strlen($senhaBruta) < 6) {
    resposta(400, 'erro', 'A senha deve ter pelo menos 6 caracteres.');
}

if ($senhaBruta !== $confirmacao) {
    resposta(400, 'erro', 'As senhas não conferem.');
}

$telefoneNumeros  = preg_replace('/\D/', '', $telefoneBruto);
$documentoNumeros = preg_replace('/\D/', '', $documentoBruto);

if (strlen($telefoneNumeros) < 10) {
    resposta(400, 'erro', 'Telefone inválido.');
}

if ($tipoBruto === 'vendedor') {
    if (strlen($documentoNumeros) !== 11 && strlen($documentoNumeros) !== 14) {
        resposta(400, 'erro', 'Informe um CPF ou CNPJ válido.');
    }
}

///limpando dadosss
$nome      = limpar($conn, $nomeBruto);
$email     = limpar($conn, strtolower($emailBruto));
$telefone  = limpar($conn, $telefoneNumeros);
$documento = limpar($conn, $documentoNumeros);
$documento = criptografar($documento);
$senhaHash = limpar($conn, password_hash($senhaBruta, PASSWORD_DEFAULT));

////email existente fodao
$sqlUsuario  = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
$sqlVendedor = "SELECT id FROM vendedor WHERE email = '$email' LIMIT 1";

$resUsuario  = mysqli_query($conn, $sqlUsuario);
$resVendedor = mysqli_query($conn, $sqlVendedor);

if (!$resUsuario || !$resVendedor) {
    resposta(500, 'erro', 'Erro ao verificar o e-mail.');
}

if (mysqli_num_rows($resUsuario) > 0 || mysqli_num_rows($resVendedor) > 0) {
    resposta(409, 'erro', 'Este e-mail já está cadastrado.');
}


if ($tipoBruto === 'usuario') {

    $sql = "INSERT INTO usuarios (nome, email, telefone, senha)
            VALUES ('$nome', '$email', '$telefone', '$senhaHash')";

} else {

    $sql = "INSERT INTO vendedor (nome, email, telefone, documento, senha)
            VALUES ('$nome', '$email', '$telefone', '$documento', '$senhaHash')";

}

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    resposta(500, 'erro', 'Não foi possível realizar o cadastro.');
}

$idNovo = mysqli_insert_id($conn);

mysqli_close($conn);

$dadosUsuario = [
    'id'       => $idNovo,
    'tipo'     => $tipoBruto,
    'nome'     => trim($nomeBruto),
    'email'    => strtolower(trim($emailBruto)),
    'telefone' => $telefoneNumeros
];

if ($tipoBruto === 'vendedor') {
    $dadosUsuario['documento'] = $documentoNumeros;
}

resposta(201, 'sucesso', 'Conta criada com sucesso!', $dadosUsuario);
