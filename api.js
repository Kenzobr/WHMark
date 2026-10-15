const URL_BASE = "https://cab-budgets-detail-harvard.trycloudflare.com/";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}