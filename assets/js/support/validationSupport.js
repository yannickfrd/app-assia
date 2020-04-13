import MessageFlash from "../utils/messageFlash";
import DisplayInputs from "../utils/displayInputs";
import ValidationInput from "../utils/validationInput";

// Validation des données de la fiche personne
export default class ValidationSupport {

    constructor() {
        this.startDate = "support_group_startDate";
        this.startDateInputElt = document.getElementById(this.startDate);
        this.startDateLabelElt = document.querySelector("label[for=" + this.startDate + "]");

        this.endDate = "support_group_endDate";
        this.endDateInputElt = document.getElementById(this.endDate);
        this.endDateLabelElt = document.querySelector("label[for=" + this.endDate + "]");

        this.status = "support_group_status";
        this.statusSelectElt = document.getElementById(this.status);
        this.statusLabelElt = document.querySelector("label[for=" + this.status + "]");

        this.now = new Date();
        this.validationInput = new ValidationInput();
        this.init();
    }

    init() {
        if (!this.endDateInputElt) {
            this.startDateInputElt.value = this.getDateNow();
            this.setOption(this.statusSelectElt, 2);
        }

        this.startDateInputElt.addEventListener("focusout", this.checkStartDate.bind(this));
        if (this.endDateInputElt) {
            this.endDateInputElt.addEventListener("focusout", this.checkEndDate.bind(this));
        }
        this.statusSelectElt.addEventListener("change", this.checkStatus.bind(this));

        document.getElementById("send").addEventListener("click", e => {
            if (this.getNbErrors()) {
                e.preventDefault(), {
                    once: true
                };
                new MessageFlash("danger", "Veuillez corriger les erreurs avant d'enregistrer.");
            }
        });

        new DisplayInputs("support_group_", "status", "select", [4]);
    }

    checkStartDate() {
        let intervalWithNow = Math.round((this.now - new Date(this.startDateInputElt.value)) / (24 * 3600 * 1000));
        let status = parseInt(this.getOption(this.statusSelectElt));

        if ((this.startDateInputElt.value && !Number.isInteger(intervalWithNow)) || intervalWithNow > (365 * 9)) {
            return this.validationInput.invalid(this.startDateInputElt, "La date est invalide.");
        }
        if (intervalWithNow < -30) {
            return this.validationInput.invalid(this.startDateInputElt, "Le début du suivi ne peut pas être supérieur de 30 jours par rapport à la date du jour.");
        }
        if (!intervalWithNow && [2, 3, 4].indexOf(status) != -1) {
            return this.validationInput.invalid(this.startDateInputElt, "Le date de début ne peut pas être vide");
        }
        if (intervalWithNow || (!intervalWithNow && status === 1)) {
            return this.validationInput.valid(this.startDateInputElt);
        }
        return this.validationInput.valid(this.startDateInputElt);
    }
    checkEndDate() {
        let startDate = new Date(this.startDateInputElt.value);
        let endDate = new Date(this.endDateInputElt.value);
        let intervalWithStart = Math.round((endDate - startDate) / (24 * 3600 * 1000));
        let intervalWithNow = Math.round((this.now - endDate) / (24 * 3600 * 1000));

        if ((this.endDateInputElt.value && !Number.isInteger(intervalWithNow)) || intervalWithNow > (365 * 9)) {
            return this.validationInput.invalid(this.endDateInputElt, "La date est invalide.");
        }

        if (intervalWithStart < 0) {
            return this.validationInput.invalid(this.endDateInputElt, "La fin du suivi ne peut pas être antérieure au début du suivi.");
        }
        if (intervalWithNow < 0) {
            return this.validationInput.invalid(this.endDateInputElt, "La fin du suivi ne peut être postérieur à la date du jour.");
        }
        if (!this.endDateInputElt.value && this.getOption(this.statusSelectElt) == 4) {
            return this.validationInput.invalid(this.endDateInputElt, "La date de fin ne peut pas être vide si le suivi est terminé.");
        }
        if (this.endDateInputElt.value) {
            this.setOption(this.statusSelectElt, 4);
            this.statusSelectElt.click();
        }
        return this.validationInput.valid(this.endDateInputElt);
    }

    checkStatus() {
        let statusValue = this.getOption(this.statusSelectElt);

        if (statusValue >= 1 && statusValue <= 5) {
            return this.validationInput.valid(this.statusSelectElt);
        }
        return this.validationInput.invalid(this.statusSelectElt, "Le statut doit être renseigné.");
    }

    getOption(selectElt) {
        let value = false;
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.selected) {
                value = parseInt(option.value);
            }
        });
        return value;
    }

    setOption(elt, value) {
        elt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === value) {
                return option.selected = true;
            }
            return option.selected = false;
        });
    }

    // Donne la date actuelle
    getDateNow() {
        let month = this.now.getMonth() + 1;
        if (this.now.getMonth() < 10) {
            month = "0" + month;
        }
        let day = this.now.getDate();
        if (this.now.getDate() < 10) {
            day = "0" + day;
        }
        return this.now.getFullYear() + "-" + month + "-" + day;
    }

    // Renvoie le nombre de champs invalides
    getNbErrors() {
        return document.querySelectorAll(".js-invalid").length;
    }
}