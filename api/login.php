<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

require_once("conexao.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(405, 'erro', 'Método não permitido.');
}


$emailBruto = $_POST['email'] ?? '';
$senhaBruta = $_POST['senha'] ?? '';


if (trim($emailBruto) === '' || $senhaBruta === '') {
    resposta(400, 'erro', 'Preencha e-mail e senha.');
}

if (!filter_var(trim($emailBruto), FILTER_VALIDATE_EMAIL)) {
    resposta(400, 'erro', 'E-mail inválido.');
}

$email = limpar($conn, strtolower($emailBruto));

$tipo      = '';
$registro  = null;

$sqlUsuario = "SELECT id, nome, email, telefone, senha
               FROM usuarios
               WHERE email = '$email'
               LIMIT 1";

$resUsuario = mysqli_query($conn, $sqlUsuario);

if (!$resUsuario) {
    resposta(500, 'erro', 'Erro ao consultar o login.');
}

if (mysqli_num_rows($resUsuario) > 0) {

    $tipo     = 'usuario';
    $registro = mysqli_fetch_assoc($resUsuario);

} else {

    $sqlVendedor = "SELECT id, nome, email, telefone, documento, senha
                    FROM vendedor
                    WHERE email = '$email'
                    LIMIT 1";

    $resVendedor = mysqli_query($conn, $sqlVendedor);

    if (!$resVendedor) {
        resposta(500, 'erro', 'Erro ao consultar o login.');
    }

    if (mysqli_num_rows($resVendedor) > 0) {

        $tipo     = 'vendedor';
        $registro = mysqli_fetch_assoc($resVendedor);

    }
}

if (!$registro || !password_verify($senhaBruta, $registro['senha'])) {
    resposta(401, 'erro', 'E-mail ou senha incorretos.');
}

mysqli_close($conn);

$dadosUsuario = [
    'id'       => (int) $registro['id'],
    'tipo'     => $tipo,
    'nome'     => $registro['nome'],
    'email'    => $registro['email'],
    'telefone' => $registro['telefone']
];

if ($tipo === 'vendedor') {
    $dadosUsuario['documento'] = $registro['documento'];
}

resposta(200, 'sucesso', 'Login realizado com sucesso!', $dadosUsuario);
