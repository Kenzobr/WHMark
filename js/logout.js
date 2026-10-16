function SAIR(){
    localStorage.removeItem("loja");
    localStorage.removeItem("usuario");
    window.top.location = 'login.html';
}