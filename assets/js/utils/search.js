// Classe pour les différentes modules de recherche
export default class Search {

    constructor(formId) {
        this.formSearch = document.getElementById(formId);
        this.inputElts = this.formSearch.getElementsByTagName("input");
        this.checkboxElts = this.formSearch.querySelectorAll("input[type='checkbox']");
        this.selectElts = this.formSearch.getElementsByTagName("select");
        this.resultsElt = document.getElementById("results");
        this.btnClearElt = this.formSearch.querySelector("button[type='reset']");
        this.firstInput = this.formSearch.querySelector("input");

        this.startDay = document.getElementById("date_start_day")
        this.startMonth = document.getElementById("date_start_month")
        this.startYear = document.getElementById("date_start_year")
        this.endDay = document.getElementById("date_end_day")
        this.endMonth = document.getElementById("date_end_month")
        this.endYear = document.getElementById("date_end_year")

        this.init();
    }

    init() {
        this.btnClearElt.addEventListener("click", e => {
            e.preventDefault();
            this.clearSearch();
        });
        this.checkDates();
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
            selectElt.querySelectorAll("option").forEach(optionElt => {
                optionElt.removeAttribute("selected");
                optionElt.selected = "";
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

        if (this.resultsElt) {
            this.resultsElt.textContent = "";
        }

        if (this.firstInput) {
            this.firstInput.focus();
        }
    }

    checkDates() {
        if (this.startMonth || this.endMonth) {
            this.startMonth.addEventListener("change", e => {
                this.updateSelect(this.startDay, "1");
            });
            this.startYear.addEventListener("change", e => {
                this.updateSelect(this.startDay, "1");
            });
        }
        if (this.endMonth || this.endYear) {
            this.endMonth.addEventListener("change", e => {
                this.updateSelect(this.endDay, "1");
            });
            this.endYear.addEventListener("change", e => {
                this.updateSelect(this.endDay, "1");
            });
        }
    }

    updateSelect(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(optionElt => {
            if (optionElt.value === value) {
                optionElt.setAttribute("selected", "selected")
            } else {
                optionElt.removeAttribute("selected");
                optionElt.selected = "";
            }
        });
    }
}