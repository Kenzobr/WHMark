<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

/* O conexao.php precisa ter as funções criptografar() e descriptografar() */
require_once("conexao.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(405, 'erro', 'Método não permitido.');
}

/* ===================== DADOS ===================== */
$idVendedor = (int) ($_POST['id_vendedor'] ?? 0);

if ($idVendedor <= 0) {
    resposta(400, 'erro', 'Vendedor inválido.');
}

function ler_campo($valor)
{
    if ($valor === null || $valor === '') {
        return '';
    }

    if (preg_match('/^(\d{11}|\d{14})$/', $valor)) {
        return $valor;
    }

    $claro = descriptografar($valor);

    return $claro === false || $claro === null ? '' : $claro;
}

/* ===================== BUSCA O VENDEDOR ===================== */
$sql = "SELECT id, nome, email, telefone, documento, tipo_chave, chave_pix
        FROM vendedor
        WHERE id = $idVendedor
        LIMIT 1";

$res = mysqli_query($conn, $sql);

if (!$res) {
    resposta(500, 'erro', 'Erro ao consultar o vendedor.');
}

if (mysqli_num_rows($res) === 0) {
    resposta(404, 'erro', 'Vendedor não encontrado.');
}

$v = mysqli_fetch_assoc($res);

mysqli_close($conn);

resposta(200, 'sucesso', 'Dados carregados.', [
    'id'         => (int) $v['id'],
    'nome'       => $v['nome'],
    'email'      => $v['email'],
    'telefone'   => $v['telefone'],
    'documento'  => ler_campo($v['documento']),
    'tipo_chave' => $v['tipo_chave'] ?? '',
    'chave_pix'  => ler_campo($v['chave_pix'])
]);
