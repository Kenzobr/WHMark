<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    resposta(405, 'erro', 'Método não permitido.');
}

require_once("conexao.php");

$id_user = (int) ($_GET['id_user'] ?? 0);
$id_loja = (string) ($_GET['id_loja'] ?? '');


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

// try {
//     $id_loja_des = descriptografar($id_loja);
// } catch (InvalidArgumentException $e) {
//     resposta(400, 'erro', 'ID da loja inválido ou malformado.');
// } catch (RuntimeException $e) {
//     resposta(400, 'erro', 'Não foi possível validar o ID da loja.');
// }
  
$sql_lojas = "SELECT * FROM lojas where id='$id_loja'";

$query_lojas = mysqli_query($conn, $sql_lojas);

if (!$query_lojas) {
    error_log("Erro SQL lojas: " . mysqli_error($conn));
    resposta(500, 'erro', 'Erro ao consultar as lojas.');
}

$html_lojas = "";

$html_header = "";

$link = $acs['link'];


while ($loja = mysqli_fetch_assoc($query_lojas)) {

    $html_produtos = "";
    $html_categorias = "";
    
    $id_loja = $loja['id'];
    $nome = $loja['nome'];
    $especialidade = $loja['especialidade'];
    $categoria = $loja['categoria'];
    $descricao_loja = $loja['descricao'];
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

        $link_produtos = $link.$produto['imagem'];
        $produto_nome = $produto['nome'];
        $categoria = $produto['categoria_nome'];
        $descricao_produto = $produto['descricao'];
        $valor = number_format($produto['valor'], 2, ',', '.');
        $valor = 'R$ ' . $valor;



        $html_produtos .= "
        
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
    $categoria_nome = limpar($conn, $cat['categoria_nome']);

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
            <div id='header'>
                <header class='store-header' style='background: {$cor_header}'>

                    <div class='store-header-content'>

                        <h1 style='color:{$cor_texto_header}'>{$nome}</h1>

                        <p style='color:{$cor_texto_header}'>
                           {$descricao_loja}
                        </p>

                    </div>

                </header>
            </div>

            <div id='main'>
                <main class='store-container' style='background: {$cor_fundo}'>


                    <!-- =========================
                        CATEGORIAS
                    ========================== -->

                    <section class='categories-section' id='categorias'>

                        <div class='section-title'>

                            <h2>Categorias</h2>

                        </div>


                        <div class='categories-list'>

                                {$html_categorias}

                        </div>

                    </section>


            

                    <section class='products-section' id='produtos'>

                        <div class='section-title'>

                            <h2>Produtos</h2>

                            <p>
                                Confira alguns dos nossos produtos.
                            </p>

                        </div>


                        <div class='products-list'>


                            {$html_produtos}


                        

                        </div>

                    </section>

                </main>
            </div>


            <footer class='bottom-menu' style='background: {$cor_header}'>

                <a href='index_vendedor.html' class='menu-item'>

                    <span class='menu-icon' style='color: {$cor_texto_header}'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'>
                            <path d='M3 11.5 12 4l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z' />
                        </svg>
                    </span>

                    <span style='color: {$cor_texto_header}'>Home</span>

                </a>


                <a href='produtos_vendedor.html' class='menu-item'>

                    <span class='menu-icon' style='color: {$cor_texto_header}'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'>
                            <path d='M6 7h12l1 13H5z' />
                            <path d='M9 7a3 3 0 0 1 6 0' />
                        </svg>
                    </span>

                    <span style='color: {$cor_texto_header}'>Produtos/Serviços</span>

                </a>


                <a href='pagamentos.html' class='menu-item'>

                    <span class='menu-icon' style='color: {$cor_texto_header}'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'>
                            <circle cx='12' cy='12' r='9' />
                            <path d='M14.5 9.5c-.5-1-1.5-1.5-2.5-1.5-1.5 0-2.5.8-2.5 2s1 1.7 2.5 2 2.5.8 2.5 2-1 2-2.5 2c-1.1 0-2-.5-2.5-1.5M12 6.5V8m0 8v1.5' />
                        </svg>
                    </span>

                    <span style='color: {$cor_texto_header}'>Financeiro</span>

                </a>

                <a href='perfil_vendedor.html' class='menu-item'>

                    <span class='menu-icon' style='color: {$cor_texto_header}'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'>
                            <path d='M4 7h10M18 7h2M4 17h2M10 17h10' />
                            <circle cx='16' cy='7' r='2' />
                            <circle cx='8' cy='17' r='2' />
                        </svg>
                    </span>

                    <span style='background: {$cor_texto_header}'>Configuração</span>

                </a>


            </footer>


          
    ";


    
}

resposta(
    200,
    "sucesso",
    "DEU BOA",
    [
            "html" => $html_lojas
    ]
);



