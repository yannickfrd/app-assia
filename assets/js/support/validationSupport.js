import MessageFlash from "../utils/messageFlash";
import DisplayInputs from "../utils/displayInputs";

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

        document.getElementById("send").addEventListener("click", function (e) {
            if (this.getNbErrors()) {
                e.preventDefault(), {
                    once: true
                };
                new MessageFlash("danger", "Veuillez corriger les erreurs avant d'enregistrer.");
            }
        }.bind(this));

        new DisplayInputs("support_group_", "status", "select", [4]);
    }

    checkStartDate() {
        let interval = Math.round((this.now - new Date(this.startDateInputElt.value)) / (24 * 3600 * 1000));
        let status = parseInt(this.getOption(this.statusSelectElt));

        if ((this.startDateInputElt.value && !Number.isInteger(interval)) || interval > (365 * 9)) {
            return this.invalid("startDate", this.startDateLabelElt, this.startDateInputElt, "La date est invalide.");
        }
        if (interval < -30) {
            return this.invalid("startDate", this.startDateLabelElt, this.startDateInputElt, "Le début du suivi ne peut pas être supérieur de 30 jours par rapport à la date du jour.");
        }
        if (!interval && [2, 3, 4].indexOf(status) != -1) {
            return this.invalid("startDate", this.startDateLabelElt, this.startDateInputElt, "Le date de début ne peut pas être vide");
        }
        if (interval || (!interval && status === 1)) {
            return this.valid("startDate", this.startDateInputElt);
        }
        return this.valid("startDate", this.startDateInputElt);
    }
    checkEndDate() {
        let startDate = new Date(this.startDateInputElt.value);
        let endDate = new Date(this.endDateInputElt.value);
        let intervalWithStart = Math.round((endDate - startDate) / (24 * 3600 * 1000));
        let intervalWithNow = Math.round((this.now - endDate) / (24 * 3600 * 1000));

        if (intervalWithStart < 0) {
            return this.invalid("startDate", this.endDateLabelElt, this.endDateInputElt, "La fin du suivi ne peut pas être antérieure au début du suivi.");
        }
        if (intervalWithNow < 0) {
            return this.invalid("startDate", this.endDateLabelElt, this.endDateInputElt, "La fin du suivi ne peut être postérieur à la date du jour.");
        }
        if (!this.endDateInputElt.value && this.getOption(this.statusSelectElt) == 4) {
            return this.invalid("startDate", this.endDateLabelElt, this.endDateInputElt, "La date de fin ne peut pas être vide si le suivi est terminé.");
        }
        if (this.endDateInputElt.value) {
            this.setOption(this.statusSelectElt, 4);
            this.statusSelectElt.click();
        }
        return this.valid("startDate", this.endDateInputElt);
    }

    checkStatus() {
        let statusValue = this.getOption(this.statusSelectElt);

        if (statusValue >= 1 && statusValue <= 5) {
            return this.valid("status", this.statusSelectElt);
        }
        return this.invalid("status", this.statusLabelElt, this.statusSelectElt, "Le statut doit être renseigné.");
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

    // Met le champ en valide 
    valid(field, inputElt) {
        if (inputElt.classList.contains("is-invalid")) {
            inputElt.classList.replace("is-invalid", "is-valid");
            return this.removeInvalid(inputElt, document.querySelector(".js-invalid-" + field));
        }
        return inputElt.classList.add("is-valid");
    }

    // Met le champ en invalide et met un message d'erreur
    invalid(field, label, inputElt, msg) {
        if (document.querySelector("label>span.js-invalid-" + field)) {
            document.querySelector("span.js-invalid-" + field).remove();
        }
        if (!inputElt.classList.contains("is-invalid")) {
            inputElt.classList.add("is-invalid");
        }
        let invalidFeedbackElt = document.createElement("span");
        invalidFeedbackElt.className = "invalid-feedback d-block js-invalid js-invalid-" + field;
        invalidFeedbackElt.innerHTML = `
                <span class="form-error-icon badge badge-danger text-uppercase">Erreur</span> 
                <span class="form-error-message">${msg}</span>
                `
        label.appendChild(invalidFeedbackElt);
    }

    // Retire le message d'erreur de validité
    removeInvalid(inputElt, msgElt) {
        inputElt.classList.remove("is-invalid");
        if (msgElt) {
            msgElt.remove();
        }
    }

    // Renvoie le nombre de champs invalides
    getNbErrors() {
        return document.querySelectorAll(".js-invalid").length;
    }
}