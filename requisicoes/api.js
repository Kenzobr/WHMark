const URL_BASE = "https://monday-variable-bachelor-mere.trycloudflare.com/";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}
