import AjaxRequest from "../utils/ajaxRequest";
import Loader from "./loader";

export default class SearchLocation {

    constructor(containerId, lengthSearch = 3, time = 500, limit = 5, lat = 49.04, lon = 2.04) {
        this.ajaxRequest = new AjaxRequest();
        this.containerElt = document.getElementById(containerId);
        this.searchElt = this.containerElt.querySelector(".js-search");
        this.addressElt = this.containerElt.querySelector(".js-address");
        this.cityElt = this.containerElt.querySelector(".js-city");
        this.zipcodeElt = this.containerElt.querySelector(".js-zipcode");
        this.locationIdElt = this.containerElt.querySelector(".js-locationId");
        this.latElt = this.containerElt.querySelector(".js-lat");
        this.lonElt = this.containerElt.querySelector(".js-lon");
        this.resultsSearchElt = this.createResultsListElt();
        this.lengthSearch = lengthSearch; // Nombre de caractères minimum pour lancer la recherche
        this.time = time; // Durée en millisecondes
        this.limit = limit; // Nombre d'éléments retournés
        this.lat = lat; // Latitude
        this.lon = lon; // Longitude
        this.features = null;
        this.countdownID = null;
        this.loader = new Loader();
        this.init();
    }

    init() {
        this.searchElt.addEventListener("keyup", this.timer.bind(this));
        this.searchElt.addEventListener("click", () => {
            this.resultsSearchElt.classList.replace("fade-out", "fade-in");
            setTimeout(this.hideListResults.bind(this), 100);
        });
    }

    // Crée la liste des résultats
    createResultsListElt() {
        let resultsListElt = document.createElement("div");
        resultsListElt.id = "results_list_location";
        resultsListElt.className = "w-100 list-group pr-sm-2 d-block fade-in position-absolute z-index-1000";
        this.searchElt.parentNode.appendChild(resultsListElt);
        return resultsListElt;
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
            let valueSearch = this.searchElt.value.replace(" ", "+");
            let geo = "&lat=" + this.lat + "&lon=" + this.lon; // Donne une priorité géographique
            let url = "https://api-adresse.data.gouv.fr/search/?q=" + valueSearch + geo + "&limit=" + this.limit;
            this.ajaxRequest.init("GET", url, this.responseAjax.bind(this), true);
        }
    }

    // Réponse Ajax
    responseAjax(response) {
        let datas = JSON.parse(response);
        this.features = datas.features;
        console.log(this.features);

        this.resultsSearchElt.innerHTML = "";
        if (this.features.length > 0) {
            this.addItem();
        } else {
            this.noResult();
        }
        this.resultsSearchElt.classList.replace("d-none", "d-block");
        this.resultsSearchElt.classList.replace("fade-out", "fade-in");
        this.loader.off();
    }

    // Ajoute un élément à la liste des résultats
    addItem() {
        let i = 0;
        this.features.forEach(feature => {
            let label = feature.properties.label;
            if (feature.properties.type === "municipality") {
                label = label + " (" + feature.properties.context + ")";
            }
            let aElt = document.createElement("a");
            aElt.innerHTML = "<span class='text-secondary small'>" + label + "</span>";
            aElt.className = "list-group-item list-group-item-action pl-3 pr-1 py-1 cursor-pointer";
            aElt.setAttribute("data-feature", i);
            this.resultsSearchElt.appendChild(aElt);
            aElt.addEventListener("click", () => {
                this.updateLocation(aElt.getAttribute("data-feature"));
            });
            i++;
        });
    }

    // Met à jour les champs du formulaire
    updateLocation(i) {
        let feature = this.features[i];
        this.searchElt.value = feature.properties.label;
        this.addressElt.value = feature.properties.name;
        this.cityElt.value = feature.properties.city;
        this.zipcodeElt.value = feature.properties.postcode;
        if (this.locationIdElt) {
            this.locationIdElt.value = feature.properties.id;
            this.lonElt.value = feature.geometry.coordinates[0];
            this.latElt.value = feature.geometry.coordinates[1];
        }
        this.hideListResults();
    }

    // Affiche 'Aucun résultat.'
    noResult() {
        let spanElt = document.createElement("p");
        spanElt.textContent = "Aucun résultat.";
        spanElt.className = "list-group-item pl-3 py-2 text-secondary small";
        this.resultsSearchElt.appendChild(spanElt);
    }

    // Supprime la liste des résultats au click
    hideListResults() {
        window.addEventListener("click", function () {
            this.resultsSearchElt.classList.replace("fade-in", "fade-out");
            this.resultsSearchElt.classList.replace("d-block", "d-none");
        }.bind(this), {
            once: true
        });
    }
}