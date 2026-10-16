const URL_BASE = "https://implementing-cowboy-questionnaire-ice.trycloudflare.com/";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}
