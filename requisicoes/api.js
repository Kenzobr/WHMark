const URL_BASE = "https://federation-heart-pittsburgh-sensor.trycloudflare.com/";

function API(ROTA) {
    return URL_BASE + ROTA.replace(/^\/+/, "");
}