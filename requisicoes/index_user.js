
function CARREGAR_USER() {
    const USUARIO = GET_USUARIO();
    const id_user = USUARIO.id;
    const tipo = USUARIO.tipo;

    $.ajax({
        url: API("api/index_user.php"),
        type: "POST",
        data: { 
            id_user: id_user,
            tipo: tipo,
            API: API()
         },
        dataType: "json",

        success: function (RESPOSTA) {

            if (RESPOSTA.status !== "sucesso") {

                AO_ERRO(RESPOSTA.mensagem);

                return;
            }
            const DADOS = REPOSTA.dados;

            $("#lista_lojas").empty();
            $("#nome_usuario").text(DADOS.nome || "");
            
            $("#lista-lojas").html(DADOS.html || "");

            
        },

        error: function (XHR) {

            AO_ERRO(
                XHR.responseJSON?.mensagem ||
                "Erro ao conectar com o servidor."
            );

        }
    });
}
