
function CARREGAR_LOJA() {
    const USUARIO = GET_USUARIO();
    const params = new URLSearchParams(window.location.search);
    const idLoja = params.get('id');
    const id_user = USUARIO.id;


    $.ajax({
        url: API("api/loja_dados.php"),
        type: "POST",
        data: { 
            id_user: id_user,
            id_loja: idLoja
         },
        dataType: "json",

        success: function (RESPOSTA) {

            if (RESPOSTA.status !== "sucesso") {

                alert(RESPOSTA.mensagem);

                return;
            }
            const DADOS = RESPOSTA.dados;

            $("#produtos").empty();
            $("categorias").empty();
            $("header").empty();
            $("main").empty();
            $("footer").empty();

            $("#header").html(DADOS.html_header || "");

            $("#produtos").html(DADOS.html_produtos || "");
            
            $("#categorias").html(DADOS.html_categorias || "");

            $("#main").html(DADOS.html_main || "");

            $("#footer").html(DADOS.html_footer || "");


            
        },

        error: function (XHR) {

            alert(
                "Erro ao conectar com o servidor."
            );

        }
    });
}




function GET_USUARIO() {

    try {

        return JSON.parse(localStorage.getItem("usuario") || "null");

    } catch (ERRO) {

        return null;

    }
}

