/* =========================================================
   FUNÇÕES COMPARTILHADAS DAS PÁGINAS DO VENDEDOR
   Incluir no <head> (antes do CSS pintar) para o tema ser aplicado
   na hora, usando a loja guardada no localStorage.
========================================================= */
  
const COR_HEX = /^#[0-9a-fA-F]{6}$/;
 
const VARIAVEIS_TEMA = [
    "--loja-header",
    "--loja-texto-header",
    "--loja-fundo",
    "--loja-texto",
    "--loja-destaque",
    "--loja-texto-destaque"
];
  
function GET_USUARIO() {

    try {

        return JSON.parse(localStorage.getItem("usuario") || "null");

    } catch (ERRO) {

        return null;

    }
}

/* Escolhe texto preto ou branco conforme a cor de fundo */
function COR_CONTRASTE(HEX) {

    const R = parseInt(HEX.substring(1, 3), 16);
    const G = parseInt(HEX.substring(3, 5), 16);
    const B = parseInt(HEX.substring(5, 7), 16);

    return ((R * 299 + G * 587 + B * 114) / 1000) > 150
        ? "#111111"
        : "#ffffff";
}

function APLICAR_TEMA(L) {

    const RAIZ = document.documentElement;

    const CORES = {
        "--loja-header": L.cor_header,
        "--loja-texto-header": L.cor_texto_header,
        "--loja-fundo": L.cor_fundo,
        "--loja-texto": L.cor_texto,
        "--loja-destaque": L.cor_destaque
    };

    for (const VARIAVEL in CORES) {

        if (COR_HEX.test(CORES[VARIAVEL])) {

            RAIZ.style.setProperty(VARIAVEL, CORES[VARIAVEL]);

        }
    }

    if (COR_HEX.test(L.cor_destaque)) {

        RAIZ.style.setProperty(
            "--loja-texto-destaque",
            COR_CONTRASTE(L.cor_destaque)
        );

    }

    RAIZ.classList.add("tema-loja");
}

function LIMPAR_TEMA() {

    const RAIZ = document.documentElement;

    VARIAVEIS_TEMA.forEach(function (VARIAVEL) {
        RAIZ.style.removeProperty(VARIAVEL);
    });

    RAIZ.classList.remove("tema-loja");
}

/* Aplica o tema guardado antes de a página aparecer (evita "piscar") */
(function () {

    try {

        const USUARIO = GET_USUARIO();
        const LOJA = JSON.parse(localStorage.getItem("loja") || "null");

        // Só usa a loja guardada se for do vendedor logado
        if (USUARIO && LOJA && Number(LOJA.id_vendedor) === Number(USUARIO.id)) {

            APLICAR_TEMA(LOJA);

        }

    } catch (ERRO) { }

})();

function FORMATAR_PRECO(VALOR) {

    return Number(VALOR).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });
}

/* ===================== REQUISIÇÕES ===================== */

function CARREGAR_LOJA(USUARIO, AO_CARREGAR, AO_ERRO) {

    $.ajax({
        url: API("api/loja_vendedor.php"),
        type: "POST",
        data: { id_vendedor: USUARIO.id },
        dataType: "json",

        success: function (RESPOSTA) {

            if (RESPOSTA.status !== "sucesso") {

                AO_ERRO(RESPOSTA.mensagem);

                return;
            }

            if (RESPOSTA.dados) {

                localStorage.setItem("loja", JSON.stringify(RESPOSTA.dados));

                APLICAR_TEMA(RESPOSTA.dados);

            } else {

                localStorage.removeItem("loja");

                LIMPAR_TEMA();

            }

            AO_CARREGAR(RESPOSTA.dados);
        },

        error: function (XHR) {

            AO_ERRO(
                XHR.responseJSON?.mensagem ||
                "Erro ao conectar com o servidor."
            );

        }
    });
}

function CARREGAR_PRODUTOS(USUARIO, AO_CARREGAR, AO_ERRO) {

    $.ajax({
        url: API("api/listar_produtos.php"),
        type: "POST",
        data: { id_vendedor: USUARIO.id },
        dataType: "json",

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                AO_CARREGAR(RESPOSTA.dados || []);

            } else {

                AO_ERRO(RESPOSTA.mensagem);

            }
        },

        error: function (XHR) {

            AO_ERRO(
                XHR.responseJSON?.mensagem ||
                "Erro ao conectar com o servidor."
            );

        }
    });
}

/* ===================== LISTA DE PRODUTOS ===================== */

function RENDER_PRODUTOS(CONTAINER, LISTA, COM_LINK_ADD) {

    CONTAINER.empty();

    if (!LISTA.length) {

        const VAZIO = $("<div>").addClass("vazio").append(
            $("<p>").text(
                COM_LINK_ADD
                    ? "Você ainda não cadastrou nenhum produto ou serviço."
                    : "Nenhum produto ou serviço ainda. Toque em Adicionar para criar o primeiro."
            )
        );

        if (COM_LINK_ADD) {

            VAZIO.append(
                $("<a>")
                    .addClass("btn-link")
                    .attr("href", "produtos_vendedor.html")
                    .text("Adicionar produto")
            );

        }

        CONTAINER.append(VAZIO);

        return;
    }

    LISTA.forEach(function (P) {

        const DETALHE = $("<div>").addClass("produto-detalhe");

        if (P.descricao) {

            DETALHE.append(
                $("<h4>").text("Descrição"),
                $("<p>").text(P.descricao)
            );

        }

        if (P.especificacao) {

            DETALHE.append(
                $("<h4>").text("Especificações"),
                $("<p>").text(P.especificacao)
            );

        }

        const IMAGEM = P.imagem
            ? $("<img>")
                .addClass("produto-img")
                .attr({ src: API(P.imagem), alt: P.nome })
            : $("<div>").addClass("produto-img");

        const ITEM = $("<article>").addClass("produto-item").append(
            $("<div>").addClass("produto-linha").append(
                IMAGEM,
                $("<div>").addClass("produto-meta").append(
                    $("<strong>").text(P.nome),
                    $("<small>").text(P.categoria || "Sem categoria")
                ),
                $("<span>")
                    .addClass("produto-preco")
                    .text(FORMATAR_PRECO(P.valor))
            ),
            DETALHE
        );

        // Toque no item abre/fecha descrição e especificações
        ITEM.on("click", function () {
            $(this).toggleClass("aberto");
        });

        CONTAINER.append(ITEM);

    });
}
