import DisplayInputs from "../utils/displayInputs";
import {
    isNumber
} from "util";

// Situation sociale
export default class evaluation {

    constructor() {
        this.selectedOptionElt;
        this.init();
    }

    init() {
        let prefix;
        // Situation sociale
        prefix = "support_grp_sitSocial_";
        new DisplayInputs(prefix, "speAnimal", "checkbox");
        new DisplayInputs(prefix, "speOther", "checkbox");
        this.editElt("", "sitSocial_specifity", "d-block");
        // Situation familiale
        prefix = "support_grp_sitFamilyGrp_";
        new DisplayInputs(prefix, "unbornChild", "select", [1]);
        new DisplayInputs(prefix, "famlReunification", "select", [1, 3, 4, 5]);
        prefix = "support_grp_sitHousing_";
        // Situation liée au logement
        new DisplayInputs(prefix, "dls", "select", [1]);
        new DisplayInputs(prefix, "syplo", "select", [1]);
        new DisplayInputs(prefix, "daloCommission", "select", [1]);
        new DisplayInputs(prefix, "hsgActionEligibility", "select", [1]);
        new DisplayInputs(prefix, "expulsionInProgress", "select", [1]);
        new DisplayInputs(prefix, "housingStatus", "select", [23, 24]);
        new DisplayInputs(prefix, "housingExperience", "select", [1]);
        new DisplayInputs(prefix, "housing", "select", [1]);
        new DisplayInputs(prefix, "domiciliation", "select", [1]);
        new DisplayInputs(prefix, "otherHelps", "checkbox");
        this.editElt("", "hsgHelps", "d-block");

        // Situation budgétaire
        this.editAmtPers("ressources");
        this.editAmtPers("charges");
        this.editAmtPers("debts");
        this.editAmtPers("repayment");

        // Gestion des situations individuelles
        let i = 0; // index person
        let cardSitProfElt = document.getElementById("accordion-sit_adm");
        let bthElts = cardSitProfElt.querySelectorAll("button.js-person");
        bthElts.forEach(btnElt => {
            prefix = "support_grp_supportPers_";
            // Situation administrative
            new DisplayInputs(prefix, i + "_sitAdm_nationality", "select", [2, 3, 4]);
            new DisplayInputs(prefix, i + "_sitAdm_paper", "select", [1]);
            new DisplayInputs(prefix, i + "_sitAdm_rightSocialSecu", "select", [1]);
            this.editElt(i, "_sitAdm_open_rights", "d-block");
            // Situation professionnelle
            new DisplayInputs(prefix, i + "_sitProf_profStatus", "select", [2, 3]);
            // Situation budgétaire
            new DisplayInputs(prefix, i + "_sitBudget_ressources", "select", [1]);
            new DisplayInputs(prefix, i + "_sitBudget_charges", "select", [1]);
            new DisplayInputs(prefix, i + "_sitBudget_debts", "select", [1]);
            new DisplayInputs(prefix, i + "_sitBudget_overIndebtRecord", "select", [1]);
            this.editElt(i, "_sitBudget_ressources_type", "d-table-row");
            this.editElt(i, "_sitBudget_charges_type", "d-table-row");
            this.editElt(i, "_sitBudget_debts_type", "d-block");
            this.selectTrElts(i, "ressources_type");
            this.selectTrElts(i, "charges_type");
            this.editAmt(prefix, i, "ressources");
            this.editAmt(prefix, i, "charges");
            i++;
        });

        document.getElementsByClassName("card").forEach(cardElt => {
            let btnPersonElts = cardElt.querySelectorAll("button.js-person");
            btnPersonElts.forEach(btnElt => {
                btnElt.addEventListener("click", this.activeBtn.bind(this, btnPersonElts, btnElt));
            });
        });

        let cardHeaderElts = document.querySelectorAll("div.card-header");
        cardHeaderElts.forEach(cardHeaderElt => {
            let spanFaElt = cardHeaderElt.querySelector("span.fa");
            cardHeaderElt.addEventListener("click", function () {
                cardHeaderElts.forEach(cardHeaderElt => {
                    cardHeaderElt.querySelector("span.fa").classList.replace("fa-chevron-down", "fa-chevron-up");
                });
                if (cardHeaderElt.classList.contains("collapsed")) {
                    spanFaElt.classList.replace("fa-chevron-up", "fa-chevron-down");
                } else {
                    spanFaElt.classList.replace("fa-chevron-down", "fa-chevron-up");
                }
            });
        });
    }

    //
    editElt(i, eltId, display) {
        let selectElt = document.getElementById("js-" + i + eltId);
        let checkboxElts = document.querySelectorAll(".js-" + i + eltId);
        selectElt.addEventListener("input", this.addOption.bind(this, selectElt, i, eltId, display));
        checkboxElts.forEach(checkbox => {
            checkbox.addEventListener("click", function () {
                this.displayNone(checkbox, display);
            }.bind(this));
            this.displayNone(checkbox, display);
        })
    }

    // Masque l'affichage de l'input
    displayNone(checkbox, display) {
        if (!checkbox.querySelector("input").checked) {
            checkbox.classList.replace(display, "d-none");
        }
    }

