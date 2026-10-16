const USUARIO_ATUAL = GET_USUARIO();

/* ===================== AUXILIARES ===================== */

function MENSAGEM(SELETOR, TEXTO, SUCESSO) {

    $(SELETOR)
        .removeClass("ok erro")
        .addClass(TEXTO ? (SUCESSO ? "ok" : "erro") : "")
        .text(TEXTO || "");

}

function MASCARA_TELEFONE(VALOR) {

    VALOR = VALOR.replace(/\D/g, "").substring(0, 11);

    if (VALOR.length <= 10) {

        return VALOR.replace(/^(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");

    }

    return VALOR.replace(/^(\d{2})(\d{5})(\d{0,4})/, "($1) $2-$3");
}

function MASCARA_DOCUMENTO(VALOR, FORCAR) {

    VALOR = VALOR.replace(/\D/g, "").substring(0, 14);

    // CPF
    if (VALOR.length <= 11 && FORCAR !== "cnpj") {

        VALOR = VALOR.replace(/(\d{3})(\d)/, "$1.$2");
        VALOR = VALOR.replace(/(\d{3})(\d)/, "$1.$2");
        VALOR = VALOR.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

        return VALOR;
    }

    // CNPJ
    VALOR = VALOR.replace(/^(\d{2})(\d)/, "$1.$2");
    VALOR = VALOR.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
    VALOR = VALOR.replace(/\.(\d{3})(\d)/, ".$1/$2");
    VALOR = VALOR.replace(/(\d{4})(\d)/, "$1-$2");

    return VALOR;
}

/* ===================== ABAS ===================== */

function MOSTRAR_ABA(NOME) {

    $(".aba-btn")
        .removeClass("ativa")
        .filter("[data-aba='" + NOME + "']")
        .addClass("ativa");

    $(".aba")
        .removeClass("ativa")
        .filter("#aba-" + NOME)
        .addClass("ativa");

    window.scrollTo(0, 0);
}

$(".aba-btn").on("click", function () {

    MOSTRAR_ABA($(this).data("aba"));

});

/* =========================================================
   ABA: LOJA
========================================================= */

function ATUALIZAR_PREVIA() {

    $("#pvHeader").css({
        "background-color": $("#l_cor_header").val(),
        "color": $("#l_cor_texto_header").val()
    });

    $("#pvBody").css({
        "background-color": $("#l_cor_fundo").val(),
        "color": $("#l_cor_texto").val()
    });

    $("#pvBotao").css("background-color", $("#l_cor_destaque").val());

    $("#pvNome").text($("#l_nome").val().trim() || "Minha loja");

}

function PREENCHER_LOJA(L) {

    $("#l_nome").val(L.nome);
    $("#l_especialidade").val(L.especialidade);
    $("#l_categoria").val(L.categoria);
    $("#l_descricao").val(L.descricao);
    $("#l_cidade").val(L.cidade);
    $("#l_estado").val(L.estado);
    $("#l_bairro").val(L.bairro);

    $("#l_cor_header").val(L.cor_header);
    $("#l_cor_texto_header").val(L.cor_texto_header);
    $("#l_cor_fundo").val(L.cor_fundo);
    $("#l_cor_texto").val(L.cor_texto);
    $("#l_cor_destaque").val(L.cor_destaque);

    if (L.logo) {

        $("#logoAtual, #pvLogo").attr("src", API(L.logo)).show();

    } else {

        $("#logoAtual, #pvLogo").hide();

    }

    ATUALIZAR_PREVIA();
}

$(".color-item input, #l_nome").on("input", ATUALIZAR_PREVIA);

$("#l_estado").on("input", function () {

    this.value = this.value.toUpperCase().replace(/[^A-Z]/g, "");

});

$("#l_logo").on("change", function () {

    const ARQUIVO = this.files[0];

    if (!ARQUIVO) {
        return;
    }

    if (ARQUIVO.size > 2 * 1024 * 1024) {

        MENSAGEM("#msgLoja", "A logo deve ter no máximo 2 MB.", false);

        this.value = "";

        return;
    }

    MENSAGEM("#msgLoja", "");

    const LEITOR = new FileReader();

    LEITOR.onload = function (E) {

        $("#logoAtual, #pvLogo").attr("src", E.target.result).show();

    };

    LEITOR.readAsDataURL(ARQUIVO);

});

$("#form_loja_perfil").on("submit", function (EVENTO) {

    EVENTO.preventDefault();

    const FORM = $(this);

    $.ajax({
        url: API("api/atualizar_loja.php"),
        type: "POST",
        data: new FormData(this),
        processData: false,
        contentType: false,
        dataType: "json",

        beforeSend: function () {

            MENSAGEM("#msgLoja", "Salvando...", true);

            FORM.find("button[type='submit']").prop("disabled", true);
        },

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                // Guarda a loja nova e já aplica o tema na própria página
                localStorage.setItem("loja", JSON.stringify(RESPOSTA.dados));
                
               window.location.reload();
                

            } else {

                MENSAGEM(
                    "#msgLoja",
                    RESPOSTA.mensagem || "Não foi possível salvar a loja.",
                    false
                );

            }

        },

        error: function (XHR) {

            MENSAGEM(
                "#msgLoja",
                XHR.responseJSON?.mensagem || "Erro ao conectar com o servidor.",
                false
            );

        },

        complete: function () {

            FORM.find("button[type='submit']").prop("disabled", false);

        }
    });

});

