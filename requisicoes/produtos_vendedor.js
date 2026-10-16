const USUARIO_ATUAL = GET_USUARIO();

/* ===================== LISTA ===================== */

function CARREGAR_LISTA() {

    CARREGAR_PRODUTOS(
        USUARIO_ATUAL,
        function (LISTA) {
            RENDER_PRODUTOS($("#produtos"), LISTA, false);
        },
        function (MENSAGEM) {
            $("#produtos").text(
                MENSAGEM || "Não foi possível carregar os produtos."
            );
        }
    );
}

/* ===================== CATEGORIAS (select2) ===================== */

function CARREGAR_CATEGORIAS() {

    const SELECT = $("#id_categoria");

    $.ajax({
        url: API("api/categorias_produtos.php"),
        type: "POST",
        dataType: "json",

        success: function (RESPOSTA) {

            if (RESPOSTA.status !== "sucesso") {
                return;
            }

            SELECT.empty().append($("<option>"));

            RESPOSTA.dados.forEach(function (CATEGORIA) {

                SELECT.append(
                    $("<option>")
                        .val(CATEGORIA.id)
                        .text(CATEGORIA.nome)
                );

            });

            // dropdownParent é obrigatório para o select2 funcionar dentro do modal
            SELECT.select2({
                dropdownParent: $("#modalProduto"),
                placeholder: "Selecione a categoria",
                width: "100%"
            });
        },

        error: function () {

            $("#carregar").text("Não foi possível carregar as categorias.");

        }
    });
}

/* ===================== MODAL ===================== */

function ABRIR_MODAL() {

    $("#modalProduto").addClass("aberto");
    $("body").addClass("modal-aberto");

}

function FECHAR_MODAL() {

    $("#modalProduto").removeClass("aberto");
    $("body").removeClass("modal-aberto");

    $("#form_produto")[0].reset();

    $("#id_vendedor").val(USUARIO_ATUAL.id);
    $("#id_categoria").val(null).trigger("change");
    $("#imagemPreview").hide();
    $("#carregar").empty();

}

$("#btnAdicionar").on("click", ABRIR_MODAL);
$("#btnFecharModal").on("click", FECHAR_MODAL);

// Clique fora da caixa fecha o modal
$("#modalProduto").on("click", function (EVENTO) {

    if (EVENTO.target === this) {
        FECHAR_MODAL();
    }

});

$(document).on("keydown", function (EVENTO) {

    if (EVENTO.key === "Escape") {
        FECHAR_MODAL();
    }

});

/* ===================== IMAGEM ===================== */

$("#imagem").on("change", function () {

    const ARQUIVO = this.files[0];

    if (!ARQUIVO) {

        $("#imagemPreview").hide();

        return;
    }

    if (ARQUIVO.size > 2 * 1024 * 1024) {

        $("#carregar").text("A imagem deve ter no máximo 2 MB.");

        this.value = "";

        $("#imagemPreview").hide();

        return;
    }

    $("#carregar").empty();

    const LEITOR = new FileReader();

    LEITOR.onload = function (E) {

        $("#imagemPreview").attr("src", E.target.result).show();

    };

    LEITOR.readAsDataURL(ARQUIVO);

});

/* ===================== ENVIO ===================== */

function POST_PRODUTO() {

    const FORM = $("#form_produto");
    const CARREGAR = $("#carregar");

    $.ajax({
        url: API("api/criar_produto.php"),
        type: "POST",
        data: new FormData(FORM[0]),
        processData: false,
        contentType: false,
        dataType: "json",

        beforeSend: function () {

            CARREGAR
                .empty()
                .append("<p>Salvando...</p>");

            FORM.find("button[type='submit']")
                .prop("disabled", true);
        },

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                FECHAR_MODAL();

                CARREGAR_LISTA();

            } else {

                CARREGAR.text(
                    RESPOSTA.mensagem ||
                    "Não foi possível salvar o produto."
                );

            }
        },

        error: function (XHR) {

            const RESPOSTA = XHR.responseJSON;

            CARREGAR.text(
                RESPOSTA?.mensagem ||
                "Erro ao conectar com o servidor."
            );

        },

        complete: function () {

            FORM.find("button[type='submit']")
                .prop("disabled", false);

        }
    });
}

$("#form_produto").on("submit", function (EVENTO) {

    EVENTO.preventDefault();

    // O select2 esconde o <select>, então a categoria é conferida aqui
    if (!$("#id_categoria").val()) {

        $("#carregar").text("Selecione uma categoria.");

        return;
    }

    POST_PRODUTO();

});

/* ===================== INÍCIO ===================== */

function INICIAR_PRODUTOS() {

    // Precisa estar logado como vendedor
    if (!USUARIO_ATUAL || USUARIO_ATUAL.tipo !== "vendedor") {

        window.location.href = "login.html";

        return;
    }

    $("#id_vendedor").val(USUARIO_ATUAL.id);

    CARREGAR_LOJA(
        USUARIO_ATUAL,
        function (LOJA) {

            // Sem loja não tem o que listar: volta para a home criar a loja
            if (!LOJA) {

                window.location.href = "index_vendedor.html";

                return;
            }

            CARREGAR_LISTA();
            CARREGAR_CATEGORIAS();
        },
        function (MENSAGEM) {

            $("#produtos").text(
                MENSAGEM || "Não foi possível carregar a sua loja."
            );

        }
    );
}

$(document).ready(INICIAR_PRODUTOS);