    // Active/Désactive le bouton d'une personne au clic
    activeBtn(btnElts, selectedBtn) {
        let active = false;
        if (selectedBtn.classList.contains("active")) {
            active = true;
        }
        btnElts.forEach(btn => {
            btn.classList.remove("active");
        });
        if (!active) {
            selectedBtn.classList.add("active");
        }
    }

    // Ajoute l'option sélectionnée de la liste déroulante
    addOption(selectElt, i, eltId, display) {
        let optionElts = selectElt.querySelectorAll("option");
        optionElts.forEach(option => {
            if (option.selected) {
                this.selectedOptionElt = document.getElementById("js-" + i + eltId + "-" + option.value);
                this.selectedOptionElt.querySelector("input").checked = "checked";
                this.selectedOptionElt.classList.replace("d-none", display);
                if (this.selectedOptionElt.id === "js-sitSocial_specifity-1") {
                    new DisplayInputs("support_grp_sitSocial_", "speAnimal", "checkbox");
                }
                if (this.selectedOptionElt.id === "js-sitSocial_specifity-98") {
                    new DisplayInputs("support_grp_sitSocial_", "speOther", "checkbox");
                }
            }
        });
        // Replace le select sur l'option par défaut
        window.setTimeout(function () {
            selectElt.querySelector(".default").selected = "selected";
            if (display === "d-table-row") {
                this.selectedOptionElt.querySelector("input[type='text']").focus();
            }
        }.bind(this), 200);
    }

    // Sélectionn toutes les ligne d'un tableau
    selectTrElts(i, type) {
        let trElts = document.querySelectorAll(".js-" + i + "_sitBudget_" + type);
        trElts.forEach(trElt => {
            let labelElt = trElt.querySelector("label");
            if (labelElt && !labelElt.classList.contains("js-noText")) {
                trElt.querySelector("td").innerHTML += labelElt.textContent;
            }
            trElt.querySelector("button.js-remove").addEventListener("click", function (e) {
                e.preventDefault();
                this.deleteTr(trElt, i);
            }.bind(this));
        });
    }

    // Supprime la ligne correspondant dans le tableau
    deleteTr(trElt, i) {
        let checkboxElt = trElt.querySelector("input[type='checkbox']");
        checkboxElt.removeAttribute("checked");
        checkboxElt.value = "0";
        trElt.querySelectorAll("input[type='text']").forEach(inputElt => {
            inputElt.value = null;
        });
        trElt.classList.replace("d-table-row", "d-none");
        this.updateSumAmt(i, "ressources");
        this.updateSumAmt(i, "charges");
    }

    // Met à jour la somme des montants après la saisie d'un input
    editAmt(prefix, i, type) {
        let inputElts = document.getElementById("collapse-sit_budget-" + i).querySelectorAll("input.js-" + type);
        inputElts.forEach(inputElt => {
            inputElt.addEventListener("input", function () {
                document.getElementById(prefix + i + "_sitBudget_" + type + "Amt").value = this.getSumAmts(inputElts);
                this.updateAmtGrp(type);
            }.bind(this));
        });
    }

    // Retourne la somme des montants
    getSumAmts(inputElts) {
        let sum = 0;
        inputElts.forEach(inputElt => {
            let value = parseInt(inputElt.value);
            if (value) {
                sum += value;
            }
        });
        return sum;
    }

    // Met à jour la somme des montants de la personne
    updateSumAmt(i, type) {
        let inputElts = document.getElementById("collapse-sit_budget-" + i).querySelectorAll("input.js-" + type);
        document.getElementById("support_grp_supportPers_" + i + "_sitBudget_" + type + "Amt").value = this.getSumAmts(inputElts);
    }

    // Met à jour le montant total du groupe lorsque modification des montants individuels
    editAmtPers(type) {
        let amountElts = document.querySelectorAll(".js-" + type + "Amt");
        amountElts.forEach(amountElt => {
            amountElt.addEventListener("input", this.updateAmtGrp.bind(this, type));
        });
    }

    // Met à jour le montant total du groupe (ressources, charges ou dettes)
    updateAmtGrp(type) {
        let amountGrp = 0;
        let amountElts = document.querySelectorAll(".js-" + type + "Amt");
        amountElts.forEach(amountElt => {
            let amountInt = parseInt(amountElt.value);
            if (amountInt > 0) {
                amountGrp += amountInt;
            }
        });
        document.getElementById(type + "GrpAmt").textContent = amountGrp;

        let ressourcesGrpAmt = parseInt(document.getElementById("ressourcesGrpAmt").textContent);
        let chargesGrpAmt = parseInt(document.getElementById("chargesGrpAmt").textContent);
        let repaymentGrpAmt = parseInt(document.getElementById("repaymentGrpAmt").textContent);
        let budgetBalanceGrpAmt = ressourcesGrpAmt - chargesGrpAmt - repaymentGrpAmt;
        document.getElementById("budgetBalanceGrpAmt").textContent = budgetBalanceGrpAmt;
    }
}