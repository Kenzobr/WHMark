let PASSO = 1;
const TOTAL_PASSOS = 3;

/* ===================== LOGIN ===================== */

const USUARIO = JSON.parse(localStorage.getItem("usuario") || "null");

if (!USUARIO || USUARIO.tipo !== "vendedor") {

    window.location.href = "login.html";

} else {

    $("#id_vendedor").val(USUARIO.id);

}

/* ===================== PASSOS ===================== */

function MOSTRAR_PASSO(N) {

    PASSO = N;

    $(".step")
        .removeClass("active")
        .filter("[data-step='" + N + "']")
        .addClass("active");

    $(".step-dot").each(function () {

        const P = Number($(this).data("step"));

        $(this)
            .toggleClass("active", P === N)
            .toggleClass("done", P < N);

    });

    $("#btnVoltar").toggle(N > 1);
    $("#btnProximo").toggle(N < TOTAL_PASSOS);
    $("#btnCriar").toggle(N === TOTAL_PASSOS);

    $("#carregar").empty();

    window.scrollTo(0, 0);
}

function VALIDAR_PASSO(N) {

    const CAMPOS = $(".step[data-step='" + N + "']")
        .find("input, select, textarea")
        .toArray();

    for (const CAMPO of CAMPOS) {

        if (!CAMPO.checkValidity()) {

            CAMPO.reportValidity();

            return false;
        }
    }

    return true;
}

$("#btnProximo").on("click", function () {

    if (VALIDAR_PASSO(PASSO)) {

        MOSTRAR_PASSO(PASSO + 1);

    }

});

$("#btnVoltar").on("click", function () {

    MOSTRAR_PASSO(PASSO - 1);

});

/* ===================== LOGO ===================== */

$("#logo").on("change", function () {

    const ARQUIVO = this.files[0];

    if (!ARQUIVO) {

        $("#logoPreview, #pvLogo").hide();

        return;
    }

    if (ARQUIVO.size > 2 * 1024 * 1024) {

        $("#carregar").text("A logo deve ter no máximo 2 MB.");

        this.value = "";

        $("#logoPreview, #pvLogo").hide();

        return;
    }

    const LEITOR = new FileReader();

    LEITOR.onload = function (E) {

        $("#logoPreview, #pvLogo")
            .attr("src", E.target.result)
            .show();

    };

    LEITOR.readAsDataURL(ARQUIVO);

});

$("#estado").on("input", function () {

    this.value = this.value.toUpperCase().replace(/[^A-Z]/g, "");

});

/* ===================== PRÉVIA ===================== */

function ATUALIZAR_PREVIA() {

    $("#pvHeader").css({
        "background-color": $("#cor_header").val(),
        "color": $("#cor_texto_header").val()
    });

    $("#pvBody").css({
        "background-color": $("#cor_fundo").val(),
        "color": $("#cor_texto").val()
    });

    $("#pvBotao").css("background-color", $("#cor_destaque").val());

    $("#pvNome").text($("#nome").val().trim() || "Minha loja");

}

$(".color-item input, #nome").on("input", ATUALIZAR_PREVIA);

/* ===================== ENVIO ===================== */

function POST_LOJA() {

    const FORM = $("#form_loja");
    const CARREGAR = $("#carregar");

    $.ajax({
        url: API("api/criar_loja.php"),
        type: "POST",
        data: new FormData(FORM[0]),
        processData: false,
        contentType: false,
        dataType: "json",

        beforeSend: function () {

            CARREGAR
                .empty()
                .append("<p>Criando sua loja...</p>");

            FORM.find("button[type='submit']")
                .prop("disabled", true);
        },

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                CARREGAR.text(RESPOSTA.mensagem);

                setTimeout(function () {
                    window.location.href = "index_vendedor.html";
                }, 1000);

            } else {

                CARREGAR.text(
                    RESPOSTA.mensagem ||
                    "Não foi possível criar a loja."
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

$("#form_loja").on("submit", function (EVENTO) {

    EVENTO.preventDefault();

    // Enter nos passos 1 e 2 avança em vez de enviar
    if (PASSO < TOTAL_PASSOS) {

        if (VALIDAR_PASSO(PASSO)) {
            MOSTRAR_PASSO(PASSO + 1);
        }

        return;
    }

    if (!VALIDAR_PASSO(1) || !VALIDAR_PASSO(2)) {

        $("#carregar").text("Preencha todos os campos obrigatórios.");

        return;
    }

    POST_LOJA();

});

MOSTRAR_PASSO(1);
ATUALIZAR_PREVIA();
