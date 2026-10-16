const USUARIO_ATUAL = GET_USUARIO();

function MOSTRAR_SEM_LOJA() {

    $("#lojaHeader").hide();
    $("#intro").show();

    $("#loja").empty().append(
        $("<div>").addClass("empty-store").append(
            $("<h2>").text("Você ainda não tem uma loja"),
            $("<p>").text(
                "Crie a sua loja personalizada: escolha nome, categoria, " +
                "logo, localidade e as cores que combinam com o seu negócio."
            ),
            $("<a>")
                .addClass("btn-link")
                .attr("href", "criar_loja.html")
                .text("Criar minha loja")
        )
    );
}

function MOSTRAR_LOJA(L) {

    // Header com logo e nome da loja
    const HEADER = $("#lojaHeader").empty();

    if (L.logo) {

        HEADER.append(
            $("<img>")
                .addClass("store-logo")
                .attr("src", API(L.logo))
                .attr("alt", "Logo da loja")
        );

    }

    HEADER.append(
        $("<div>").append(
            $("<h1>").text(L.nome),
            $("<span>").text(L.especialidade)
        )
    ).show();

    // Informações + lista de produtos
    $("#loja").empty().append(
        $("<div>").addClass("store-info").append(
            $("<span>").addClass("store-tag").text(L.categoria),
            $("<p>").addClass("store-desc").text(L.descricao || "Sem descrição."),
            $("<p>").addClass("store-local").text(
                [L.bairro, L.cidade, L.estado].filter(Boolean).join(" - ")
            )
        ),
        $("<div>").addClass("section-head").append(
            $("<h2>").text("Meus produtos/serviços"),
            $("<a>")
                .attr("href", "produtos_vendedor.html")
                .text("Gerenciar")
        ),
        $("<div>").attr("id", "produtos").addClass("produtos-lista").append(
            $("<p>").addClass("loja-status").text("Carregando produtos...")
        )
    );

    CARREGAR_PRODUTOS(
        USUARIO_ATUAL,
        function (LISTA) {
            RENDER_PRODUTOS($("#produtos"), LISTA, true);
        },
        function (MENSAGEM) {
            $("#produtos").text(
                MENSAGEM || "Não foi possível carregar os produtos."
            );
        }
    );
}

function INICIAR_HOME() {

    // Precisa estar logado como vendedor
    if (!USUARIO_ATUAL || USUARIO_ATUAL.tipo !== "vendedor") {

        window.location.href = "login.html";

        return;
    }

    CARREGAR_LOJA(
        USUARIO_ATUAL,
        function (LOJA) {

            if (LOJA) {

                MOSTRAR_LOJA(LOJA);

            } else {

                MOSTRAR_SEM_LOJA();

            }
        },
        function (MENSAGEM) {

            $("#loja").text(
                MENSAGEM || "Não foi possível carregar a sua loja."
            );

        }
    );
}

$(document).ready(INICIAR_HOME);