/* =========================================================
   ABA: VENDEDOR
========================================================= */

const CHAVES_PIX = {
    "": { placeholder: "Selecione o tipo da chave", maxlength: 100 },
    cpf: { placeholder: "000.000.000-00", maxlength: 14 },
    cnpj: { placeholder: "00.000.000/0000-00", maxlength: 18 },
    telefone: { placeholder: "(00) 00000-0000", maxlength: 15 },
    email: { placeholder: "seu@email.com", maxlength: 100 },
    aleatoria: { placeholder: "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx", maxlength: 36 }
};

function FORMATAR_CHAVE() {

    const TIPO = $("#v_tipo_chave").val();
    const CAMPO = $("#v_chave_pix");

    let VALOR = CAMPO.val();

    if (TIPO === "cpf" || TIPO === "cnpj") {

        VALOR = MASCARA_DOCUMENTO(VALOR, TIPO);

    } else if (TIPO === "telefone") {

        VALOR = MASCARA_TELEFONE(VALOR);

    }

    CAMPO.val(VALOR);
}

function AJUSTAR_CHAVE(LIMPAR) {

    const TIPO = $("#v_tipo_chave").val();
    const CONFIG = CHAVES_PIX[TIPO];
    const CAMPO = $("#v_chave_pix");

    CAMPO
        .attr({ placeholder: CONFIG.placeholder, maxlength: CONFIG.maxlength })
        .prop("disabled", TIPO === "");

    if (TIPO === "" || LIMPAR) {

        CAMPO.val("");

    } else {

        FORMATAR_CHAVE();

    }
}

$("#v_tipo_chave").on("change", function () {

    AJUSTAR_CHAVE(true);

});

$("#v_chave_pix").on("input", FORMATAR_CHAVE);

$("#v_telefone").on("input", function () {

    this.value = MASCARA_TELEFONE(this.value);

});

$("#v_documento").on("input", function () {

    this.value = MASCARA_DOCUMENTO(this.value);

});

function CARREGAR_VENDEDOR() {

    $.ajax({
        url: API("api/perfil_vendedor.php"),
        type: "POST",
        data: { id_vendedor: USUARIO_ATUAL.id },
        dataType: "json",

        success: function (RESPOSTA) {

            if (RESPOSTA.status !== "sucesso") {

                MENSAGEM(
                    "#msgVendedor",
                    RESPOSTA.mensagem || "Não foi possível carregar seus dados.",
                    false
                );

                return;
            }

            const V = RESPOSTA.dados;

            $("#v_nome").val(V.nome);
            $("#v_email").val(V.email);
            $("#v_telefone").val(MASCARA_TELEFONE(V.telefone || ""));
            $("#v_documento").val(MASCARA_DOCUMENTO(V.documento || ""));

            $("#v_tipo_chave").val(V.tipo_chave || "");

            AJUSTAR_CHAVE(false);

            if (V.tipo_chave) {

                $("#v_chave_pix").val(V.chave_pix || "");

                FORMATAR_CHAVE();

            }
        },

        error: function (XHR) {

            MENSAGEM(
                "#msgVendedor",
                XHR.responseJSON?.mensagem || "Erro ao conectar com o servidor.",
                false
            );

        }
    });
}

