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

/* ===================== LOJA DO VENDEDOR ===================== */
$sqlLoja = "SELECT id FROM lojas WHERE id_vendedor = $idVendedor LIMIT 1";
$resLoja = mysqli_query($conn, $sqlLoja);

if (!$resLoja) {
    resposta(500, 'erro', 'Erro ao consultar a loja.');
}

if (mysqli_num_rows($resLoja) === 0) {
    resposta(404, 'erro', 'Você ainda não tem uma loja.');
}

$loja   = mysqli_fetch_assoc($resLoja);
$idLoja = (int) $loja['id'];

/* ===================== PRODUTOS ===================== */
$sql = "SELECT p.id, p.id_categoria, p.nome, p.valor, p.descricao,
               p.especificacao, p.imagem, c.nome AS categoria
        FROM produtos p
        LEFT JOIN categorias_produtos c ON c.id = p.id_categoria
        WHERE p.id_loja = $idLoja
        ORDER BY p.id DESC";

$res = mysqli_query($conn, $sql);

if (!$res) {
    resposta(500, 'erro', 'Erro ao consultar os produtos.');
}

$produtos = [];

while ($linha = mysqli_fetch_assoc($res)) {
    $produtos[] = $linha;
}

mysqli_close($conn);

resposta(200, 'sucesso', 'Produtos carregados.', $produtos);
