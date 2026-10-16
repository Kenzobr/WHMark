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
$idVendedor    = (int) ($_POST['id_vendedor'] ?? 0);
$nomeBruto     = trim($_POST['nome']          ?? '');
$especBruta    = trim($_POST['especialidade'] ?? '');
$categoria     = trim($_POST['categoria']     ?? '');
$descBruta     = trim($_POST['descricao']     ?? '');
$cidadeBruta   = trim($_POST['cidade']        ?? '');
$estadoBruto   = strtoupper(trim($_POST['estado'] ?? ''));
$bairroBruto   = trim($_POST['bairro']        ?? '');

$corHeader      = trim($_POST['cor_header']       ?? '');
$corTextoHeader = trim($_POST['cor_texto_header'] ?? '');
$corFundo       = trim($_POST['cor_fundo']        ?? '');
$corTexto       = trim($_POST['cor_texto']        ?? '');
$corDestaque    = trim($_POST['cor_destaque']     ?? '');

/* ===================== VALIDAÇÕES ===================== */
if ($idVendedor <= 0) {
    resposta(400, 'erro', 'Vendedor inválido.');
}

if ($nomeBruto === '' || $especBruta === '' || $cidadeBruta === '') {
    resposta(400, 'erro', 'Preencha todos os campos obrigatórios.');
}

if (mb_strlen($nomeBruto) > 100 || mb_strlen($especBruta) > 100) {
    resposta(400, 'erro', 'Nome ou especialidade muito longos.');
}

if (mb_strlen($descBruta) > 300) {
    resposta(400, 'erro', 'A descrição deve ter no máximo 300 caracteres.');
}

$categoriasValidas = ['roupas', 'comidas', 'servicos', 'outros'];

if (!in_array($categoria, $categoriasValidas, true)) {
    resposta(400, 'erro', 'Categoria inválida.');
}

if (!preg_match('/^[A-Z]{2}$/', $estadoBruto)) {
    resposta(400, 'erro', 'UF inválida.');
}

foreach ([$corHeader, $corTextoHeader, $corFundo, $corTexto, $corDestaque] as $cor) {
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
        resposta(400, 'erro', 'Cor inválida.');
    }
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

/* ===================== JÁ TEM LOJA? ===================== */
$sqlLoja = "SELECT id FROM lojas WHERE id_vendedor = $idVendedor LIMIT 1";
$resLoja = mysqli_query($conn, $sqlLoja);

if (!$resLoja) {
    resposta(500, 'erro', 'Erro ao consultar a loja.');
}

if (mysqli_num_rows($resLoja) > 0) {
    resposta(409, 'erro', 'Você já tem uma loja cadastrada.');
}

/* ===================== UPLOAD DA LOGO (opcional) ===================== */
$logoCaminho = '';

if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        resposta(400, 'erro', 'Erro ao enviar a logo.');
    }

    if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
        resposta(400, 'erro', 'A logo deve ter no máximo 2 MB.');
    }

    $info = getimagesize($_FILES['logo']['tmp_name']);

    $tiposPermitidos = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp'
    ];

    if (!$info || !isset($tiposPermitidos[$info[2]])) {
        resposta(400, 'erro', 'A logo deve ser JPG, PNG ou WEBP.');
    }

    $pasta = __DIR__ . '/../uploads/lojas/';

    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }

    $nomeArquivo = 'loja_' . $idVendedor . '_' . uniqid() . '.' . $tiposPermitidos[$info[2]];

    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $pasta . $nomeArquivo)) {
        resposta(500, 'erro', 'Não foi possível salvar a logo.');
    }

    $logoCaminho = 'uploads/lojas/' . $nomeArquivo;
}

/* ===================== LIMPANDO DADOS ===================== */
$nome           = limpar($conn, $nomeBruto);
$especialidade  = limpar($conn, $especBruta);
$categoriaSql   = limpar($conn, $categoria);
$descricao      = limpar($conn, $descBruta);
$logo           = limpar($conn, $logoCaminho);
$cidade         = limpar($conn, $cidadeBruta);
$estado         = limpar($conn, $estadoBruto);
$bairro         = limpar($conn, $bairroBruto);
$corHeaderSql   = limpar($conn, $corHeader);
$corTextoHSql   = limpar($conn, $corTextoHeader);
$corFundoSql    = limpar($conn, $corFundo);
$corTextoSql    = limpar($conn, $corTexto);
$corDestaqueSql = limpar($conn, $corDestaque);

/* ===================== INSERT ===================== */
$sql = "INSERT INTO lojas
            (id_vendedor, nome, especialidade, categoria, descricao, logo,
             cidade, estado, bairro,
             cor_header, cor_texto_header, cor_fundo, cor_texto, cor_destaque)
        VALUES
            ($idVendedor, '$nome', '$especialidade', '$categoriaSql', '$descricao', '$logo',
             '$cidade', '$estado', '$bairro',
             '$corHeaderSql', '$corTextoHSql', '$corFundoSql', '$corTextoSql', '$corDestaqueSql')";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {

    // Se o banco falhou, não deixa a logo órfã na pasta
    if ($logoCaminho !== '') {
        @unlink(__DIR__ . '/../' . $logoCaminho);
    }

    resposta(500, 'erro', 'Não foi possível criar a loja.');
}

$idLoja = mysqli_insert_id($conn);

mysqli_close($conn);

resposta(201, 'sucesso', 'Loja criada com sucesso!', [
    'id'          => $idLoja,
    'id_vendedor' => $idVendedor,
    'nome'        => $nomeBruto,
    'categoria'   => $categoria
]);
