fetch("footer_vendedor.html")
    .then(response => {
        if (!response.ok) {
            throw new Error("Erro ao carregar o footer");
        }

        return response.text();
    })
    .then(data => {
        document.getElementById("footer").innerHTML = data;

        console.log(
            "Total de itens:",
            document.querySelectorAll("#footer .menu-item").length
        );

        console.log(
            "Links:",
            [...document.querySelectorAll("#footer .menu-item")]
                .map(item => item.innerText)
        );

        console.log(
            "Itens carregados:",
            document.querySelectorAll("#footer .menu-item").length
        );

        const PAGINA =
            window.location.pathname.split("/").pop()
            || "index_vendedor.html";

        document.querySelectorAll("#footer .menu-item").forEach(item => {
            item.classList.toggle(
                "active",
                item.getAttribute("href") === PAGINA
            );
        });
    })
    .catch(error => {
        console.error("Erro ao carregar o footer:", error);
    });