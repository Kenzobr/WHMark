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
$id_loja = (string) ($_POST['id_loja'] ?? '');


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

$id_loja_des = descriptografar($id_loja);

  
/* ===================== BUSCA A LOJA ===================== */
$sql_lojas = "SELECT * FROM lojas where id='$id_loja_des'";

$query_lojas = mysqli_query($conn, $sql_lojas);

if (!$query_lojas) {
    error_log("Erro SQL lojas: " . mysqli_error($conn));
    resposta(500, 'erro', 'Erro ao consultar as lojas.');
}

$html_lojas = "";
$html_produtos = "";
$html_header = "";
$html_categorias = "";
$link = $acs['link'];


while ($loja = mysqli_fetch_assoc($query_lojas)) {
    $id_loja = $loja['id'];
    $nome = $loja['nome'];
    $especialidade = $loja['especialidade'];
    $categoria = $loja['categoria'];
    $descricao = $loja['descricao'];
    $logo = $loja['logo'];
    $cor_header = $loja['cor_header'];
    $cor_texto_header = $loja['cor_texto_header'];
    $cor_fundo = $loja['cor_fundo'];
    $cor_texto = $loja['cor_texto'];
    $cor_destaque = $loja['cor_destaque'];




    //////////////// produtos

    $sql_produtos = "SELECT 
                    p.*,
                    c.nome as categoria_nome

                    from produtos p
                    left join categorias_produtos c on c.id = p.id_categoria

                    where p.id_loja = '$id_loja'
                        
                        ";

    $sql_query_produtos = mysqli_query($conn, $sql_produtos);

    if (!$sql_query_produtos) {
        error_log("Erro SQL produtos: " . mysqli_error($conn));
        resposta(500, 'erro', 'Erro ao consultar os produtos.');
    }
    
    while($produto = mysqli_fetch_assoc($sql_query_produtos)){

        $link_produtos = $link.$produto['imagem'];\
        $produto_nome = $produto['nome'];
        $categoria = $produto['categoria_nome'];
        $descricao_produto = $produto['descricao'];
        $valor = number_format($produto['valor'], 2, ',', '.');
        $valor = 'R$ ' . $valor;



        $html_produto .= "
        
                    <article class='product-card'>

                        <img
                            src='{$link_produtos}'
                            alt='Produto 1'
                            class='product-image'
                        >

                        <div class='product-info'>

                            <span class='product-category' style='color:{$cor_destaque}'>
                                {$categoria}
                            </span>

                            <h3>{$produto_nome}</h3>

                            <p class='product-description' style='color:{$cor_texto}'>
                                {$descricao_produto}
                            </p>

                            <div class='product-bottom'>

                                <strong style='color:{$cor_destaque}'>
                                    {$valor}
                                </strong>

                                <button style='background:{$cor_destaque}'>
                                    Ver produto
                                </button>

                            </div>

                        </div>

                    </article>

        ";

    }

    /////////////CATEGORIAS

    $sql_categorias = "
        SELECT
            c.id AS categoria_id,
            c.nome AS categoria_nome,
            p.imagem AS categoria_imagem
        FROM categorias_produtos c
        INNER JOIN (
            SELECT
                id_categoria,
                MIN(id) AS produto_id
            FROM produtos
            WHERE id_loja = '$id_loja'
            AND id_categoria IS NOT NULL
            GROUP BY id_categoria
        ) AS escolhidos
            ON escolhidos.id_categoria = c.id
        INNER JOIN produtos p
            ON p.id = escolhidos.produto_id
        ORDER BY c.nome ASC
    ";


    $query_categorias = mysqli_query($conn, $sql_categorias);

    if (!$query_categorias) {
        error_log("Erro SQL categorias: " . mysqli_error($conn));
        resposta(500, 'erro', 'Erro ao consultar as categorias.');
    }
        

    while ($cat = mysqli_fetch_assoc($query_categorias)) {

    $categoria_id = $cat['categoria_id'];
    $categoria_nome = limpar($conn, $cat['nome']);

    $categoria_imagem = $link . $cat['categoria_imagem'];


    $html_categorias .= "
    
                    <article class='category-card'>

                        <img
                            src='{$categoria_imagem}'
                            alt='categoria1'
                        >

                        <div class='category-info'>

                            <h3>{$categoria_nome}</h3>

                            <span style='color: {$cor_texto}'>Ver produtos →</span>

                        </div>

                    </article>


    ";


    }







    /////////////////LOJAAAAA
    $html_lojas .= "

          
    ";


    
}

resposta(
    200,
    'sucesso',
    'DEU BOA',
    [
            'html' => $html_lojas,
            'nome' => $nome_user
    ]
);



