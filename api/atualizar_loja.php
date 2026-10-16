<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(405, 'erro', 'Método não permitido.');
}

/* ===================== IDENTIFICAÇÃO ===================== */

$idVendedor = filter_input(INPUT_POST, 'id_vendedor', FILTER_VALIDATE_INT);

if (!$idVendedor || $idVendedor <= 0) {
    resposta(400, 'erro', 'Vendedor inválido.');
}

/* ===================== RECEBER CAMPOS ===================== */

$nome             = trim($_POST['nome'] ?? '');
$especialidade    = trim($_POST['especialidade'] ?? '');
$categoria        = trim($_POST['categoria'] ?? '');
$descricao        = trim($_POST['descricao'] ?? '');
$cidade           = trim($_POST['cidade'] ?? '');
$estado           = trim($_POST['estado'] ?? '');
$bairro           = trim($_POST['bairro'] ?? '');
$cor_header       = trim($_POST['cor_header'] ?? '');
$cor_texto_header = trim($_POST['cor_texto_header'] ?? '');
$cor_fundo        = trim($_POST['cor_fundo'] ?? '');
$cor_texto        = trim($_POST['cor_texto'] ?? '');
$cor_destaque     = trim($_POST['cor_destaque'] ?? '');

if ($nome === '' || mb_strlen($nome) > 100) {
    resposta(400, 'erro', 'Informe um nome válido (até 100 caracteres).');
}

$campos = [
    'especialidade'    => $especialidade,
    'categoria'        => $categoria,
    'descricao'        => $descricao,
    'cidade'           => $cidade,
    'estado'           => $estado,
    'bairro'           => $bairro,
    'cor_header'       => $cor_header,
    'cor_texto_header' => $cor_texto_header,
    'cor_fundo'        => $cor_fundo,
    'cor_texto'        => $cor_texto,
    'cor_destaque'     => $cor_destaque
];

foreach ($campos as $campo => $valor) {
    if ($valor === '') {
        resposta(400, 'erro', "Preencha o campo: $campo.");
    }
}

/* ===================== BUSCAR LOJA ===================== */

// Confirme se a coluna correta é id_vendedor ou id.
$idVendedorSql = (int) $idVendedor;

$sqlLoja = "
    SELECT id, logo
    FROM lojas
    WHERE id_vendedor = $idVendedorSql
    LIMIT 1
";

$resultadoLoja = mysqli_query($conn, $sqlLoja);

if (!$resultadoLoja) {
    resposta(500, 'erro', 'Erro ao consultar a loja.');
}

if (mysqli_num_rows($resultadoLoja) === 0) {
    resposta(404, 'erro', 'Loja não encontrada.');
}

$loja = mysqli_fetch_assoc($resultadoLoja);
$logoAntiga = $loja['logo'] ?? '';

/* ===================== CONFIGURAÇÃO DE UPLOAD ===================== */

// Considera que uploads/lojas fica ao lado deste arquivo PHP.
$pastaUploads = dirname(__DIR__) . '/uploads/lojas/';

$logoNova = null;
$arquivoNovo = null;

$enviouLogo = isset($_FILES['logo'])
    && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE;

if ($enviouLogo) {
    $arquivo = $_FILES['logo'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        resposta(400, 'erro', 'Erro durante o envio da logo.');
    }

    if (!is_uploaded_file($arquivo['tmp_name'])) {
        resposta(400, 'erro', 'Arquivo enviado inválido.');
    }

    if ($arquivo['size'] <= 0 || $arquivo['size'] > 5 * 1024 * 1024) {
        resposta(400, 'erro', 'A logo deve ter até 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($arquivo['tmp_name']);

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($tiposPermitidos[$mime])) {
        resposta(400, 'erro', 'Formato inválido. Envie JPG, PNG ou WEBP.');
    }

    if (@getimagesize($arquivo['tmp_name']) === false) {
        resposta(400, 'erro', 'O arquivo enviado não é uma imagem válida.');
    }

    if (!is_dir($pastaUploads)) {
        if (!mkdir($pastaUploads, 0755, true) && !is_dir($pastaUploads)) {
            resposta(500, 'erro', 'Não foi possível criar a pasta de logos.');
        }
    }

    $nomeArquivo = 'loja_' . $idVendedorSql . '_' .
        bin2hex(random_bytes(8)) . '.' . $tiposPermitidos[$mime];

    $arquivoNovo = $pastaUploads . $nomeArquivo;
    $logoNova = 'uploads/lojas/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $arquivoNovo)) {
        resposta(500, 'erro', 'Não foi possível salvar a nova logo.');
    }
}

