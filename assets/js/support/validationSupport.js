import AjaxRequest from "../utils/ajaxRequest";
import MessageFlash from "../utils/messageFlash";
import DisplayInputs from "../utils/displayInputs";
import ValidationInput from "../utils/validationInput";
import Select from "../utils/select";
import CheckDate from "../utils/checkDate";
import Loader from "../utils/loader";

// Validation des données de la fiche personne
export default class ValidationSupport {

    constructor() {
        this.ajaxRequest = new AjaxRequest();
        this.validationInput = new ValidationInput();
        this.select = new Select();
        this.loader = new Loader();

        this.serviceSelectElt = document.getElementById("support_service");
        this.statusSelectElt = document.getElementById("support_status");
        this.startDateInputElt = document.getElementById("support_startDate");
        this.endDateInputElt = document.getElementById("support_endDate");
        this.endStatusInputElt = document.getElementById("support_endStatus");
        this.btnSubmitElts = document.querySelectorAll("button[type='submit']");
        this.dateInputElts = document.querySelectorAll("input[type='date']");
        this.now = new Date();

        this.init();
    }

    init() {
        this.service = this.select.getOption(this.serviceSelectElt);
        this.serviceSelectElt.addEventListener("change", this.changeService.bind(this));
        if (this.statusSelectElt) {
            this.statusSelectElt.addEventListener("change", this.checkStatus.bind(this));
            this.startDateInputElt.addEventListener("focusout", this.checkStartDate.bind(this));
            this.endDateInputElt.addEventListener("focusout", this.checkEndDate.bind(this));
            this.endStatusInputElt.addEventListener("change", this.checkEndStatus.bind(this));
        }

        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt));
        })

        this.btnSubmitElts.forEach(btnElt => {

            btnElt.addEventListener("click", e => {
                if (this.statusSelectElt) {
                    this.checkStatus();
                    this.checkStartDate();
                    this.checkEndDate();
                    this.checkEndStatus();
                }

                if (this.validationInput.getNbErrors()) {
                    e.preventDefault(), {
                        once: true
                    };
                    new MessageFlash("danger", "Veuillez corriger les erreurs indiquées avant d'enregistrer.");
                }
            });
        })
        new DisplayInputs("support_", "status", "select", [4]);
    }

    checkDate(dateInputElt) {
        let checkDate = new CheckDate(dateInputElt);


        if (checkDate.isValid() === false) {
            return this.validationInput.invalid(dateInputElt, "La date est invalide.");
        }

        return this.validationInput.valid(dateInputElt);
    }

    changeService() {
        if (window.confirm("Le changement de service va recharger la page actuelle. Confirmer ?")) {
            this.loader.on();
            document.getElementById("send").click();
        } else {
            this.select.setOption(this.serviceSelectElt, this.service);
        }
    }

    checkStatus() {
        let statusValue = this.select.getOption(this.statusSelectElt);

        if (statusValue >= 1 && statusValue <= 6) {
            return this.validationInput.valid(this.statusSelectElt);
        }
        return this.validationInput.invalid(this.statusSelectElt, "Le statut doit être renseigné.");
    }

    checkStartDate() {
        let intervalWithNow = (this.now - new Date(this.startDateInputElt.value)) / (24 * 3600 * 1000);
        let status = this.select.getOption(this.statusSelectElt);

        if ((this.startDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            return this.validationInput.invalid(this.startDateInputElt, "La date est invalide.");
        }
        if (intervalWithNow < -30) {
            return this.validationInput.invalid(this.startDateInputElt, "Le début du suivi ne peut pas être supérieur de 30 jours par rapport à la date du jour.");
        }

        if (!intervalWithNow && [2, 3, 4].indexOf(status) != -1) {
            return this.validationInput.invalid(this.startDateInputElt, "La date de début ne peut pas être vide.");
        }
        if (intervalWithNow && [1, 5].indexOf(status) != -1) {
            return this.validationInput.invalid(this.startDateInputElt, "Il ne peut pas y avoir de date début de suivi pour une pré-admission.");
        }
        if (intervalWithNow || (!intervalWithNow && status === 1)) {
            return this.validationInput.valid(this.startDateInputElt);
        }
        return this.validationInput.valid(this.startDateInputElt);
    }

    checkEndDate() {
        let startDate = new Date(this.startDateInputElt.value);
        let endDate = new Date(this.endDateInputElt.value);
        let intervalWithStart = (endDate - startDate) / (24 * 3600 * 1000);
        let intervalWithNow = (this.now - endDate) / (24 * 3600 * 1000);

        if ((this.endDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 9)) {
            return this.validationInput.invalid(this.endDateInputElt, "La date est invalide.");
        }
        if (intervalWithStart < 0) {
            return this.validationInput.invalid(this.endDateInputElt, "La fin du suivi ne peut pas être antérieure au début du suivi.");
        }
        if (intervalWithNow < 0) {
            return this.validationInput.invalid(this.endDateInputElt, "La fin du suivi ne peut être postérieure à la date du jour.");
        }
        if (!this.endDateInputElt.value && this.select.getOption(this.statusSelectElt) === 4) { // statut égal à Terminé
            return this.validationInput.invalid(this.endDateInputElt, "La date de fin ne peut pas être vide si le suivi est terminé.");
        }
        if (this.endDateInputElt.value) {
            this.select.setOption(this.statusSelectElt, 4);
            this.statusSelectElt.click();
        }
        return this.validationInput.valid(this.endDateInputElt);
    }

    checkEndStatus() {
        if (!this.endStatusInputElt.value && this.select.getOption(this.statusSelectElt) === 4) { // statut égal à Terminé
            return this.validationInput.invalid(this.endStatusInputElt, "La situation à la fin du suivi ne peut pas être vide.");
        }
        return this.validationInput.valid(this.endStatusInputElt);

    }
}