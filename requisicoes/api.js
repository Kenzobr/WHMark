const URL_BASE = "https://prix-bug-warner-run.trycloudflare.com/";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}
