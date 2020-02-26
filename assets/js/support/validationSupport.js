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
        this.statusInputElt = document.getElementById(this.status);
        this.statusLabelElt = document.querySelector("label[for=" + this.status + "]");

        this.now = new Date();

        this.init();
    }

    init() {
        if (!this.endDateInputElt) {
            this.startDateInputElt.value = this.getDateNow();
            this.setOption(this.statusInputElt, 2);
        }

        this.startDateInputElt.addEventListener("focusout", this.checkStartDate.bind(this));
        if (this.endDateInputElt) {
            this.endDateInputElt.addEventListener("focusout", this.checkEndDate.bind(this));
        }
        this.statusInputElt.addEventListener("input", this.checkStatus.bind(this));

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
        let startDate = new Date(this.startDateInputElt.value);
        let interval = Math.round((this.now - startDate) / (24 * 3600 * 1000));

        if (!Number.isInteger(interval)) {
            this.invalid("startDate", this.startDateLabelElt, this.startDateInputElt, "La date est invalide.");
        } else if (interval < -7) {
            this.invalid("startDate", this.startDateLabelElt, this.startDateInputElt, "Le début du suivi ne peut pas être supérieur de 7 jours par rapport à la date du jour.");
        } else {
            this.valid("startDate", this.startDateInputElt);
        }
    }
    checkEndDate() {
        let startDate = new Date(this.startDateInputElt.value);
        let endDate = new Date(this.endDateInputElt.value);
        let intervalWithStart = Math.round((endDate - startDate) / (24 * 3600 * 1000));
        let intervalWithNow = Math.round((this.now - endDate) / (24 * 3600 * 1000));

        if (intervalWithStart < 0) {
            this.invalid("startDate", this.endDateLabelElt, this.endDateInputElt, "La fin du suivi ne peut pas être antérieure au début du suivi.");
        } else if (intervalWithNow < 0) {
            this.invalid("startDate", this.endDateLabelElt, this.endDateInputElt, "La fin du suivi ne peut être postérieur à la date du jour.");
        } else {
            this.valid("startDate", this.endDateInputElt);
            this.setOption(this.statusInputElt, 4);
        }
    }

    checkStatus() {
        this.statusInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.statusValue = parseInt(option.value);
            }
        });
        if (this.statusValue >= 1 && this.statusValue <= 5) {
            this.valid("status", this.statusInputElt);
        } else {
            this.invalid("status", this.statusLabelElt, this.statusInputElt, "Le statut doit être renseigné.");
        }
    }

    setOption(elt, value) {
        elt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
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
    valid(field, input) {
        if (input.classList.contains("is-invalid")) {
            input.classList.replace("is-invalid", "is-valid");
            document.querySelector(".js-invalid-" + field).remove();
        } else {
            input.classList.add("is-valid");
        }
    }

    // Met le champ en invalide et met un message d'erreur
    invalid(field, label, input, msg) {
        if (document.querySelector("label>span.js-invalid-" + field)) {
            document.querySelector("span.js-invalid-" + field).remove();
        }
        if (!input.classList.contains("is-invalid")) {
            input.classList.add("is-invalid");
        }
        let invalidFeedbackElt = document.createElement("span");
        invalidFeedbackElt.className = "invalid-feedback d-block js-invalid js-invalid-" + field;
        invalidFeedbackElt.innerHTML = `
                <span class="form-error-icon badge badge-danger text-uppercase">Erreur</span> 
                <span class="form-error-message">${msg}</span>
                `
        label.appendChild(invalidFeedbackElt);
    }

    // Renvoie le nombre de champs invalides
    getNbErrors() {
        let nbErrors = document.querySelectorAll(".js-invalid").length;
        return nbErrors;
    }
}