import DisplayInputs from "../utils/displayInputs";

// Evaluation sociale
export default class evaluation {

    constructor() {
        this.selectedOptionElt;
        this.evalBudgetElt = document.getElementById("accordion-parent-eval_budget");
        this.init();
    }

    init() {
        let $evalGroup = "evaluation_group_";
        let prefix;
        // Evaluation sociale
        prefix = $evalGroup + "evalSocialGroup_";
        new DisplayInputs(prefix, "animal", "select", [1]);
        // Evaluation familiale
        prefix = $evalGroup + "evalFamilyGroup_";
        new DisplayInputs(prefix, "famlReunification", "select", [1, 3, 4, 5]);
        prefix = $evalGroup + "evalHousingGroup_";
        // Evaluation liée au logement
        // new DisplayInputs(prefix, "housingAccessType", "select", [1, 2, 3, 4, 5, 6, 7, 8, 9]);
        new DisplayInputs(prefix, "housingStatus", "select", [200, 201, 202, 203, 204, 205, 206, 207, 300, 301, 302, 303, 304]);
        new DisplayInputs(prefix, "siaoRequest", "select", [1]);
        new DisplayInputs(prefix, "socialHousingRequest", "select", [1]);
        new DisplayInputs(prefix, "syplo", "select", [1]);
        new DisplayInputs(prefix, "daloCommission", "select", [1]);
        new DisplayInputs(prefix, "daloTribunalAction", "select", [1]);
        new DisplayInputs(prefix, "collectiveAgreementHousing", "select", [1]);
        new DisplayInputs(prefix, "hsgActionEligibility", "select", [1]);
        new DisplayInputs(prefix, "expulsionInProgress", "select", [1]);
        new DisplayInputs(prefix, "housingExperience", "select", [1]);
        new DisplayInputs(prefix, "housing", "select", [1]);
        new DisplayInputs(prefix, "domiciliation", "select", [1]);
        this.editElt("", "hsgHelps", "d-table-row");
        this.selectTrElts("eval_housing", "evalHousingGroup", "", "hsgHelps");

        // Evaluation budgétaire
        this.editAmtPers("resources");
        this.editAmtPers("charges");
        this.editAmtPers("debts");
        this.editAmtPers("repayment");

        let evalPerson = "evaluation_group_evaluationPeople_";
        let i = 0; // index person
        // Evaluation situation initiale individuelle
        document.getElementById("accordion-init_eval").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_initEvalPerson_rightSocialSecurity", "select", [1, 3]);
            new DisplayInputs(evalPerson, i + "_initEvalPerson_profStatus", "select", [3, 5, 8]);
            new DisplayInputs(evalPerson, i + "_initEvalPerson_resources", "select", [1, 3]);
            new DisplayInputs(evalPerson, i + "_initEvalPerson_debts", "select", [1]);
            this.editElt(i, "_initEvalPerson_resources_type", "d-table-row");
            this.selectTrElts("init_eval", "initEvalPerson", i, "resources_type");
            this.editAmt(evalPerson, "init_eval", "initEvalPerson", i, "resources");
            i++;
        });
        // Evaluation sociale individuelle
        i = 0;
        document.getElementById("accordion-eval_social").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalSocialPerson_rightSocialSecurity", "select", [1, 3]);
            new DisplayInputs(evalPerson, i + "_evalSocialPerson_healthProblem", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalSocialPerson_careSupport", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalSocialPerson_violenceVictim", "select", [1]);
            this.editElt(i, "_evalSocialPerson_healthProblemType", "d-table-row");
            this.selectTrElts("eval_social", "evalSocialPerson", i, "healthProblemType");
            i++;
        });
        // Evaluation administrative individuelle
        i = 0;
        document.getElementById("accordion-eval_adm").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalAdmPerson_nationality", "select", [2, 3, 4]);
            new DisplayInputs(evalPerson, i + "_evalAdmPerson_paper", "select", [1, 3]);
            new DisplayInputs(evalPerson, i + "_evalAdmPerson_paperType", "select", [20, 21, 22, 30, 31, 97]);
            new DisplayInputs(evalPerson, i + "_evalAdmPerson_asylumBackground", "select", [1]);
            i++;
        });
        // Evaluation familiale individuelle
        i = 0;
        document.getElementById("accordion-eval_family").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalFamilyPerson_unbornChild", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalFamilyPerson_protectiveMeasure", "select", [1, 3]);
            i++;
        });
        // Evaluation professionnelle individuelle
        i = 0;
        document.getElementById("accordion-eval_prof").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalProfPerson_profStatus", "select", [3, 5, 8]);
            new DisplayInputs(evalPerson, i + "_evalProfPerson_rqth", "select", [1]);
            i++;
        });
        // Evaluation budgétaire individuelle
        i = 0;
        document.getElementById("accordion-" + "eval_budget").querySelectorAll("button.js-person").forEach(btnElt => {
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_resources", "select", [1, 3]);
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_charges", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_debts", "select", [1]);
            new DisplayInputs(evalPerson, i + "_evalBudgetPerson_overIndebtRecord", "select", [1]);
            this.editElt(i, "_evalBudgetPerson_resources_type", "d-table-row");
            this.editElt(i, "_evalBudgetPerson_charges_type", "d-table-row");
            this.editElt(i, "_evalBudgetPerson_debts_type", "d-table-row");
            this.selectTrElts("eval_budget", "evalBudgetPerson", i, "resources_type");
            this.selectTrElts("eval_budget", "evalBudgetPerson", i, "charges_type");
            this.selectTrElts("eval_budget", "evalBudgetPerson", i, "debts_type");
            this.editAmt(evalPerson, "eval_budget", "evalBudgetPerson", i, "resources");
            this.editAmt(evalPerson, "eval_budget", "evalBudgetPerson", i, "charges");
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
        let inputElts = document.querySelectorAll(".js-" + i + eltId);
        selectElt.addEventListener("input", this.addOption.bind(this, selectElt, i, eltId, display));
        inputElts.forEach(inputElt => {
            inputElt.addEventListener("click", function () {
                this.displayNone(inputElt, display);
            }.bind(this));
            this.displayNone(inputElt, display);
        })
    }

    // Masque l'affichage de l'input
    displayNone(inputElt, display) {
        if (inputElt.querySelector("input").value != 1) {
            inputElt.classList.replace(display, "d-none");
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
                this.selectedOptionElt.querySelector("input").value = 1;
                this.selectedOptionElt.classList.replace("d-none", display);
            }
        });
        // Remplace le select sur l'option par défaut
        window.setTimeout(function () {
            selectElt.querySelector("option").selected = "selected";
            let inputTextElt = this.selectedOptionElt.querySelector("input[type='text']");
            if (display === "d-table-row" && inputTextElt) {
                inputTextElt.focus();
            }
        }.bind(this), 200);
    }

    // Sélectionne toutes les lignes d'un tableau
    selectTrElts(collapseId, entity, i, type) {
        let trElts = document.querySelectorAll(".js-" + i + "_" + entity + "_" + type);
        trElts.forEach(trElt => {
            trElt.querySelector("button.js-remove").addEventListener("click", function (e) {
                e.preventDefault();
                this.deleteTr(collapseId, entity, i, trElt);
            }.bind(this));
        });
    }

    // Supprime la ligne correspondant dans le tableau
    deleteTr(collapseId, entity, i, trElt) {
        trElt.querySelectorAll("input").forEach(inputElt => {
            inputElt.value = null;
        });
        trElt.classList.replace("d-table-row", "d-none");
        if (entity === "evalBudgetPerson") {
            this.updateSumAmt(collapseId, entity, i, "resources");
            this.updateSumAmt(collapseId, entity, i, "charges");
        }
    }

    // Met à jour la somme des montants après la saisie d'un input
    editAmt(prefix, collapseId, entity, i, type) {
        let inputElts = document.getElementById("collapse-" + collapseId + "-" + i).querySelectorAll("input.js-" + type);
        inputElts.forEach(inputElt => {
            inputElt.addEventListener("input", function () {
                document.getElementById(prefix + i + "_" + entity + "_" + type + "Amt").value = this.getSumAmts(inputElts);
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
    updateSumAmt(collapseId, entity, i, type) {
        let inputElts = document.getElementById("collapse-" + collapseId + "-" + i).querySelectorAll("input.js-" + type);
        document.getElementById("evaluation_group_evaluationPeople_" + i + "_" + entity + "_" + type + "Amt").value = this.getSumAmts(inputElts);
        this.updateAmtGroup(type);
    }

    // Met à jour le montant total du groupe lorsque modification des montants individuels
    editAmtPers(type) {
        this.evalBudgetElt.querySelectorAll(".js-" + type + "Amt").forEach(amountElt => {
            amountElt.addEventListener("input", this.updateAmtGroup.bind(this, type));
        });
    }

    // Met à jour le montant total du groupe (resources, charges ou dettes)
    updateAmtGroup(type) {
        let amountGroup = 0;
        this.evalBudgetElt.querySelectorAll(".js-" + type + "Amt").forEach(amountElt => {
            let amountInt = parseInt(amountElt.value);
            if (amountInt > 0) {
                amountGroup += amountInt;
            }
        });

        document.getElementById(type + "GroupAmt").textContent = amountGroup;

        let resourcesGroupAmt = parseInt(document.getElementById("resourcesGroupAmt").textContent);
        let chargesGroupAmt = parseInt(document.getElementById("chargesGroupAmt").textContent);
        let repaymentGroupAmt = parseInt(document.getElementById("repaymentGroupAmt").textContent);
        let budgetBalanceGroupAmt = resourcesGroupAmt - chargesGroupAmt - repaymentGroupAmt;

        document.getElementById("budgetBalanceGroupAmt").textContent = budgetBalanceGroupAmt;
    }
}