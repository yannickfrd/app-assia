import AjaxRequest from "../utils/ajaxRequest";
import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";
import SelectType from "../utils/selectType";
import ValidationForm from "../utils/validationForm";
import ParametersUrl from "../utils/parametersUrl";

export default class SupportContributions {

    constructor() {
        this.ajaxRequest = new AjaxRequest();
        this.loader = new Loader("#modal-contribution");
        this.selectType = new SelectType();
        this.validationForm = new ValidationForm();
        this.parametersUrl = new ParametersUrl();

        this.resourcesChecked = false; // Ressources vérifiées dans la base de données
        this.salaryAmt = null;
        this.resourcesAmt = null;
        this.contributionAmt = null;
        this.toPayAmt = null;
        this.rentAmt = null;

        this.btnNewElt = document.getElementById("js-new-contribution");
        this.contributionRate = parseFloat(this.btnNewElt.getAttribute("data-contribution-rate"));
        this.supportStartDate = new Date(this.btnNewElt.getAttribute("data-support-start-date"));
        this.supportEndDate = new Date(this.btnNewElt.getAttribute("data-support-end-date"));
        this.trElt = null;

        this.modalConfirmElt = document.getElementById("modal-confirm");

        this.sumToPayAmtElt = document.querySelector(".js-sumToPayAmt");
        this.sumPaidAmtElt = document.querySelector(".js-sumPaidAmt");
        this.sumStillToPayAmtElt = document.querySelector(".js-sumStillToPayAmt");

        this.themeColor = document.getElementById("header").getAttribute("data-color");
        this.countContributionsElt = document.getElementById("count-contributions");
        this.nbTotalContributionsElt = document.getElementById("nb-total-contributions");
        this.supportId = document.getElementById("support").getAttribute("data-support");
        this.contributionId = null;

        // Formulaire modal
        this.modalContributionElt = document.getElementById("modal-contribution");
        this.formContributionElt = this.modalContributionElt.querySelector("form[name=contribution]");
        this.typeSelect = document.getElementById("contribution_type");
        this.monthContribYearSelect = document.getElementById("contribution_monthContrib_year");
        this.monthContribMonthSelect = document.getElementById("contribution_monthContrib_month");
        this.monthContribDaySelect = document.getElementById("contribution_monthContrib_day");
        this.salaryAmtInput = document.getElementById("contribution_salaryAmt");
        this.resourcesAmtInput = document.getElementById("contribution_resourcesAmt");
        this.rentAmtInput = document.getElementById("contribution_rentAmt");
        this.aplAmtInput = document.getElementById("contribution_aplAmt");
        this.toPayAmtInput = document.getElementById("contribution_toPayAmt");
        this.calculationMethodElt = document.getElementById("calculationMethod");
        this.paymentDateInput = document.getElementById("contribution_paymentDate");
        this.paymentTypeSelect = document.getElementById("contribution_paymentType");
        this.paidAmtInput = document.getElementById("contribution_paidAmt");
        this.stillToPayAmtInput = document.getElementById("contribution_stillToPayAmt");
        this.returnAmtInput = document.getElementById("contribution_returnAmt");
        this.commentInput = document.getElementById("contribution_comment");
        this.infoContribElt = document.getElementById("js-info-contrib");

        this.btnDeleteElt = document.getElementById("modal-btn-delete");
        this.btnSaveElt = document.getElementById("js-btn-save");

        this.modalElt = $("#modal-contribution");
        this.now = new Date();
        this.error = false;

        this.init();
    }

