import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";
import ValidationInput from "../utils/validationInput";

export default class SupportContributions {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.modalContributionElt = document.getElementById("modal-contribution");
        this.formContributionElt = this.modalContributionElt.querySelector("form[name=contribution]");
        this.titleElt = this.modalContributionElt.querySelector("h2");
        this.dateYearSelect = document.getElementById("contribution_contribDate_year");
        this.dateMonthSelect = document.getElementById("contribution_contribDate_month");
        this.typeSelect = document.getElementById("contribution_type");
        this.salaryAmtInput = document.getElementById("contribution_salaryAmt");
        this.resourcesAmtInput = document.getElementById("contribution_resourcesAmt");
        this.credentialInput = document.getElementById("contribution_credential");
        this.contribAmtInput = document.getElementById("contribution_contribAmt");
        this.paymentDateInput = document.getElementById("contribution_paymentDate");
        this.paymentTypeSelect = document.getElementById("contribution_paymentType");
        this.paymentAmtInput = document.getElementById("contribution_paymentAmt");
        this.stillDueAmtInput = document.getElementById("contribution_stillDueAmt");
        this.returnDateInput = document.getElementById("contribution_returnDate");
        this.returnAmtInput = document.getElementById("contribution_returnAmt");
        this.commentInput = document.getElementById("contribution_comment");

        this.btnNewElt = document.getElementById("js-new-contribution");
        this.contributionRate = parseFloat(this.btnNewElt.getAttribute("data-contribution-rate"));
        this.supportStartDate = new Date(this.btnNewElt.getAttribute("data-support-start-date"));
        this.supportEndDate = new Date(this.btnNewElt.getAttribute("data-support-end-date"));
        this.trElt = null;
        this.btnSaveElt = document.getElementById("js-btn-save");
        this.btnDeleteElt = document.getElementById("modal-btn-delete");

        this.sumContribAmtElt = document.getElementById("js-sumContribAmt");
        this.sumPaymentAmtElt = document.getElementById("js-sumPaymentAmt");
        this.sumStillDueAmtElt = document.getElementById("js-sumStillDueAmt");

        this.modalConfirmElt = document.getElementById("modal-confirm");

        this.themeColor = document.getElementById("header").getAttribute("data-color");
        this.countContributionsElt = document.getElementById("count-contributions");
        this.supportId = document.getElementById("support").getAttribute("data-support");

        this.loader = new Loader("#modal-contribution");
        this.modalElt = $("#modal-contribution");
        this.now = new Date();
        this.error = false;
        this.validationInput = new ValidationInput();

