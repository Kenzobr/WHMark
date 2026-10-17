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
$id_user = (int) ($_POST['id_user'] ?? 0);
$tipo = (string) ($_POST['tipo'] ?? '');
$api = (string) ($_POST['API'] ?? '');

if ($id_user <= 0) {
    resposta(400, 'erro', 'User inválido.');
}

$sql_user = "SELECT id, nome FROM usuarios WHERE id = $id_user LIMIT 1";

$res_user = mysqli_query($conn, $sql_user);

if (!$res_user) {
    error_log("Erro SQL usuário: " . mysqli_error($conn));
    resposta(500, 'erro', 'Erro ao consultar o usuário.');
}

if (mysqli_num_rows($res_user) === 0) {
    resposta(404, 'erro', 'Usuário não encontrado.');
}

$res_usuario = mysqli_fetch_assoc($res_user);
$nome_user = $res_usuario['nome'];
  
if (!$res_user) {
    resposta(500, 'erro', 'Erro ao consultar o User.');
}

if (mysqli_num_rows($res_user) === 0) {
    resposta(404, 'erro', 'user não encontrado.');
}
  
/* ===================== BUSCA A LOJA ===================== */
$sql_lojas = "SELECT * FROM lojas";

$query_lojas = mysqli_query($conn, $sql_lojas);

if (!$query_lojas) {
    error_log("Erro SQL lojas: " . mysqli_error($conn));
    resposta(500, 'erro', 'Erro ao consultar as lojas.');
}

$html_lojas = "";

while ($loja = mysqli_fetch_assoc($query_lojas)) {
    $nome_loja = $loja['nome'];
    $especialidade = $loja['especialidade'];
    $categoria = $loja['categoria'];
    $foto_loja = $loja['logo'];
    $logo_api = $api.$foto_loja;
    // $horario = $loja['horario'];

    $html_lojas .= "

            <article class='business-card' onclick='window.top.location='loja_dados.html?id=urban123''>

                    <img
                        src='{$logo_api}'
                        alt='LOJA'
                        class='business-image'
                    >

                    <div class='business-info'>

                        <div class='business-top'>

                            <h3>{$nome_loja}</h3>

                            <span class='category'>
                                {$categoria}
                            </span>

                        </div>

                        <p class='business-hours'>
                            🕐 Aberto · 09:00 - 18:00
                        </p>

                        <p class='business-price'>
                            💰 R$ 50 - R$ 200
                        </p>

                        <div class='business-rating'>
                            ⭐ 0.0
                        </div>

                    </div>

            </article>
    ";


    
}

resposta(
    200,
    'sucesso',
    [
            'html' => $html_lojas,
            'nome' => $nome_user
    ]
);



