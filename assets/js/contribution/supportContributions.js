import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";
import ValidationInput from "../utils/validationInput";
import ParametersUrl from "../utils/parametersUrl";

export default class SupportContributions {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.modalContributionElt = document.getElementById("modal-contribution");
        this.formContributionElt = this.modalContributionElt.querySelector("form[name=contribution]");
        this.dateYearSelect = document.getElementById("contribution_month_year");
        this.dateMonthSelect = document.getElementById("contribution_month_month");
        this.typeSelect = document.getElementById("contribution_type");
        this.salaryAmtInput = document.getElementById("contribution_salaryAmt");
        this.resourcesAmtInput = document.getElementById("contribution_resourcesAmt");
        this.housingAssistanceInput = document.getElementById("contribution_housingAssitanceAmt");
        this.dueAmtInput = document.getElementById("contribution_dueAmt");
        this.calculationMethodElt = document.getElementById("contribution_dueAmt_help");
        this.paymentDateInput = document.getElementById("contribution_paymentDate");
        this.paymentTypeSelect = document.getElementById("contribution_paymentType");
        this.paidAmtInput = document.getElementById("contribution_paidAmt");
        this.stillDueAmtInput = document.getElementById("contribution_stillDueAmt");
        this.returnDateInput = document.getElementById("contribution_returnDate");
        this.returnAmtInput = document.getElementById("contribution_returnAmt");
        this.commentInput = document.getElementById("contribution_comment");

        this.resourcesChecked = false;
        this.salaryAmt = null;
        this.resourcesAmt = null;
        this.contributionAmt = null;
        this.dueAmt = null;
        this.rentAmt = 0;

        this.btnNewElt = document.getElementById("js-new-contribution");
        this.contributionRate = parseFloat(this.btnNewElt.getAttribute("data-contribution-rate"));
        this.supportStartDate = new Date(this.btnNewElt.getAttribute("data-support-start-date"));
        this.supportEndDate = new Date(this.btnNewElt.getAttribute("data-support-end-date"));
        this.trElt = null;
        this.btnSaveElt = document.getElementById("js-btn-save");
        this.btnDeleteElt = document.getElementById("modal-btn-delete");

        this.sumDueAmtElt = document.getElementById("js-sumDueAmt");
        this.sumPaidAmtElt = document.getElementById("js-sumPaidAmt");
        this.sumStillDueAmtElt = document.getElementById("js-sumStillDueAmt");

        this.modalConfirmElt = document.getElementById("modal-confirm");

        this.themeColor = document.getElementById("header").getAttribute("data-color");
        this.countContributionsElt = document.getElementById("count-contributions");
        this.nbTotalContributionsElt = document.getElementById("nb-total-contributions");
        this.supportId = document.getElementById("support").getAttribute("data-support");

        this.loader = new Loader("#modal-contribution");
        this.modalElt = $("#modal-contribution");
        this.now = new Date();
        this.error = false;
        this.validationInput = new ValidationInput();
        this.parametersUrl = new ParametersUrl();

