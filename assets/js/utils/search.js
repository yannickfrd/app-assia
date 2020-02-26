// Classe pour les différentes modules de recherche
export default class Search {

    constructor(formId) {
        this.formSearch = document.getElementById(formId);
        this.inputElts = this.formSearch.getElementsByTagName("input");
        this.checkboxElts = this.formSearch.querySelectorAll("input[type='checkbox']");
        this.selectElts = this.formSearch.getElementsByTagName("select");
        this.btnClearElt = this.formSearch.querySelector("button[type='reset']");
        this.init();
    }

    init() {
        this.btnClearElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.clearSearch();
        }.bind(this));
    }

    // Efface les données du formulaire de recherche au clic
    clearSearch() {
        this.inputElts.forEach(inputElt => {
            inputElt.value = null;
        });
        this.checkboxElts.forEach(checkboxElt => {
            checkboxElt.removeAttribute("checked");
            checkboxElt.value = "0";
        });
        this.selectElts.forEach(selectElt => {
            selectElt.querySelectorAll("option").forEach(option => {
                option.selected = "";
            });
        });
        this.formSearch.querySelectorAll("li.select2-selection__choice").forEach(liElt => {
            liElt.remove();
        });
    }
}