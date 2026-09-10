fetch("footer.html")
    .then(response => {

        if (!response.ok) {
            throw new Error("Não foi possível carregar o footer.html");
        }

        return response.text();

    })
    .then(data => {

        document.getElementById("footer").innerHTML = data;

    })
    .catch(error => {

        console.error("Erro ao carregar o footer:", error);

    });
