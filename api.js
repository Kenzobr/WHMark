const URL_BASE = "https://watching-spokesman-price-propecia.trycloudflare.com";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}
