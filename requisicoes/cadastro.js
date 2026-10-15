function POST_CADASTRO() {

    const FORM = $("#cadastroForm");
    const CARREGAR = $("#carregar");

    $.ajax({
        url: "api/cadastro.php",
        type: "POST",
        data: FORM.serialize(),
        dataType: "json",

        beforeSend: function () {

            CARREGAR
                .empty()
                .append("<p>Criando sua conta...</p>");

            FORM.find("button[type='submit']")
                .prop("disabled", true);
        },

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                CARREGAR.html(RESPOSTA.dados);

            } else {

                CARREGAR.text(
                    RESPOSTA.mensagem ||
                    "Não foi possível realizar o cadastro."
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

$("#cadastroForm").on("submit", function (EVENTO) {

    EVENTO.preventDefault();

    const TIPO = $("#tipo").val();

    if (!TIPO) {

        $("#carregar").text(
            "Selecione WHUser ou WHVendedor."
        );

        return;
    }

    POST_CADASTRO();

});