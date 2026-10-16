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

/* ===================== BUSCA AS CATEGORIAS ===================== */
$sql = "SELECT id, nome FROM categorias_produtos ORDER BY nome ASC";

$res = mysqli_query($conn, $sql);

if (!$res) {
    resposta(500, 'erro', 'Erro ao consultar as categorias.');
}

$categorias = [];

while ($linha = mysqli_fetch_assoc($res)) {
    $categorias[] = $linha;
}

mysqli_close($conn);

resposta(200, 'sucesso', 'Categorias carregadas.', $categorias);
