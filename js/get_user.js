  
function GET_USUARIO() {

    try {

        return JSON.parse(localStorage.getItem("usuario") || "null");

    } catch (ERRO) {

        return null;

    }
}