$("#form_vendedor").on("submit", function (EVENTO) {

    EVENTO.preventDefault();

    const FORM = $(this);

    $.ajax({
        url: API("api/atualizar_vendedor.php"),
        type: "POST",
        data: FORM.serialize(),
        dataType: "json",

        beforeSend: function () {

            MENSAGEM("#msgVendedor", "Salvando...", true);

            FORM.find("button[type='submit']").prop("disabled", true);
        },

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                // Mantém o usuário do localStorage atualizado
                const USUARIO = GET_USUARIO() || {};

                localStorage.setItem(
                    "usuario",
                    JSON.stringify(Object.assign(USUARIO, RESPOSTA.dados))
                );

                MENSAGEM("#msgVendedor", RESPOSTA.mensagem, true);

            } else {

                MENSAGEM(
                    "#msgVendedor",
                    RESPOSTA.mensagem || "Não foi possível salvar os dados.",
                    false
                );

            }
        },

        error: function (XHR) {

            MENSAGEM(
                "#msgVendedor",
                XHR.responseJSON?.mensagem || "Erro ao conectar com o servidor.",
                false
            );

        },

        complete: function () {

            FORM.find("button[type='submit']").prop("disabled", false);

        }
    });

});

/* ===================== SENHA ===================== */

$("#form_senha").on("submit", function (EVENTO) {

    EVENTO.preventDefault();

    const FORM = $(this);

    if ($("#s_nova").val() !== $("#s_confirmacao").val()) {

        MENSAGEM("#msgSenha", "As senhas não conferem.", false);

        return;
    }

    $.ajax({
        url: API("api/alterar_senha.php"),
        type: "POST",
        data: FORM.serialize(),
        dataType: "json",

        beforeSend: function () {

            MENSAGEM("#msgSenha", "Alterando...", true);

            FORM.find("button[type='submit']").prop("disabled", true);
        },

        success: function (RESPOSTA) {

            if (RESPOSTA.status === "sucesso") {

                FORM[0].reset();

                $("#form_senha input[name='id_vendedor']").val(USUARIO_ATUAL.id);

                MENSAGEM("#msgSenha", RESPOSTA.mensagem, true);

            } else {

                MENSAGEM(
                    "#msgSenha",
                    RESPOSTA.mensagem || "Não foi possível alterar a senha.",
                    false
                );

            }
        },

        error: function (XHR) {

            MENSAGEM(
                "#msgSenha",
                XHR.responseJSON?.mensagem || "Erro ao conectar com o servidor.",
                false
            );

        },

        complete: function () {

            FORM.find("button[type='submit']").prop("disabled", false);

        }
    });

});

/* ===================== INÍCIO ===================== */

function INICIAR_PERFIL() {

    // Precisa estar logado como vendedor
    if (!USUARIO_ATUAL || USUARIO_ATUAL.tipo !== "vendedor") {

        window.location.href = "login.html";

        return;
    }

    $("input[name='id_vendedor']").val(USUARIO_ATUAL.id);

    CARREGAR_LOJA(
        USUARIO_ATUAL,
        function (LOJA) {

            if (LOJA) {

                $("#semLoja").hide();
                $("#form_loja_perfil").show();

                PREENCHER_LOJA(LOJA);

            } else {

                $("#form_loja_perfil").hide();
                $("#semLoja").show();

            }
        },
        function (MENSAGEM_ERRO) {

            $("#semLoja").show().find("p").text(
                MENSAGEM_ERRO || "Não foi possível carregar a sua loja."
            );

        }
    );

    CARREGAR_VENDEDOR();
}

$(document).ready(INICIAR_PERFIL);