/* ===================== ESCAPAR VALORES SQL ===================== */

$nomeSql             = limpar($conn, $nome);
$especialidadeSql    = limpar($conn, $especialidade);
$categoriaSql        = limpar($conn, $categoria);
$descricaoSql        = limpar($conn, $descricao);
$cidadeSql           = limpar($conn, $cidade);
$estadoSql           = limpar($conn, $estado);
$bairroSql           = limpar($conn, $bairro);
$corHeaderSql        = limpar($conn, $cor_header);
$corTextoHeaderSql   = limpar($conn, $cor_texto_header);
$corFundoSql         = limpar($conn, $cor_fundo);
$corTextoSql         = limpar($conn, $cor_texto);
$corDestaqueSql      = limpar($conn, $cor_destaque);

/* ===================== ATUALIZAR BANCO ===================== */

$setLogo = '';

if ($logoNova !== null) {
    $logoSql = mysqli_real_escape_string($conn, $logoNova);
    $setLogo = ", logo = '$logoSql'";
}

$sqlUpdate = "
    UPDATE lojas SET
        nome = '$nomeSql',
        especialidade = '$especialidadeSql',
        categoria = '$categoriaSql',
        descricao = '$descricaoSql',
        cidade = '$cidadeSql',
        estado = '$estadoSql',
        bairro = '$bairroSql',
        cor_header = '$corHeaderSql',
        cor_texto_header = '$corTextoHeaderSql',
        cor_fundo = '$corFundoSql',
        cor_texto = '$corTextoSql',
        cor_destaque = '$corDestaqueSql'
        $setLogo
    WHERE id_vendedor = $idVendedorSql
    LIMIT 1
";

$resultadoUpdate = mysqli_query($conn, $sqlUpdate);

if (!$resultadoUpdate) {
    $erroSql = mysqli_error($conn);
    // A atualização falhou: tenta remover a imagem nova que já foi enviada.
    if ($arquivoNovo !== null && is_file($arquivoNovo)) {
        @unlink($arquivoNovo);
    }

    error_log('Erro ao atualizar loja: ' . $erroSql);

    error_log('Erro ao atualizar loja: ' . mysqli_error($conn));

    resposta(500, 'erro', 'Não foi possível atualizar os dados da loja.');
}

/* ===================== APAGAR LOGO ANTIGA ===================== */

if (
    $logoNova !== null &&
    $logoAntiga !== '' &&
    $logoAntiga !== $logoNova
) {
    /*
     * Só permite excluir arquivos dentro de uploads/lojas.
     * O caminho no banco é relativo à pasta deste backend.
     */
    $prefixoPermitido = 'uploads/lojas/';

    if (strpos($logoAntiga, $prefixoPermitido) === 0) {
        $nomeAntigo = substr($logoAntiga, strlen($prefixoPermitido));

        // Evita caminhos com diretórios ou traversal.
        if (
            $nomeAntigo !== '' &&
            basename($nomeAntigo) === $nomeAntigo
        ) {
            $arquivoAntigo = $pastaUploads . $nomeAntigo;

            if (is_file($arquivoAntigo)) {
                if (!@unlink($arquivoAntigo)) {
                    error_log('Não foi possível apagar a logo antiga: ' . $arquivoAntigo);
                }
            }
        }
    }
}

mysqli_close($conn);

resposta(200, 'sucesso', 'Dados atualizados com sucesso!', [
    'id' => $idVendedor,
    'logo' => $logoNova ?? $logoAntiga,
    ''
]);


?>