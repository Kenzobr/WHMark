function POST_LOGIN() {

    const FORM = $("#form_login");
    const CARREGAR = $("#carregar");

    $.ajax({
        url: API("api/login.php"),
        type: "POST",
        data: FORM.serialize(),
        dataType: "json",

        beforeSend: function () {

            CARREGAR
                .empty()
                .append("<p>Logando sua conta...</p>");

            FORM.find("button[type='submit']")
                .prop("disabled", true);
        },

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                localStorage.setItem(
                    "usuario",
                    JSON.stringify(RESPOSTA.dados)
                );

                CARREGAR.text(RESPOSTA.mensagem);

                if(RESPOSTA.dados.tipo == "usuario"){
                setTimeout(function () {
                    window.location.href = "index_user.html";
                }, 1000);}else{
                    setTimeout(function () {
                    window.location.href = "index_vendedor.html";
                }, 1000);
                }

            } else {

                CARREGAR.text(
                    RESPOSTA.mensagem ||
                    "Não foi possível realizar o login."
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

$("#form_login").on("submit", function (EVENTO) {

    EVENTO.preventDefault();
    
    POST_LOGIN();

});
