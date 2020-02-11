import DisplayInputs from "../utils/displayInputs";

// Evaluation sociale
export default class evaluation {

    constructor() {
        this.selectedOptionElt;
        this.init();
    }

    init() {
        let $evalGroup = "evaluation_group_";
        let prefix;
        // Evaluation sociale
        prefix = $evalGroup + "evalSocialGroup_";
        new DisplayInputs(prefix, "speAnimal", "checkbox");
        new DisplayInputs(prefix, "speOther", "checkbox");
        this.editElt("", "evalSocialGroup_specifity", "d-block");
        // Evaluation familiale
        prefix = $evalGroup + "evalFamilyGroup_";
        new DisplayInputs(prefix, "unbornChild", "select", [1]);
        new DisplayInputs(prefix, "famlReunification", "select", [1, 3, 4, 5]);
        prefix = $evalGroup + "evalHousingGroup_";
        // Evaluation liée au logement
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

        // Evaluation budgétaire
        this.editAmtPers("ressources");
        this.editAmtPers("charges");
        this.editAmtPers("debts");
        this.editAmtPers("repayment");

        let evalPerson = "evaluation_group_evaluationPeople_";
        let i = 0; // index person
        // Evaluation administrative
        document.getElementById("accordion-sit_adm").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalAdmPerson_nationality", "select", [2, 3, 4]);
            new DisplayInputs(evalPerson, i + "_evalAdmPerson_paper", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalAdmPerson_rightSocialSecu", "select", [1]);
            this.editElt(i, "_evalAdmPerson_open_rights", "d-block");
            i++;
        });
        // Evaluation professionnelle
        i = 0;
        document.getElementById("accordion-sit_prof").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalProfPerson_profStatus", "select", [2, 3]);
            i++;
        });
        // Evaluation budgétaire
        i = 0;
        document.getElementById("accordion-sit_budget").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_ressources", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_charges", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_debts", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_overIndebtRecord", "select", [1]);
            this.editElt(i, "_evalBudgetPerson_ressources_type", "d-table-row");
            this.editElt(i, "_evalBudgetPerson_charges_type", "d-table-row");
            this.editElt(i, "_evalBudgetPerson_debts_type", "d-block");
            this.selectTrElts(i, "ressources_type");
            this.selectTrElts(i, "charges_type");
            this.editAmt(evalPerson, i, "ressources");
            this.editAmt(evalPerson, i, "charges");
            i++;
        });

        document.getElementsByClassName("card").forEach(cardElt => {
            let btnPersonElts = cardElt.querySelectorAll("button.js-person");
            btnPersonElts.forEach(btnElt => {
                btnElt.addEventListener("click", this.activeBtn.bind(this, btnPersonElts, btnElt));
            });
        });
    }

    // Masque ou affiche un élement
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
                if (this.selectedOptionElt.id === "js-evalSocialGroup_specifity-1") {
                    new DisplayInputs("support_group_evalSocialGroup_", "speAnimal", "checkbox");
                }
                if (this.selectedOptionElt.id === "js-evalSocialGroup_specifity-98") {
                    new DisplayInputs("support_group_evalSocialGroup_", "speOther", "checkbox");
                }
            }
        });
        // Replace le select sur l'option par défaut
        window.setTimeout(function () {
            selectElt.querySelector("option").selected = "selected";
            if (display === "d-table-row") {
                this.selectedOptionElt.querySelector("input[type='text']").focus();
            }
        }.bind(this), 200);
    }

    // Sélectionn toutes les ligne d'un tableau
    selectTrElts(i, type) {
        let trElts = document.querySelectorAll(".js-" + i + "_evalBudgetPerson_" + type);
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
                document.getElementById(prefix + i + "_evalBudgetPerson_" + type + "Amt").value = this.getSumAmts(inputElts);
                this.updateAmtGroup(type);
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
        document.getElementById("support_group_supportPerson_" + i + "_evalBudgetPerson_" + type + "Amt").value = this.getSumAmts(inputElts);
    }

    // Met à jour le montant total du groupe lorsque modification des montants individuels
    editAmtPers(type) {
        let amountElts = document.querySelectorAll(".js-" + type + "Amt");
        amountElts.forEach(amountElt => {
            amountElt.addEventListener("input", this.updateAmtGroup.bind(this, type));
        });
    }

    // Met à jour le montant total du groupe (ressources, charges ou dettes)
    updateAmtGroup(type) {
        let amountGroup = 0;
        let amountElts = document.querySelectorAll(".js-" + type + "Amt");
        amountElts.forEach(amountElt => {
            let amountInt = parseInt(amountElt.value);
            if (amountInt > 0) {
                amountGroup += amountInt;
            }
        });
        document.getElementById(type + "GroupAmt").textContent = amountGroup;

        let ressourcesGroupAmt = parseInt(document.getElementById("ressourcesGroupAmt").textContent);
        let chargesGroupAmt = parseInt(document.getElementById("chargesGroupAmt").textContent);
        let repaymentGroupAmt = parseInt(document.getElementById("repaymentGroupAmt").textContent);
        let budgetBalanceGroupAmt = ressourcesGroupAmt - chargesGroupAmt - repaymentGroupAmt;
        document.getElementById("budgetBalanceGroupAmt").textContent = budgetBalanceGroupAmt;
    }
}