        this.init();
    }

    init() {
        this.btnNewElt.addEventListener("click", () => {
            if (this.loader.isInLoading() === false) {
                this.newContribution();
            }
        });

        document.querySelectorAll(".js-contribution").forEach(trElt => {
            let btnGetElt = trElt.querySelector("button.js-get");
            btnGetElt.addEventListener("click", () => {
                if (this.loader.isInLoading() === false) {
                    this.trElt = trElt;
                    this.getContribution(Number(btnGetElt.getAttribute("data-id")));
                }
            });
            let btnDeleteElt = trElt.querySelector("button.js-delete");
            btnDeleteElt.addEventListener("click", () => {
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

        this.dateMonthSelect.addEventListener("input", () => {
            this.calculateContrib();
        });
        this.dateYearSelect.addEventListener("input", () => {
            this.calculateContrib();
        });

        this.typeSelect.addEventListener("input", () => {
            this.checkType();
        });

        this.resourcesAmtInput.addEventListener("input", () => {
            this.checkMoney(this.resourcesAmtInput);
            this.calculateContrib();
        });
        this.housingAssistanceInput.addEventListener("input", () => {
            this.checkMoney(this.housingAssistanceInput);
            this.calculateContrib();
        });
        this.dueAmtInput.addEventListener("input", () => {
            this.checkMoney(this.dueAmtInput);
            this.calculateStillDue();
        });
        this.paidAmtInput.addEventListener("input", () => {
            this.checkMoney(this.paidAmtInput);
            this.calculateStillDue();
        });
        this.paymentDateInput.addEventListener("focusout", () => {
            this.checkPaidAmt();
        });

        this.calculateSumAmts();

        let contributionId = Number(this.parametersUrl.get("contributionId"));
        this.trElt = document.getElementById("contribution-" + contributionId);
        if (this.trElt) {
            this.getContribution(contributionId);
        }
    }

    // Vérifie le type de partipation (redevance ou caution)
    checkType() {
        let option = this.getOption(this.typeSelect);
        let otherOption = 1;
        if (option === "1") {
            otherOption = 2;
        } else {
            this.calculationMethodElt.textContent = "";
        }

        this.formContributionElt.querySelectorAll(".js-type-" + option).forEach(elt => {
            elt.classList.remove("d-none");
        });

        this.formContributionElt.querySelectorAll(".js-type-" + otherOption).forEach(elt => {
            elt.classList.add("d-none");
        });

        if (option != "1") {
            this.salaryAmtInput.value = "";
            this.resourcesAmtInput.value = "";
            this.housingAssistanceInput.value = "";
        }

        if (option === "2") {
            this.formContributionElt.querySelector(".js-caution").classList.remove("d-none");
        } else {
            this.formContributionElt.querySelector(".js-caution").classList.add("d-none");
            this.returnDateInput.value = "";
            this.returnAmtInput.value = "";
        }

        if (option === "3") {
            this.formContributionElt.querySelector(".js-payment").classList.add("d-none");
        } else {
            this.formContributionElt.querySelector(".js-payment").classList.remove("d-none");
        }

        if (option === "4") {
            this.formContributionElt.querySelector(".js-dueAmt").classList.replace("d-block", "d-none");
            this.dueAmtInput.value = "";
        } else {
            this.formContributionElt.querySelector(".js-dueAmt").classList.replace("d-none", "d-block");
        }


    }

    // Calcul la somme de tous les montants pour le footer du tableau
    calculateSumAmts() {
        this.sumDueAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-dueAmt")).toLocaleString() + " €";
        this.sumPaidAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-paidAmt")).toLocaleString() + " €";
        this.sumStillDueAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-stillDueAmt")).toLocaleString() + " €";
    }

    // Donne le ratio de jours de présence dans le mois
    getRateDays() {
        let month = new Date(this.getOption(this.dateYearSelect) + "-" + this.getOption(this.dateMonthSelect) + "-01");
        let nextMonth = (new Date(month)).setMonth(month.getMonth() + 1);
        let nbDaysInMonth = Math.round((nextMonth - month) / (1000 * 60 * 60 * 24));
        let rateDays = 1;

        if (this.supportStartDate > month) {
            rateDays = 1 - ((this.supportStartDate - month) / (1000 * 60 * 60 * 24) / nbDaysInMonth);
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
        let rateDays = this.getRateDays();
        if (this.contributionAmt > 0) {
            this.dueAmtInput.value = this.contributionAmt - this.housingAssistanceInput.value;
            this.calculationMethodElt.textContent = "Mode de calcul : Montant fixé dans l'évalution sociale (" + this.contributionAmt + " €)" +
                (this.housingAssistanceInput.value > 0 ? " - Montant APL (" + this.housingAssistanceInput.value + " €)" : "") + ".";
        } else if (this.rentAmt > 0) {
            this.dueAmtInput.value = (Math.round((this.rentAmt * rateDays) * 100) / 100) - this.housingAssistanceInput.value;
            this.calculationMethodElt.textContent = "Mode de calcul : Montant du loyer (" + this.rentAmt + " €)" +
                (rateDays < 1 ? " x Prorata présence sur le mois (" + (Math.round(rateDays * 10000) / 100) + " %)" : "") +
                (this.housingAssistanceInput.value > 0 ? " - Montant APL (" + this.housingAssistanceInput.value + " €)." : ".");
        } else if (!isNaN(this.resourcesAmtInput.value) && !isNaN(this.contributionRate)) {
            this.dueAmtInput.value = Math.round((this.resourcesAmtInput.value * this.contributionRate) * rateDays * 100) / 100;
            this.calculationMethodElt.textContent = "Mode de calcul : Montant des ressources (" + this.resourcesAmtInput.value +
                " €) x Taux de participation (" + (this.contributionRate * 100) + " %)" + (rateDays < 1 ? " x Prorata présence sur le mois (" +
                    (Math.round(rateDays * 10000) / 100) + " %)." : ".");
        }
    }

    // Calcule le restant dû
    calculateStillDue() {
        if (!isNaN(this.dueAmtInput.value) && !isNaN(this.paidAmtInput.value)) {
            this.stillDueAmtInput.value = Math.round((this.dueAmtInput.value - this.paidAmtInput.value) * 100) / 100;
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
        if (!this.paymentDateInput.value && this.paidAmtInput.value) {
            this.error = true;
            return this.validationInput.invalid(this.paymentDateInput, "La date ne peut pas être vide.");
        }
        return this.validationInput.valid(this.paymentDateInput);
    }

    // Vérifie le type de paiement saisie
    checkPaymentType() {
        if ((!this.getOption(this.paymentTypeSelect) && this.paymentDateInput.value) || (!this.getOption(this.paymentTypeSelect) && this.paidAmtInput.value)) {
            this.error = true;
            return this.validationInput.invalid(this.paymentTypeSelect, "Ne peut pas être vide.");
        }
        return this.validationInput.valid(this.paymentTypeSelect);

    }

    // Vérifie le montant du paiement saisi
    checkPaidAmt() {
        if (this.paymentDateInput.value && !this.paidAmtInput.value) {
            this.error = true;
            return this.validationInput.invalid(this.paidAmtInput, "Le montant ne pas être vide.");
        }
        if (isNaN(this.paidAmtInput.value)) {
            this.error = true;
            return this.validationInput.invalid(this.paidAmtInput, "Le montant n'est pas valide.");
        }
        return this.validationInput.valid(this.paidAmtInput);
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

        if (this.resourcesChecked === false) {
            this.ajaxRequest.init("GET", this.btnNewElt.getAttribute("data-url"), this.responseAjax.bind(this), true);
        } else {
            this.getResources();
        }
    }

    // Requête pour obtenir le RDV sélectionné dans le formulaire modal
    getContribution(id) {
        this.loader.on();

        this.modalContributionElt.querySelector("form").action = "/contribution/" + id + "/edit";

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/contribution/" + id + "/delete";
        this.btnSaveElt.textContent = "Mettre à jour";

        this.reinitForm();

        this.ajaxRequest.init("GET", "/contribution/" + id + "/get", this.responseAjax.bind(this), true);
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
        this.checkPaidAmt();

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
                    break;
                case "show":
                    this.showContribution(data.data.contribution);
                    break;
                case "create":
                    this.createContribution(data.data.contribution);
                    new MessageFlash(data.alert, data.msg);
                    break;
                case "update":
                    this.updateContribution(data.data.contribution);
                    new MessageFlash(data.alert, data.msg);
                    break;
                case "delete":
                    this.trElt.remove();
                    this.updateCounts(-1);
                    this.loader.off(true);
                    new MessageFlash(data.alert, data.msg);
                    break;
                default:
                    this.loader.off(false);
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

        if (this.resourcesChecked === false) {
            this.salaryAmt = data.salaryAmt;
            this.resourcesAmt = data.resourcesAmt;
            this.contributionAmt = data.contributionAmt;
            this.dueAmt = data.dueAmt;
            this.rentAmt = data.rentAmt;
            this.resourcesChecked = true;
        }

        this.salaryAmtInput.value = this.salaryAmt;
        this.resourcesAmtInput.value = this.resourcesAmt;
        this.contributionAmt = this.contributionAmt;
        this.dueAmtInput.value = this.dueAmt;
        this.rentAmt = this.rentAmt;

        this.checkType();
        this.calculateContrib();
        this.loader.off(false);
    }

    // Donne la redevance sélectionnée dans le formulaire modal
    showContribution(contribution) {
        this.modalElt.modal("show");
        this.dateYearSelect.value = contribution.month.substring(0, 4);
        this.dateMonthSelect.value = contribution.month.substring(6, 7);
        this.selectOption(this.typeSelect, contribution.type);
        this.salaryAmtInput.value = contribution.salaryAmt;
        this.resourcesAmtInput.value = contribution.resourcesAmt;
        this.dueAmtInput.value = contribution.dueAmt;
        this.paymentDateInput.value = contribution.paymentDate ? contribution.paymentDate.substring(0, 10) : null;
        this.selectOption(this.paymentTypeSelect, contribution.paymentType);
        this.paidAmtInput.value = contribution.paidAmt;
        this.stillDueAmtInput.value = Math.round(contribution.stillDueAmt * 100) / 100;
        this.returnDateInput.value = contribution.returnDate ? contribution.returnDate.substring(0, 10) : null;
        this.returnAmtInput.value = contribution.returnAmt;
        this.commentInput.value = contribution.comment;
        this.checkType();
        this.loader.off(false);
    }

    // Crée la ligne de la nouvelle redevance dans le tableau
    createContribution(data) {
        let contributionElt = document.createElement("tr");
        contributionElt.className = "js-contribution";

        contributionElt.innerHTML = this.getPrototypeContribution(data);

        let containerContributionsElt = document.getElementById("container-contributions");
        containerContributionsElt.insertBefore(contributionElt, containerContributionsElt.firstChild);
        this.updateCounts(1);

        this.calculateSumAmts();

        let btnGetElt = contributionElt.querySelector("button.js-get");
        btnGetElt.addEventListener("click", () => {
            if (this.loader.isInLoading() === false) {
                this.trElt = contributionElt;
                this.getContribution(Number(btnGetElt.getAttribute("data-id")));
            }
        });

        let btnDeleteElt = contributionElt.querySelector("button.js-delete");
        btnDeleteElt.addEventListener("click", () => {
            this.trElt = contributionElt;
            this.modalConfirmElt.setAttribute("data-url", btnDeleteElt.getAttribute("data-url"));
        });
        this.loader.off(true);
    }

    // Met à jour la ligne du tableau correspondant au contribution
    updateContribution(contribution) {
        this.trElt.querySelector("td.js-month").textContent = new Date(contribution.paymentDate).toLocaleDateString("fr").substring(3, 10);
        this.trElt.querySelector("td.js-type").textContent = contribution.typeToString;
        this.trElt.querySelector("td.js-dueAmt").textContent = contribution.dueAmt ? contribution.dueAmt + " €" : "";
        this.trElt.querySelector("td.js-paidAmt").textContent = contribution.paidAmt ? contribution.paidAmt + " €" : "";
        this.trElt.querySelector("td.js-stillDueAmt").textContent = contribution.stillDueAmt ? Math.round(contribution.stillDueAmt * 100) / 100 + " €" : "";
        this.trElt.querySelector("td.js-paymentDate").textContent = contribution.paymentDate ? new Date(contribution.paymentDate).toLocaleDateString("fr") : "";
        this.trElt.querySelector("td.js-paymentType").textContent = contribution.paymentTypeToString;
        this.trElt.querySelector("td.js-comment").textContent = contribution.comment && contribution.comment.length > 70 ? contribution.comment.slice(0, 65) + "..." : contribution.comment;
        this.calculateSumAmts();
        this.loader.off(true);
    }

    // Crée la ligne de la contribution
    getPrototypeContribution(contribution) {
        return `
            <td scope="row" class="text-center">
                <button class="btn btn-${this.themeColor} btn-sm shadow js-get" data-id="${contribution.id}" 
                    data-url="/contribution/${contribution.id}/get" 
                    data-placement="bottom" title="Voir la redevance"><span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle js-month">${new Date(contribution.paymentDate).toLocaleDateString("fr").substring(3, 10)}</td>
            <td class="align-middle js-type">${contribution.typeToString}</td>
            <td class="align-middle text-right js-dueAmt">${contribution.dueAmt ? contribution.dueAmt + " €" : ""}</td>
            <td class="align-middle text-right js-paidAmt">${contribution.paidAmt ? contribution.paidAmt + " €" : ""}</td>
            <td class="align-middle text-right js-stillDueAmt">${contribution.stillDueAmt ? Math.round(contribution.stillDueAmt * 100) / 100 + " €" : ""}</td>
            <td class="align-middle js-paymentDate">${contribution.paymentDate ? new Date(contribution.paymentDate).toLocaleDateString("fr") : ""}</td>
            <td class="align-middle js-paymentType">${contribution.paymentType ? contribution.paymentTypeToString : ""}</td>
            <td class="align-middle js-comment">${contribution.comment ? contribution.comment.slice(0, 65) : "" }</td>
            <td class="align-middle text-center">
                <button data-url="/contribution/${contribution.id}/delete" 
                    class="js-delete btn btn-danger btn-sm shadow my-1" data-placement="bottom" title="Supprimer la redevance" data-toggle="modal" data-target="#modal-block">
                    <span class="fas fa-trash-alt"></span>
                </button>
            </td>`
    }

    updateCounts(value) {
        this.countContributionsElt.textContent = parseInt(this.countContributionsElt.textContent) + value;
        if (this.nbTotalContributionsElt) {
            this.nbTotalContributionsElt.textContent = parseInt(this.nbTotalContributionsElt.textContent) + value;
        }
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
                array.push(parseFloat(elt.textContent.replace(",", ".")));
            }
        });

        let sum = array.reduce((a, b) => a + b, 0);

        if (!isNaN(sum)) {
            return sum;
        }
        return "Erreur";
    }
}