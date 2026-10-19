const URL_BASE = "https://sometimes-profiles-glow-goals.trycloudflare.com/";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}
