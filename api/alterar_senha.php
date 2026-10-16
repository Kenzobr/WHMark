<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

require_once("conexao.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(405, 'erro', 'Método não permitido.');
}


$idVendedor  = (int) ($_POST['id_vendedor'] ?? 0);
$senhaAtual  = $_POST['senha_atual'] ?? '';
$novaSenha   = $_POST['nova_senha']  ?? '';
$confirmacao = $_POST['confirmacao'] ?? '';

/* ===================== VALIDAÇÕES ===================== */
if ($idVendedor <= 0) {
    resposta(400, 'erro', 'Vendedor inválido.');
}

if ($senhaAtual === '' || $novaSenha === '') {
    resposta(400, 'erro', 'Preencha todos os campos.');
}

if (strlen($novaSenha) < 6) {
    resposta(400, 'erro', 'A nova senha deve ter pelo menos 6 caracteres.');
}

if ($novaSenha !== $confirmacao) {
    resposta(400, 'erro', 'As senhas não conferem.');
}

/* ===================== CONFERE A SENHA ATUAL ===================== */
$sql = "SELECT senha FROM vendedor WHERE id = $idVendedor LIMIT 1";
$res = mysqli_query($conn, $sql);

if (!$res) {
    resposta(500, 'erro', 'Erro ao consultar o vendedor.');
}

if (mysqli_num_rows($res) === 0) {
    resposta(404, 'erro', 'Vendedor não encontrado.');
}

$vendedor = mysqli_fetch_assoc($res);

if (!password_verify($senhaAtual, $vendedor['senha'])) {
    resposta(401, 'erro', 'A senha atual está incorreta.');
}

/* ===================== ATUALIZA ===================== */
$senhaHash = limpar($conn, password_hash($novaSenha, PASSWORD_DEFAULT));

$sqlUpdate = "UPDATE vendedor SET senha = '$senhaHash' WHERE id = $idVendedor";

if (!mysqli_query($conn, $sqlUpdate)) {
    resposta(500, 'erro', 'Não foi possível alterar a senha.');
}

mysqli_close($conn);

resposta(200, 'sucesso', 'Senha alterada com sucesso!');
