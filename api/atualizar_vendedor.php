<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

/* O conexao.php precisa ter a função criptografar() */
require_once("conexao.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(405, 'erro', 'Método não permitido.');
}

/* ===================== DADOS DO FORM ===================== */
$idVendedor     = (int) ($_POST['id_vendedor'] ?? 0);
$nomeBruto      = trim($_POST['nome']       ?? '');
$emailBruto     = trim($_POST['email']      ?? '');
$telefoneBruto  = trim($_POST['telefone']   ?? '');
$documentoBruto = trim($_POST['documento']  ?? '');
$tipoChave      = trim($_POST['tipo_chave'] ?? '');
$chaveBruta     = trim($_POST['chave_pix']  ?? '');

/* ===================== VALIDAÇÕES ===================== */
if ($idVendedor <= 0) {
    resposta(400, 'erro', 'Vendedor inválido.');
}

if ($nomeBruto === '' || mb_strlen($nomeBruto) > 100) {
    resposta(400, 'erro', 'Informe um nome válido (até 100 caracteres).');
}

if (!filter_var($emailBruto, FILTER_VALIDATE_EMAIL)) {
    resposta(400, 'erro', 'E-mail inválido.');
}

$telefoneNumeros  = preg_replace('/\D/', '', $telefoneBruto);
$documentoNumeros = preg_replace('/\D/', '', $documentoBruto);

if (strlen($telefoneNumeros) < 10 || strlen($telefoneNumeros) > 11) {
    resposta(400, 'erro', 'Telefone inválido.');
}

if (strlen($documentoNumeros) !== 11 && strlen($documentoNumeros) !== 14) {
    resposta(400, 'erro', 'Informe um CPF ou CNPJ válido.');
}

/* ----- Chave PIX (opcional) ----- */
$tiposValidos = ['', 'cpf', 'cnpj', 'telefone', 'email', 'aleatoria'];

if (!in_array($tipoChave, $tiposValidos, true)) {
    resposta(400, 'erro', 'Tipo de chave PIX inválido.');
}

$chaveFinal = '';

if ($tipoChave !== '') {

    if ($chaveBruta === '') {
        resposta(400, 'erro', 'Informe a chave PIX ou escolha "Sem chave PIX".');
    }

    switch ($tipoChave) {

        case 'cpf':
            $chaveFinal = preg_replace('/\D/', '', $chaveBruta);

            if (strlen($chaveFinal) !== 11) {
                resposta(400, 'erro', 'CPF da chave PIX inválido.');
            }
            break;

        case 'cnpj':
            $chaveFinal = preg_replace('/\D/', '', $chaveBruta);

            if (strlen($chaveFinal) !== 14) {
                resposta(400, 'erro', 'CNPJ da chave PIX inválido.');
            }
            break;

        case 'telefone':
            $chaveFinal = preg_replace('/\D/', '', $chaveBruta);

            if (strlen($chaveFinal) < 10 || strlen($chaveFinal) > 11) {
                resposta(400, 'erro', 'Telefone da chave PIX inválido.');
            }
            break;

        case 'email':
            if (!filter_var($chaveBruta, FILTER_VALIDATE_EMAIL)) {
                resposta(400, 'erro', 'E-mail da chave PIX inválido.');
            }

            $chaveFinal = strtolower($chaveBruta);
            break;

        case 'aleatoria':
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $chaveBruta)) {
                resposta(400, 'erro', 'A chave aleatória deve ter o formato xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.');
            }

            $chaveFinal = strtolower($chaveBruta);
            break;
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

/* ===================== E-MAIL JÁ É DE OUTRA CONTA? ===================== */
$email = limpar($conn, strtolower($emailBruto));

$sqlUsuario    = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
$sqlOutroVend  = "SELECT id FROM vendedor WHERE email = '$email' AND id <> $idVendedor LIMIT 1";

$resUsuario   = mysqli_query($conn, $sqlUsuario);
$resOutroVend = mysqli_query($conn, $sqlOutroVend);

if (!$resUsuario || !$resOutroVend) {
    resposta(500, 'erro', 'Erro ao verificar o e-mail.');
}

if (mysqli_num_rows($resUsuario) > 0 || mysqli_num_rows($resOutroVend) > 0) {
    resposta(409, 'erro', 'Este e-mail já está em uso.');
}

/* ===================== CRIPTOGRAFIA + LIMPEZA ===================== */
$nome       = limpar($conn, $nomeBruto);
$telefone   = limpar($conn, $telefoneNumeros);
$tipoSql    = limpar($conn, $tipoChave);
$documento  = limpar($conn, criptografar($documentoNumeros));
$chavePix   = $chaveFinal === '' ? '' : limpar($conn, criptografar($chaveFinal));

/* ===================== UPDATE ===================== */
$sql = "UPDATE vendedor
        SET nome       = '$nome',
            email      = '$email',
            telefone   = '$telefone',
            documento  = '$documento',
            tipo_chave = '$tipoSql',
            chave_pix  = '$chavePix'
        WHERE id = $idVendedor";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    resposta(500, 'erro', 'Não foi possível atualizar os dados.');
}

mysqli_close($conn);

/* Sem documento nem chave PIX na resposta: o front não precisa guardá-los */
resposta(200, 'sucesso', 'Dados atualizados com sucesso!', [
    'id'       => $idVendedor,
    'tipo'     => 'vendedor',
    'nome'     => $nomeBruto,
    'email'    => strtolower($emailBruto),
    'telefone' => $telefoneNumeros
]);
