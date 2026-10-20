const URL_BASE = "https://clicks-boot-carriers-fine.trycloudflare.com/";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}
