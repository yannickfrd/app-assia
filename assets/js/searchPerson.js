import Loader from "./utils/loader";
// Recherche instannée Ajax
export default class SearchPerson {

    constructor(ajaxRequest, lengthSearch, time) {
        this.ajaxRequest = ajaxRequest;
        this.searchElt = document.getElementById("search-person");
        this.resultsSearchElt = document.getElementById("results_search");
        this.lengthSearch = lengthSearch;
        this.time = time;
        this.countdownID = null;
        this.loader = new Loader();
        this.init();
    }

    init() {
        if (this.searchElt) {
            this.searchElt.addEventListener("keyup", this.timer.bind(this));
        }
    }

    // Timer avant de lancer la requête Ajax
    timer() {
        clearInterval(this.countdownID);
        this.countdownID = setTimeout(this.count.bind(this), this.time);
    }

    // Compte le nombre de caratères saisis et lance la requête Ajax<
    count() {
        let valueSearch = this.searchElt.value;
        if (valueSearch.length >= this.lengthSearch) {
            this.loader.on();
            let url = "/search/person?search=" + valueSearch;
            this.ajaxRequest.init("GET", url, this.response.bind(this), true);
        }
    }

    // Affiche les résultats de la rêquête
    response(data) {
        let dataJSON = JSON.parse(data);
        this.resultsSearchElt.innerHTML = "";
        if (dataJSON.nb_results > 0) {
            this.addItem(dataJSON);
        } else {
            this.noResult();
        }
        this.resultsSearchElt.classList.replace("d-none", "d-block");
        this.resultsSearchElt.classList.replace("fade-out", "fade-in");
        this.loader.off();

        document.addEventListener("click", e => {
            if (e.target.id != "search-person") {
                this.hideListResults();
            }
        });
    }

    // Ajoute un élément à la liste des résultats
    addItem(dataJSON) {
        dataJSON.results.forEach(person => {
            let aElt = document.createElement("a");
            aElt.innerHTML = "<span class='text-capitalize text-secondary small'>" + person.fullname + "</span>";
            aElt.href = "/person/" + person.id;
            aElt.className = "list-group-item list-group-item-action pl-3 pr-1 py-1";
            this.resultsSearchElt.appendChild(aElt);
            aElt.addEventListener("click", () => {
                this.loader.on();
            });
        });
    }

    // Affiche 'Aucun résultat.'
    noResult() {
        let spanElt = document.createElement("p");
        spanElt.textContent = "Aucun résultat.";
        spanElt.className = "list-group-item pl-3 py-2";
        this.resultsSearchElt.appendChild(spanElt);
    }

    // Supprime la liste des résultats au click
    hideListResults() {
        this.resultsSearchElt.classList.replace("fade-in", "fade-out");
        this.resultsSearchElt.classList.replace("d-block", "d-none");
    }
}