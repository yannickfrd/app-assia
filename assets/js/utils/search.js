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
        this.btnClearElt.addEventListener("click", e => {
            e.preventDefault();
            this.clearSearch();
        });
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

        this.formSearch.querySelectorAll(".select2-container").forEach(containerElt => {
            let removeElts = containerElt.querySelectorAll(".select2-selection__choice__remove");
            removeElts.forEach(removeElt => {
                removeElt.click();
            });
            if (removeElts.length > 0) {
                containerElt.querySelector("input").click();
            }
        });

        this.formSearch.querySelector("input").focus();
    }
}