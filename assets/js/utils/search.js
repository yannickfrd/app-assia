// Classe pour les différents modules de recherche
export default class Search {

    constructor(formId) {
        this.formSearch = document.getElementById(formId);
        this.inputElts = this.formSearch.getElementsByTagName("input");
        this.checkboxElts = this.formSearch.querySelectorAll("input[type='checkbox']");
        this.selectElts = this.formSearch.getElementsByTagName("select");
        this.resultsElt = document.getElementById("results");
        this.btnClearElt = this.formSearch.querySelector("button[type='reset']");
        this.firstInput = this.formSearch.querySelector("input");

        this.dateDay = document.getElementById("date_day")
        this.dateMonth = document.getElementById("date_month")
        this.dateYear = document.getElementById("date_year")

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
        if (this.dateYear) {
            this.changeDate(this.dateYear, this.dateMonth, this.dateDay);
        }
        if (this.startYear) {
            this.changeDate(this.startYear, this.startMonth, this.startDay);
        }
        if (this.endYear) {
            this.changeDate(this.endYear, this.endMonth, this.endDay);
        }
    }

    changeDate(yearElt, monthElt, dayElt) {
        monthElt.addEventListener("change", () => {
            this.updateSelect(dayElt, "1");
        });
        yearElt.addEventListener("change", () => {
            this.updateSelect(dayElt, "1");
        });
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