<?php

require("key.php");
//////conexaooo
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'whmark';

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    resposta(500, 'erro', 'Erro ao conectar com o banco de dados.');
}

mysqli_set_charset($conn, 'utf8mb4');

/* ===================== FUNÇÕES ===================== */
function resposta($codigo, $status, $mensagem, $dados = null)
{
    http_response_code($codigo);

    echo json_encode([
        'status'   => $status,
        'mensagem' => $mensagem,
        'dados'    => $dados
    ]);

    exit;
}

function limpar($conn, $valor)
{
    return mysqli_real_escape_string($conn, trim($valor));
}

function criptografar($texto)
{
    $chave = hash('sha256', CHAVE_CRIPTOGRAFIA, true);
    $iv = random_bytes(12);
    $tag = '';

    $textoCriptografado = openssl_encrypt(
        $texto,
        'aes-256-gcm',
        $chave,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($textoCriptografado === false) {
        throw new RuntimeException('Erro ao criptografar.');
    }

    return base64_encode($iv . $tag . $textoCriptografado);
}

function descriptografar($texto)
{
    $dados = base64_decode($texto, true);

    if ($dados === false || strlen($dados) < 28) {
        throw new InvalidArgumentException('Texto criptografado inválido.');
    }

    $chave = hash('sha256', CHAVE_CRIPTOGRAFIA, true);

    $iv = substr($dados, 0, 12);
    $tag = substr($dados, 12, 16);
    $conteudo = substr($dados, 28);

    $resultado = openssl_decrypt(
        $conteudo,
        'aes-256-gcm',
        $chave,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($resultado === false) {
        throw new RuntimeException(
            'Não foi possível descriptografar. Verifique a chave ou os dados.'
        );
    }

    return $resultado;
}
?>