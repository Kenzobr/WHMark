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

/* ===================== DADOS ===================== */
$idVendedor = (int) ($_POST['id_vendedor'] ?? 0);

if ($idVendedor <= 0) {
    resposta(400, 'erro', 'Vendedor inválido.');
}

/* ===================== VENDEDOR EXISTE? ===================== */
$sqlVendedor = "SELECT id FROM vendedor WHERE id = $idVendedor LIMIT 1";
$resVendedor = mysqli_query($conn, $sqlVendedor);

if (!$resVendedor) {
    resposta(500, 'erro', 'Erro ao consultar o vendedor.');
}

if (mysqli_num_rows($resVendedor) === 0) {
    resposta(404, 'erro', 'Vendedor não encontrado.');
}

/* ===================== BUSCA A LOJA ===================== */
$sqlLoja = "SELECT id, id_vendedor, nome, especialidade, categoria, descricao,
                   logo, cidade, estado, bairro,
                   cor_header, cor_texto_header, cor_fundo, cor_texto, cor_destaque
            FROM lojas
            WHERE id_vendedor = $idVendedor
            LIMIT 1";

$resLoja = mysqli_query($conn, $sqlLoja);

if (!$resLoja) {
    resposta(500, 'erro', 'Erro ao consultar a loja.');
}

if (mysqli_num_rows($resLoja) === 0) {
    mysqli_close($conn);
    resposta(200, 'sucesso', 'Este vendedor ainda não tem loja.', null);
}

$loja = mysqli_fetch_assoc($resLoja);

mysqli_close($conn);

resposta(200, 'sucesso', 'Loja encontrada.', $loja);
