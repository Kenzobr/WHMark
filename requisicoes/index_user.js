
function CARREGAR_USER() {
    const USUARIO = GET_USUARIO();
    const id_user = USUARIO.id;
    const tipo = USUARIO.tipo;

    $.ajax({
        url: API("api/index_user.php"),
        type: "POST",
        data: { 
            id_user: id_user,
            tipo: tipo  
         },
        dataType: "json",

        success: function (RESPOSTA) {

            if (RESPOSTA.status !== "sucesso") {

                alert(RESPOSTA.mensagem);

                return;
            }
            const DADOS = RESPOSTA.dados;

            $("#lista_lojas").empty();
            $("#nome_usuario").text(DADOS.nome || "");
            
            $("#lista-lojas").html(DADOS.html || "");

            
        },

        error: function (XHR) {

            alert(
                "Erro ao conectar com o servidor."
            );

        }
    });
}