    init() {
        this.btnNewElt.addEventListener("click", () => {
            if (this.loader.isInLoading() === false) {
                this.newContribution();
            }
        });

        document.querySelectorAll("tr.contribution").forEach(trElt => {
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
                this.tryToSave();
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

        this.typeSelect.addEventListener("input", () => {
            this.initForm();
            this.checkType();
        });

        this.modalContributionElt.querySelectorAll(".js-money").forEach(elt => {
            elt.addEventListener("input", () => {
                this.checkMoney(elt);
            });
        })

        // Récupère les ressources et calcul le montant à payer du suivi au clic sur le bouton.
        document.getElementById("btn-update-contribution").addEventListener("click", e => {
            e.preventDefault();
            this.loader.on();
            if (this.resourcesChecked === false) {
                this.ajaxRequest.init("GET", this.btnNewElt.getAttribute("data-url"), this.responseAjax.bind(this), true);
            } else {
                this.getResources();
            }
        });

        this.rentAmtInput.addEventListener("input", () => {
            this.calculateAmountToPay();
        });
        this.aplAmtInput.addEventListener("input", () => {
            this.calculateAmountToPay();
        });

        this.toPayAmtInput.addEventListener("input", () => {
            this.calculateAmountStillDue();
        });
        this.paidAmtInput.addEventListener("input", () => {
            this.calculateAmountStillDue();
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
        let option = this.selectType.getOption(this.typeSelect);

        // Masque tous les champs du formulaire.
        this.formContributionElt.querySelectorAll(".js-contrib").forEach(elt => {
            elt.classList.add("d-none");
        });

        // Si PF / Redevance et Loyer.
        if ([1, 2].indexOf(option) != -1) {
            this.selectType.setOption(this.monthContribDaySelect, "1");
            this.formContributionElt.querySelectorAll(".js-contribution").forEach(elt => {
                elt.classList.remove("d-none");
            });
        } else {
            this.calculationMethodElt.textContent = null;
        }
        if (option === 2) {
            this.formContributionElt.querySelector(".js-rent").classList.remove("d-none");
        }

        if ([1, 2, 3, 30, 31, 32].indexOf(option) != -1) { // Redevance, caution, remboursement
            this.formContributionElt.querySelector("label[for='contribution_toPayAmt']").textContent = "Montant à régler";
            this.formContributionElt.querySelectorAll(".js-payment").forEach(elt => {
                elt.classList.remove("d-none");
            });
        }
        if ([1, 2, 10].indexOf(option) != -1) { // Redevance, caution
            this.formContributionElt.querySelector(".js-stillToPayAmt").classList.remove("d-none");
        }
        if (option === 20) { // Prêt
            this.formContributionElt.querySelector("label[for='contribution_toPayAmt']").textContent = "Montant prêté";
        }
        if ([1, 2, 10, 30, 31, 32].indexOf(option) != -1) { // Redevance, Caution, Prêt
            this.formContributionElt.querySelector(".js-toPayAmt").classList.remove("d-none");
        }
        if (option === 11) { // Restitution caution
            this.formContributionElt.querySelector(".js-returnAmt").classList.remove("d-none");
        }
        if (option >= 1) { // Tout sauf vide
            this.formContributionElt.querySelector(".js-paymentDate").classList.remove("d-none");
            this.formContributionElt.querySelector(".js-paymentType").classList.remove("d-none");
            this.formContributionElt.querySelector(".js-comment").classList.remove("d-none");
        }
    }

    // Calcul la somme de tous les montants pour le footer du tableau.
    calculateSumAmts() {
        this.sumToPayAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-toPayAmt")).toLocaleString() + " €";
        this.sumPaidAmtElt.textContent = this.getSumAmts(document.querySelectorAll("td.js-paidAmt")).toLocaleString() + " €";

        let stillToPayAmtElts = document.querySelectorAll("td.js-stillToPayAmt");
        let sumStillToPayAmt = this.getSumAmts(stillToPayAmtElts);
        stillToPayAmtElts.forEach(elt => {
            this.changeTextColor(elt, elt.textContent);
        })
        this.sumStillToPayAmtElt.textContent = sumStillToPayAmt.toLocaleString() + " €";
        this.changeTextColor(this.sumStillToPayAmtElt, sumStillToPayAmt);
    }

    // Donne le ratio de jours de présence dans le mois.
    getRateDays() {
        let date = new Date(this.selectType.getOption(this.monthContribYearSelect) + "-" + this.selectType.getOption(this.monthContribMonthSelect) + "-01");
        let nextMonth = (new Date(date)).setMonth(date.getMonth() + 1);
        let nbDaysInMonth = Math.round((nextMonth - date) / (1000 * 60 * 60 * 24));
        let rateDays = 1;

        if (this.supportStartDate > date) {
            rateDays = 1 - ((this.supportStartDate - date) / (1000 * 60 * 60 * 24) / nbDaysInMonth);
        }

        if (this.supportEndDate < nextMonth) {
            rateDays = 1 - ((nextMonth - this.supportEndDate) / (1000 * 60 * 60 * 24) / nbDaysInMonth);
        }

        if (rateDays > 1 || rateDays < 0) {
            rateDays = 0;
        }

        return rateDays;
    }

    // Calcule le montant de la participation.
    calculateAmountToPay() {
        let rateDays = this.getRateDays();
        let calculationMethod = "";
        // Si redevance ou PF à régler
        if (this.contributionAmt > 0) {
            this.toPayAmtInput.value = this.contributionAmt - this.aplAmtInput.value;
            calculationMethod = "Montant fixé dans l'évalution sociale (" + this.contributionAmt + " €)" +
                (this.aplAmtInput.value > 0 ? " - Montant APL (" + this.aplAmtInput.value + " €)" : "") + ".";
            // Si loyer fixe à payer
        } else if (this.rentAmtInput.value > 0) {
            this.toPayAmtInput.value = (Math.round((this.rentAmtInput.value * rateDays) * 100) / 100) - this.aplAmtInput.value;
            calculationMethod = "Montant du loyer (" + this.rentAmtInput.value + " €)" +
                (rateDays < 1 ? " x Prorata présence sur le mois (" + (Math.round(rateDays * 10000) / 100) + " %)" : "") +
                (this.aplAmtInput.value > 0 ? " - Montant APL (" + this.aplAmtInput.value + " €)." : ".");
        } else if (!isNaN(this.resourcesAmtInput.value) && !isNaN(this.contributionRate)) {
            this.toPayAmtInput.value = Math.round((this.resourcesAmtInput.value * this.contributionRate) * rateDays * 100) / 100;
            calculationMethod = "Montant des ressources (" + this.resourcesAmtInput.value +
                " €) x Taux de participation (" + (this.contributionRate * 100) + " %)" + (rateDays < 1 ? " x Prorata présence sur le mois (" +
                    (Math.round(rateDays * 10000) / 100) + " %)." : ".");
        }
        this.calculationMethodElt.textContent = "Mode de calcul : " + calculationMethod;
    }

    // Calcule le restant dû.
    calculateAmountStillDue() {
        if (!isNaN(this.toPayAmtInput.value) && !isNaN(this.paidAmtInput.value)) {
            this.stillToPayAmtInput.value = Math.round((this.toPayAmtInput.value - this.paidAmtInput.value) * 100) / 100;
        }
        this.changeTextColor(this.stillToPayAmtInput, this.stillToPayAmtInput.value);
    }


    isValidForm() {
        this.error = false;
        let option = this.selectType.getOption(this.typeSelect);

        this.checkContributionDate(option);
        this.checkToPaidAmt(option);
        this.checkReturnAmt(option);
        this.checkPaymentDate(option);
        this.checkPaymentType(option);
        this.checkPaidAmt(option);

        return this.error === false ? true : false;
    }

    checkContributionDate(option) {
        if ([1, 2].indexOf(option) != -1) { // PF et Loyer
            if (!this.selectType.getOption(this.monthContribMonthSelect)) {
                this.error = true;
                this.validationForm.invalidField(this.monthContribMonthSelect, "Ne peut pas être vide.");
            } else {
                this.validationForm.validField(this.monthContribMonthSelect);
            }
            if (!this.selectType.getOption(this.monthContribYearSelect)) {
                this.error = true;
                this.validationForm.invalidField(this.monthContribYearSelect, "Ne peut pas être vide.");
            } else {
                this.validationForm.validField(this.monthContribYearSelect);
            }
        } else {
            this.selectType.setOption(this.monthContribYearSelect, "");
            this.selectType.setOption(this.monthContribMonthSelect, "");
            this.selectType.setOption(this.monthContribDaySelect, "");
        }
    }

    // Vérfifie le montant à payer si redevance ou caution
    checkToPaidAmt(option) {
        if (isNaN(this.toPayAmtInput.value)) {
            this.error = true;
            return this.validationForm.invalidField(this.toPayAmtInput, "La valeur est invalide.");
        }
        if ([1, 2, 10, 20].indexOf(option) != -1 && !this.toPayAmtInput.value) { // PF, loyer, cautionn prêt
            this.error = true;
            return this.validationForm.invalidField(this.toPayAmtInput, "Ne peut pas être vide.");
        }
    }

    // Vérifie le montant restitué si Restitution caution
    checkReturnAmt(option) {
        if (isNaN(this.returnAmtInput.value)) {
            this.error = true;
            return this.validationForm.invalidField(this.returnAmtInput, "La valeur est invalide.");
        }
        if (option == 11 && !this.returnAmtInput.value) { // Restitution Caution
            this.error = true;
            return this.validationForm.invalidField(this.returnAmtInput, "Ne peut pas être vide.");
        }
    }

    // Vérifie le montant du paiement saisi.
    checkPaidAmt(option) {
        if (isNaN(this.paidAmtInput.value)) {
            this.error = true;
            return this.validationForm.invalidField(this.paidAmtInput, "La valeur est invalide.");
        }
        if ((!this.paidAmtInput.value && [1, 2, 10].indexOf(option) != -1 && (this.paymentDateInput.value || this.selectType.getOption(this.paymentTypeSelect))) ||
            (!this.paidAmtInput.value && [30, 31, 32].indexOf(option) != -1)) {
            this.error = true;
            return this.validationForm.invalidField(this.paidAmtInput, "Ne peut pas être vide.");
        }
        return this.validationForm.validField(this.paidAmtInput);
    }

    // Vérifie la date du paiement.
    checkPaymentDate(option) {
        let intervalWithNow = (this.now - new Date(this.paymentDateInput.value)) / (1000 * 60 * 60 * 24);

        if ((this.paymentDateInput.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            this.error = true;
            return this.validationForm.invalidField(this.paymentDateInput, "Date invalide.");
        }
        if (intervalWithNow < 0) {
            this.error = true;
            return this.validationForm.invalidField(this.paymentDateInput, "La date ne peut être postérieure à la date du jour.");
        }
        if (!this.paymentDateInput.value && (option === 20 || this.paidAmtInput.value || this.selectType.getOption(this.paymentTypeSelect) || this.returnAmtInput.value)) {
            this.error = true;
            return this.validationForm.invalidField(this.paymentDateInput, "La date ne peut pas être vide.");
        }
        return this.validationForm.validField(this.paymentDateInput);
    }

    // Vérifie le type de paiement saisi.
    checkPaymentType(option) {
        if (!this.selectType.getOption(this.paymentTypeSelect) && (option === 20 || this.paymentDateInput.value || this.paidAmtInput.value || this.returnAmtInput.value)) {
            this.error = true;
            return this.validationForm.invalidField(this.paymentTypeSelect, "Ne peut pas être vide.");
        }
        return this.validationForm.validField(this.paymentTypeSelect);
    }


    // Affiche un formulaire modal vierge.
    newContribution() {
        this.contributionId = null;
        this.modalElt.modal("show");
        this.selectType.setOption(this.typeSelect, "");
        this.initForm();
        this.checkType();
        if (!this.contributionId) {
            this.selectType.setOption(this.monthContribMonthSelect, this.now.getMonth() + 1);
            this.selectType.setOption(this.monthContribYearSelect, this.now.getFullYear());
        }
        this.modalContributionElt.querySelector("form").action = "/support/" + this.supportId + "/contribution/new";
        this.btnDeleteElt.classList.replace("d-block", "d-none");
        this.btnSaveElt.textContent = "Enregistrer";
    }

    // Requête pour obtenir le RDV sélectionné dans le formulaire modal.
    getContribution(id) {
        this.loader.on();

        this.contributionId = id;
        this.modalContributionElt.querySelector("form").action = "/contribution/" + id + "/edit";

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/contribution/" + id + "/delete";
        this.btnSaveElt.textContent = "Mettre à jour";

        this.initForm();
        this.checkType();

        this.ajaxRequest.init("GET", "/contribution/" + id + "/get", this.responseAjax.bind(this), true);
    }

    // Réinitialise le formulaire.
    initForm() {
        this.selectType.setOption(this.paymentTypeSelect, "");
        this.paymentTypeSelect.classList.remove("is-valid");
        this.formContributionElt.querySelectorAll("input").forEach(inputElt => {
            if (inputElt.type != "hidden") {
                inputElt.classList.remove("is-valid");
                inputElt.value = null;
            }
        });
        this.commentInput.value = "";
        this.infoContribElt.innerHTML = "";
    }

    // Sélectionne une des options dans une liste select.
    selectOption(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }

    // Enregistre l'opération.
    tryToSave() {
        this.loader.on();
        if (this.isValidForm()) {
            let formData = new FormData(this.formContributionElt);
            let formToString = new URLSearchParams(formData).toString();
            this.ajaxRequest.init("POST", this.formContributionElt.getAttribute("action"), this.responseAjax.bind(this), true, formToString);
        } else {
            new MessageFlash("danger", "Veuillez corriger le(s) erreur(s) avant d'enregistrer.");
            this.loader.off();
        }
    }

    // Envoie une requête ajax pour supprimer l'enregistrement.
    deleteContribution(url) {
        this.loader.on();
        if (window.confirm("Voulez-vous vraiment supprimer cette enregistrement ?")) {
            this.ajaxRequest.init("GET", url, this.responseAjax.bind(this), true);
        }
    }

    // Réponse du serveur.
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

    // Donne le montant des ressources du ménage.
    getResources(data = null) {
        if (this.resourcesChecked === false) {
            this.salaryAmt = data.salaryAmt;
            this.resourcesAmt = data.resourcesAmt;
            this.contributionAmt = data.contributionAmt;
            this.toPayAmt = data.toPayAmt;
            this.rentAmt = data.rentAmt;
            this.resourcesChecked = true;
        }

        this.resourcesAmtInput.value = this.resourcesAmt;
        this.salaryAmtInput.value = this.salaryAmt;
        // this.contributionAmt = this.contributionAmt;
        this.toPayAmtInput.value = this.toPayAmt;
        this.rentAmtInput.value = this.rentAmt;

        this.calculateAmountToPay();
        this.loader.off();
    }

    // Donne la redevance sélectionnée dans le formulaire modal.
    showContribution(contribution) {
        // let modalContentElt = document.querySelector(".modal-content");
        // modalContentElt.innerHTML = contribution.content;
        this.modalElt.modal("show");
        this.selectOption(this.typeSelect, contribution.type);
        if (contribution.monthContrib) {
            this.selectOption(this.monthContribYearSelect, parseInt(contribution.monthContrib.substring(0, 4)));
            this.selectOption(this.monthContribMonthSelect, parseInt(contribution.monthContrib.substring(5, 7)));
        }
        this.salaryAmtInput.value = contribution.salaryAmt;
        this.resourcesAmtInput.value = contribution.resourcesAmt;
        this.rentAmtInput.value = contribution.rentAmt;
        this.toPayAmtInput.value = contribution.toPayAmt;
        this.paymentDateInput.value = contribution.paymentDate ? contribution.paymentDate.substring(0, 10) : null;
        this.selectOption(this.paymentTypeSelect, contribution.paymentType);
        this.paidAmtInput.value = contribution.paidAmt;
        this.stillToPayAmtInput.value = Math.round(contribution.stillToPayAmt * 100) / 100;
        this.returnAmtInput.value = contribution.returnAmt;
        this.commentInput.value = contribution.comment;

        this.infoContribElt.innerHTML = this.getInfoContribElt(contribution);

        this.checkType();
        this.loader.off(false);
    }

    // Donnes les informations sur l'enregistrements (date de création, créateur...).
    getInfoContribElt(contribution) {
        let htmlContent = `Créé le ${this.formatDatetime(contribution.createdAt)} par ${contribution.createdBy.fullname}`;
        if (contribution.createdAt != contribution.updatedAt) {
            htmlContent = htmlContent + `<br/> (modifié le ${this.formatDatetime(contribution.updatedAt)} par ${contribution.updatedBy.fullname})`;
        }
        return htmlContent;
    }

    // Crée la ligne de la nouvelle redevance dans le tableau.
    createContribution(data) {
        let contributionElt = document.createElement("tr");
        contributionElt.className = "js-payment";

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

    // Met à jour la ligne du tableau correspondant au contribution.
    updateContribution(contribution) {
        this.trElt.querySelector("td.js-type").textContent = contribution.typeToString + (contribution.type == 11 ? " (" + this.formatMoney(contribution.returnAmt) + ")" : "");
        this.trElt.querySelector("td.js-monthContrib").textContent = this.formatDatetime(contribution.monthContrib, "d/m");
        this.trElt.querySelector("td.js-toPayAmt").textContent = this.formatMoney(contribution.toPayAmt);
        this.trElt.querySelector("td.js-paidAmt").textContent = this.formatMoney(contribution.paidAmt);
        this.trElt.querySelector("td.js-stillToPayAmt").textContent = this.formatMoney(this.roundMoney(contribution.stillToPayAmt));
        this.trElt.querySelector("td.js-paymentDate").textContent = this.formatDatetime(contribution.paymentDate, "date");
        this.trElt.querySelector("td.js-paymentType").textContent = contribution.paymentTypeToString;
        this.trElt.querySelector("td.js-comment").textContent = this.sliceComment(contribution.comment);
        this.calculateSumAmts();
        this.loader.off(true);
    }

    // Crée la ligne de la contribution.
    getPrototypeContribution(contribution) {
        return `
            <td scope="row" class="align-middle text-center">
                <button class="btn btn-${this.themeColor} btn-sm shadow js-get" data-id="${contribution.id}" 
                    data-url="/contribution/${contribution.id}/get" data-toggle="tooltip" 
                    data-placement="bottom" title="Voir l'enregistrement"><span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle js-type">${contribution.typeToString}<br/>
                <span class="text-secondary">${contribution.type == 11 ? " (" + this.formatMoney(contribution.returnAmt) + ")" : "" }</span>
            </td>
            <td class="align-middle js-monthContrib">${this.formatDatetime(contribution.monthContrib, "d/m")}</td>
            <td class="align-middle text-right js-toPayAmt">${this.formatMoney(contribution.toPayAmt)}</td>
            <td class="align-middle text-right js-paidAmt">${this.formatMoney(contribution.paidAmt)}</td>
            <td class="align-middle text-right js-stillToPayAmt">${this.formatMoney(this.roundMoney(contribution.stillToPayAmt))}</td>
            <td class="align-middle text-center js-paymentDate">${this.formatDatetime(contribution.paymentDate, "date")}</td>
            <td class="align-middle js-paymentType">${contribution.paymentType ? contribution.paymentTypeToString : ""}</td>
            <td class="align-middle js-comment">${this.sliceComment(contribution.comment)}</td>
            <td class="align-middle js-createdAt">${this.formatDatetime(this.now, "date")}</td>
            <td class="align-middle text-center">
                <button data-url="/contribution/${contribution.id}/delete" 
                    class="js-delete btn btn-danger btn-sm shadow my-1" data-placement="bottom" title="Supprimer l'enregistrement" data-toggle="modal" data-target="#modal-block">
                    <span class="fas fa-trash-alt"></span>
                </button>
            </td>`
    }

    // Arrondi un nombre en valeur monétaire.
    roundMoney(number) {
        return number ? Math.round(number * 100) / 100 : "";
    }

    // Coupe un texte en un nombre maximum de caractères.
    sliceComment(comment, limit = 65) {
        if (comment === null) {
            return "";
        }
        return comment.length > limit ? comment.slice(0, limit) + "..." : comment;
    }

    // Met à jour le nombre d'enregistrements.
    updateCounts(value) {
        this.countContributionsElt.textContent = parseInt(this.countContributionsElt.textContent) + value;
        if (this.nbTotalContributionsElt) {
            this.nbTotalContributionsElt.textContent = parseInt(this.nbTotalContributionsElt.textContent) + value;
        }
    }

    // Vérifie si le montant saisie est valide.
    checkMoney(moneyElt) {
        let value = moneyElt.value;
        value = value.replace(" ", "").replace(",", ".");
        if (Number(value) >= 0) {
            return this.validationForm.validField(moneyElt);
        }
        return this.validationForm.invalidField(moneyElt, "Valeur invalide.");
    }

    // Vérifie si la date est valide.
    checkDate(dateElt) {
        let interval = Math.round((this.now - new Date(dateElt.value)) / (1000 * 60 * 60 * 24));
        if ((dateElt.value && !Number.isInteger(interval)) || interval > (365 * 99) || interval < -(365 * 99)) {
            return this.validationForm.invalidField(dateElt, "Date invalide.");
        }
        return this.validationForm.validField(dateElt);
    }

    // Donne la somme des montants.
    getSumAmts(elts) {
        let array = [];
        elts.forEach(elt => {
            let value = elt.textContent;
            if (value) {
                value = value.replace(" ", "").replace(",", ".");
                array.push(parseFloat(value));
            }
        });

        let sum = array.reduce((a, b) => a + b, 0);

        if (!isNaN(sum)) {
            return sum;
        }
        return "Erreur";
    }

    // Format un nombre en valeur monétaire.
    formatMoney(number, locale = "fr") {
        return number ? number.toFixed(2).replace(".", ",") + " €" : "";
    }

    // Formate une valeur texte en date.
    formatDatetime(date, type = "datetime", locale = "fr") {
        if (date === null) {
            return "";
        }

        date = new Date(date);

        switch (type) {
            case "date":
                return date.toLocaleDateString(locale);
                break;
            case "d/m":
                return date.toLocaleDateString(locale).substring(3, 10);
                break;
            case "time":
                return date.toLocaleTimeString(locale).substring(0, 5);
                break;
            default:
                return date.toLocaleDateString(locale) + " " + date.toLocaleTimeString(locale).substring(0, 5);
                break;
        }
    }

    // Change la couleur du texte d'un élément on fonction de la valeur d'un nombre.
    changeTextColor(elt, value) {
        if (parseFloat(value) > 0) {
            elt.classList.remove("text-success");
            elt.classList.add("text-danger");
        } else {
            elt.classList.remove("text-danger");
            elt.classList.add("text-success");
        }
    }
}