        this.init();
    }

    init() {
        this.btnNewElt.addEventListener("click", e => {
            if (this.loader.isInLoading() === false) {
                this.newContribution();
            }
        });

        document.querySelectorAll(".js-contribution").forEach(trElt => {
            let btnGetElt = trElt.querySelector("button.js-get");
            btnGetElt.addEventListener("click", e => {
                if (this.loader.isInLoading() === false) {
                    this.trElt = trElt;
                    this.getContribution(btnGetElt);
                }
            });
            let btnDeleteElt = trElt.querySelector("button.js-delete");
            btnDeleteElt.addEventListener("click", e => {
                this.trElt = trElt;
                this.modalConfirmElt.setAttribute("data-url", btnDeleteElt.getAttribute("data-url"));
            });

        });

        this.btnSaveElt.addEventListener("click", e => {
            e.preventDefault();
            if (this.loader.isInLoading() === false) {
                this.saveContribution();
            }
        });

        this.btnDeleteElt.addEventListener("click", e => {
            e.preventDefault();
            if (this.loader.isInLoading() === false) {
                this.deleteContribution(this.btnDeleteElt.href);
            }
        });

        this.modalConfirmElt.addEventListener("click", e => {
            e.preventDefault();

            this.ajaxRequest.init("GET", this.modalConfirmElt.getAttribute("data-url"), this.responseAjax.bind(this), true);
        });

        this.dateMonthSelect.addEventListener("input", e => {
            this.calculateContrib();
        });
        this.dateYearSelect.addEventListener("input", e => {
            this.calculateContrib();
        });

        this.typeSelect.addEventListener("input", e => {
            this.checkType();
        });

        this.resourcesAmtInput.addEventListener("input", e => {
            this.checkMoney(this.resourcesAmtInput);
            this.calculateContrib();
        });
        this.contribAmtInput.addEventListener("input", e => {
            this.checkMoney(this.contribAmtInput);
            this.calculateStillDue();
        });
        this.paymentAmtInput.addEventListener("input", e => {
            this.checkMoney(this.paymentAmtInput);
            this.calculateStillDue();
        });
        this.paymentDateInput.addEventListener("focusout", e => {
            this.checkPaymentAmt();
        });

        this.calculateSumAmts();
    }

    // Vérifie le type de partipation (redevance ou caution)
    checkType() {
        let option = this.getOption(this.typeSelect);

        let otherOption = 1;
        if (option === "1") {
            otherOption = 2;
            this.titleElt.textContent = "Redevance";
        } else {
            this.titleElt.textContent = "Caution";
        }

        this.formContributionElt.querySelectorAll(".js-type-" + option).forEach(elt => {
            elt.classList.remove("d-none");
        });

        this.formContributionElt.querySelectorAll(".js-type-" + otherOption).forEach(elt => {
            elt.classList.add("d-none");
        });

    }

    // Calcul la somme de tous les montants pour le footer du tableau
    calculateSumAmts() {
        this.sumContribAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-contribAmt")).toLocaleString() + " €";
        this.sumPaymentAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-paymentAmt")).toLocaleString() + " €";
        this.sumStillDueAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-stillDueAmt")).toLocaleString() + " €";
    }

    // Donne le ratio de jours de présence dans le mois
    getRateDays() {
        let contribDate = new Date(this.getOption(this.dateYearSelect) + "-" + this.getOption(this.dateMonthSelect) + "-01");
        let nextMonth = (new Date(contribDate)).setMonth(contribDate.getMonth() + 1);
        let nbDaysInMonth = Math.round((nextMonth - contribDate) / (1000 * 60 * 60 * 24));
        let rateDays = 1;

        if (this.supportStartDate > contribDate) {
            rateDays = 1 - ((this.supportStartDate - contribDate) / (1000 * 60 * 60 * 24) / nbDaysInMonth);
        }

        if (this.supportEndDate < nextMonth) {
            rateDays = 1 - ((nextMonth - this.supportEndDate) / (1000 * 60 * 60 * 24) / nbDaysInMonth);
        }

        if (rateDays > 1 || rateDays < 0) {
            rateDays = 0;
        }
        return rateDays;
    }

    // Calcule le montant de la participation
    calculateContrib() {
        if (!isNaN(this.resourcesAmtInput.value)) {
            this.contribAmtInput.value = Math.round((this.resourcesAmtInput.value * this.contributionRate) * this.getRateDays());
        }
    }

    // Calcule le restant dû
    calculateStillDue() {
        if (!isNaN(this.contribAmtInput.value) && !isNaN(this.paymentAmtInput.value)) {
            this.stillDueAmtInput.value = this.contribAmtInput.value - this.paymentAmtInput.value;
        }
    }

    // Vérifie la date du paiement
    checkPaymentDate() {
        let intervalWithNow = (this.now - new Date(this.paymentDateInput.value)) / (1000 * 60 * 60 * 24);

        if ((this.paymentDateInput.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            this.error = true;
            return this.validationInput.invalid(this.paymentDateInput, "La date est invalide.");
        }
        if (intervalWithNow < 0) {
            this.error = true;
            return this.validationInput.invalid(this.paymentDateInput, "La date ne peut être postérieure à la date du jour.");
        }
        if (!this.paymentDateInput.value && this.paymentAmtInput.value) {
            this.error = true;
            return this.validationInput.invalid(this.paymentDateInput, "La date ne peut pas être vide.");
        }
        return this.validationInput.valid(this.paymentDateInput);
    }

    // Vérifie le type de paiement saisie
    checkPaymentType() {
        if ((!this.getOption(this.paymentTypeSelect) && this.paymentDateInput.value) || (!this.getOption(this.paymentTypeSelect) && this.paymentAmtInput.value)) {
            this.error = true;
            return this.validationInput.invalid(this.paymentTypeSelect, "Ne peut pas être vide.");
        }
        return this.validationInput.valid(this.paymentTypeSelect);

    }

    // Vérifie le montant du paiement saisi
    checkPaymentAmt() {
        if (this.paymentDateInput.value && !this.paymentAmtInput.value) {
            this.error = true;
            return this.validationInput.invalid(this.paymentAmtInput, "Le montant ne pas être vide.");
        }
        if (isNaN(this.paymentAmtInput.value)) {
            this.error = true;
            return this.validationInput.invalid(this.paymentAmtInput, "Le montant n'est pas valide.");
        }
        return this.validationInput.valid(this.paymentAmtInput);
    }

    // Affiche un formulaire modal vierge
    newContribution() {
        this.loader.on();
        let today = new Date();
        this.selectOption(this.dateYearSelect, today.getFullYear());
        this.selectOption(this.dateMonthSelect, today.getMonth());
        this.selectOption(this.typeSelect, 1);
        this.reinitForm();
        this.modalContributionElt.querySelector("form").action = "/support/" + this.supportId + "/contribution/new";
        this.btnDeleteElt.classList.replace("d-block", "d-none");
        this.btnSaveElt.textContent = "Enregistrer";

        this.ajaxRequest.init("GET", this.btnNewElt.getAttribute("data-url"), this.responseAjax.bind(this), true);
    }

    // Requête pour obtenir le RDV sélectionné dans le formulaire modal
    getContribution(btnElt) {
        this.loader.on();

        this.contributionId = Number(btnElt.getAttribute("data-id"));
        this.modalContributionElt.querySelector("form").action = "/contribution/" + this.contributionId + "/edit";

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/contribution/" + this.contributionId + "/delete";

        this.btnSaveElt.textContent = "Mettre à jour";

        this.reinitForm();

        this.ajaxRequest.init("GET", btnElt.getAttribute("data-url"), this.responseAjax.bind(this), true);
    }

    // Réinitialise le formulaire
    reinitForm() {
        this.selectOption(this.paymentTypeSelect, null);

        this.paymentTypeSelect.classList.remove("is-valid");
        this.formContributionElt.querySelectorAll("input").forEach(inputElt => {
            if (inputElt.type != "hidden") {
                inputElt.classList.remove("is-valid");
                inputElt.value = null;
            }
        });
        this.commentInput.value = "";
    }

    // Sélectionne une des options dans une liste select
    selectOption(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }

    // Retourne l'option sélectionnée
    getOption(selectElt) {
        let optionValue;
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                optionValue = option.value;
            }
        });
        return optionValue;
    }

    // Enregistre la redevance
    saveContribution() {
        this.loader.on();
        this.error = false;
        this.checkPaymentDate();
        this.checkPaymentType();
        this.checkPaymentAmt();

        if (this.error === false) {
            let formData = new FormData(this.formContributionElt);
            let formToString = new URLSearchParams(formData).toString();
            this.ajaxRequest.init("POST", this.formContributionElt.getAttribute("action"), this.responseAjax.bind(this), true, formToString);
        } else {
            new MessageFlash("danger", "Veuillez corriger le(s) erreur(s) avant d'enregistrer.");
            this.loader.off();
        }
    }

    // Envoie une requête ajax pour supprimer la redevance
    deleteContribution(url) {
        this.loader.on();
        if (window.confirm("Voulez-vous vraiment supprimer cette redevance ?")) {
            this.ajaxRequest.init("GET", url, this.responseAjax.bind(this), true);
        }
    }

    // Réponse du serveur
    responseAjax(response) {
        let data = JSON.parse(response);
        if (data.code === 200) {
            switch (data.action) {
                case "getResources":
                    this.getResources(data.data);
                    this.loader.off(false);
                    break;
                case "show":
                    this.showContribution(data.data.contribution);
                    this.loader.off(false);
                    break;
                case "create":
                    this.createContribution(data.data.contribution);
                    this.loader.off(true);
                    new MessageFlash(data.alert, data.msg);
                    break;
                case "update":
                    this.updateContribution(data.data.contribution);
                    this.loader.off(true);
                    new MessageFlash(data.alert, data.msg);
                    break;
                case "delete":
                    this.trElt.remove();
                    this.countContributionsElt.textContent = parseInt(this.countContributionsElt.textContent) - 1;
                    this.loader.off(true);
                    new MessageFlash(data.alert, data.msg);
                    break;
                default:
                    this.loader.off(true);
                    new MessageFlash(data.alert, data.msg);
                    break;
            }
        }
        this.loading = false;
        this.calculateSumAmts();

    }

    // Donne le montant des ressources du ménage
    getResources(data) {
        this.modalElt.modal("show");
        this.salaryAmtInput.value = data.salaryAmt;
        this.resourcesAmtInput.value = data.resourcesAmt;
        this.contribAmtInput.value = data.contribAmt;
        this.checkType();
    }

    // Donne la redevance sélectionnée dans le formulaire modal
    showContribution(contribution) {
        this.modalElt.modal("show");
        this.dateYearSelect.value = contribution.contribDate.substring(0, 4);
        this.dateMonthSelect.value = contribution.contribDate.substring(6, 7);
        this.selectOption(this.typeSelect, contribution.type);
        this.salaryAmtInput.value = contribution.salaryAmt;
        this.resourcesAmtInput.value = contribution.resourcesAmt;
        this.credentialInput.value = contribution.credential;
        this.contribAmtInput.value = contribution.contribAmt;
        this.paymentDateInput.value = contribution.paymentDate ? contribution.paymentDate.substring(0, 10) : null;
        this.selectOption(this.paymentTypeSelect, contribution.paymentType);
        this.paymentAmtInput.value = contribution.paymentAmt;
        this.stillDueAmtInput.value = contribution.stillDueAmt;
        this.returnDateInput.value = contribution.returnDate ? contribution.returnDate.substring(0, 10) : null;
        this.returnAmtInput.value = contribution.returnAmt;
        this.commentInput.value = contribution.comment;
        this.checkType();
    }

    // Crée la ligne de la nouvelle redevance dans le tableau
    createContribution(data) {
        let contributionElt = document.createElement("tr");
        contributionElt.className = "js-contribution";

        contributionElt.innerHTML = this.getPrototypeContribution(data);

        let containerContributionsElt = document.getElementById("container-contributions");
        containerContributionsElt.insertBefore(contributionElt, containerContributionsElt.firstChild);
        this.countContributionsElt.textContent = parseInt(this.countContributionsElt.textContent) + 1;

        this.calculateSumAmts();

        let btnGetElt = contributionElt.querySelector("button.js-get");
        btnGetElt.addEventListener("click", e => {
            if (this.loader.isInLoading() === false) {
                this.trElt = contributionElt;
                this.getContribution(btnGetElt);
            }
        });

        let btnDeleteElt = contributionElt.querySelector("button.js-delete");
        btnDeleteElt.addEventListener("click", e => {
            this.trElt = contributionElt;
            this.modalConfirmElt.setAttribute("data-url", btnDeleteElt.getAttribute("data-url"));
        });
    }

    // Met à jour la ligne du tableau correspondant au contribution
    updateContribution(contribution) {
        this.trElt.querySelector("td.js-contribDate").textContent = contribution.contribDate.substring(0, 7);
        this.trElt.querySelector("td.js-type").textContent = contribution.typeToString;
        this.trElt.querySelector("td.js-contribAmt").textContent = contribution.contribAmt ? contribution.contribAmt + " €" : "";
        this.trElt.querySelector("td.js-paymentAmt").textContent = contribution.paymentAmt ? contribution.paymentAmt + " €" : "";
        this.trElt.querySelector("td.js-stillDueAmt").textContent = contribution.stillDueAmt ? contribution.stillDueAmt + " €" : "";
        this.trElt.querySelector("td.js-paymentDate").textContent = contribution.paymentDate ? new Date(contribution.paymentDate).toLocaleDateString("fr") : "";
        this.trElt.querySelector("td.js-paymentType").textContent = contribution.paymentTypeToString;
        this.trElt.querySelector("td.js-comment").textContent = contribution.comment.length > 70 ? contribution.comment.slice(0, 70) + "..." : contribution.comment;
        this.calculateSumAmts();
    }

    // Crée la ligne de la contribution
    getPrototypeContribution(contribution) {
        return `
            <td scope="row" class="text-center">
                <button class="btn btn-dark btn-sm shadow js-get" data-id="${contribution.id}" 
                    data-url="/contribution/${contribution.id}/get" 
                    data-placement="bottom" title="Voir la redevance"><span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle js-contribDate">${contribution.contribDate.substring(0, 7)}</td>
            <td class="align-middle js-type">${contribution.typeToString}</td>
            <td class="align-middle text-right js-contribAmt">${contribution.contribAmt ? contribution.contribAmt + " €" : ""}</td>
            <td class="align-middle text-right js-paymentAmt">${contribution.paymentAmt ? contribution.paymentAmt + " €" : ""}</td>
            <td class="align-middle text-right js-stillDueAmt">${contribution.stillDueAmt ? contribution.stillDueAmt + " €" : ""}</td>
            <td class="align-middle js-paymentDate">${contribution.paymentDate ? new Date(contribution.paymentDate).toLocaleDateString("fr") : ""}</td>
            <td class="align-middle js-paymentType">${contribution.paymentType ? contribution.paymentTypeToString : ""}</td>
            <td class="align-middle js-comment">${contribution.comment.length > 70 ? contribution.comment.slice(0, 70) + "..." : contribution.comment}</td>
            <td class="align-middle text-center">
                <button data-url="/contribution/${contribution.id}/delete" 
                    class="js-delete btn btn-danger btn-sm shadow my-1" data-placement="bottom" title="Supprimer la redevance" data-toggle="modal" data-target="#modal-block">
                    <span class="fas fa-trash-alt"></span>
                </button>
            </td>`
    }

    // Vérifie si le montant saisie est valide
    checkMoney(moneyElt) {
        moneyElt.value = moneyElt.value.replace(" ", "");
        moneyElt.value = moneyElt.value.replace(",", ".");
        if (Number(moneyElt.value) >= 0) {
            return this.validationInput.valid(moneyElt);
        }
        return this.validationInput.invalid(moneyElt, "Montant invalide.");
    }

    // Vérifie si la date est valide 
    checkDate(dateElt) {
        let interval = Math.round((this.now - new Date(dateElt.value)) / (1000 * 60 * 60 * 24));
        if ((dateElt.value && !Number.isInteger(interval)) || interval > (365 * 99) || interval < -(365 * 99)) {
            return this.validationInput.invalid(dateElt, "Date invalide.");
        }
        return this.validationInput.valid(dateElt);
    }

    // Donne la somme des montants
    getSumAmts(elts) {
        let array = [];
        elts.forEach(elt => {
            if (elt.textContent) {
                array.push(parseFloat(elt.textContent));
            }
        });

        let sum = array.reduce((a, b) => a + b, 0);

        if (!isNaN(sum)) {
            return sum;
        }
        return "Erreur";
    }
}