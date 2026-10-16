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

/* ===================== DADOS DO FORM ===================== */
$idVendedor   = (int) ($_POST['id_vendedor']  ?? 0);
$idCategoria  = (int) ($_POST['id_categoria'] ?? 0);
$nomeBruto    = trim($_POST['nome']           ?? '');
$valorBruto   = str_replace(',', '.', trim($_POST['valor'] ?? ''));
$descBruta    = trim($_POST['descricao']      ?? '');
$especBruta   = trim($_POST['especificacao']  ?? '');

/* ===================== VALIDAÇÕES ===================== */
if ($idVendedor <= 0) {
    resposta(400, 'erro', 'Vendedor inválido.');
}

if ($idCategoria <= 0) {
    resposta(400, 'erro', 'Selecione uma categoria.');
}

if ($nomeBruto === '' || $descBruta === '') {
    resposta(400, 'erro', 'Preencha nome e descrição.');
}

if (mb_strlen($nomeBruto) > 100) {
    resposta(400, 'erro', 'O nome deve ter no máximo 100 caracteres.');
}

if (mb_strlen($descBruta) > 500) {
    resposta(400, 'erro', 'A descrição deve ter no máximo 500 caracteres.');
}

if (mb_strlen($especBruta) > 1000) {
    resposta(400, 'erro', 'A especificação deve ter no máximo 1000 caracteres.');
}

if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $valorBruto) || (float) $valorBruto <= 0) {
    resposta(400, 'erro', 'Informe um valor válido.');
}

$valorNumero = number_format((float) $valorBruto, 2, '.', '');

if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] === UPLOAD_ERR_NO_FILE) {
    resposta(400, 'erro', 'Envie a imagem do produto.');
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

/* ===================== CATEGORIA EXISTE? ===================== */
$sqlCategoria = "SELECT id FROM categorias_produtos WHERE id = $idCategoria LIMIT 1";
$resCategoria = mysqli_query($conn, $sqlCategoria);

if (!$resCategoria) {
    resposta(500, 'erro', 'Erro ao consultar a categoria.');
}

if (mysqli_num_rows($resCategoria) === 0) {
    resposta(400, 'erro', 'Categoria inválida.');
}

/* ===================== UPLOAD DA IMAGEM ===================== */
if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    resposta(400, 'erro', 'Erro ao enviar a imagem.');
}

if ($_FILES['imagem']['size'] > 2 * 1024 * 1024) {
    resposta(400, 'erro', 'A imagem deve ter no máximo 2 MB.');
}

$info = getimagesize($_FILES['imagem']['tmp_name']);

$tiposPermitidos = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_WEBP => 'webp'
];

if (!$info || !isset($tiposPermitidos[$info[2]])) {
    resposta(400, 'erro', 'A imagem deve ser JPG, PNG ou WEBP.');
}

$pasta = __DIR__ . '/../uploads/produtos/';

if (!is_dir($pasta)) {
    mkdir($pasta, 0755, true);
}

$nomeArquivo = 'produto_' . $idLoja . '_' . uniqid() . '.' . $tiposPermitidos[$info[2]];

if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $nomeArquivo)) {
    resposta(500, 'erro', 'Não foi possível salvar a imagem.');
}

$imagemCaminho = 'uploads/produtos/' . $nomeArquivo;

/* ===================== LIMPANDO DADOS ===================== */
$nome          = limpar($conn, $nomeBruto);
$descricao     = limpar($conn, $descBruta);
$especificacao = limpar($conn, $especBruta);
$imagem        = limpar($conn, $imagemCaminho);

/* ===================== INSERT ===================== */
$sql = "INSERT INTO produtos
            (id_loja, id_categoria, nome, valor, descricao, especificacao, imagem)
        VALUES
            ($idLoja, $idCategoria, '$nome', '$valorNumero', '$descricao', '$especificacao', '$imagem')";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {

    // Se o banco falhou, não deixa a imagem órfã na pasta
    @unlink($pasta . $nomeArquivo);

    resposta(500, 'erro', 'Não foi possível salvar o produto.');
}

$idProduto = mysqli_insert_id($conn);

mysqli_close($conn);

resposta(201, 'sucesso', 'Produto criado com sucesso!', [
    'id'     => $idProduto,
    'nome'   => $nomeBruto,
    'imagem' => $imagemCaminho
]);
