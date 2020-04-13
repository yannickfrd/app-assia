import DisplayInputs from "../utils/displayInputs";
import ValidationInput from "../utils/validationInput";

// Evaluation sociale
export default class evaluation {

    constructor() {
        this.evalBudgetElt = document.getElementById("accordion-parent-eval_budget");
        this.prefix = "evaluation_group_";
        this.editMode = document.querySelector("div[data-editMode]").getAttribute("data-editMode");

        this.resourcesGroupAmtElt = document.getElementById("resourcesGroupAmt");
        this.chargesGroupAmtElt = document.getElementById("chargesGroupAmt");
        this.debtsGroupAmtElt = document.getElementById("debtsGroupAmt");
        this.repaymentGroupAmtElt = document.getElementById("repaymentGroupAmt");
        this.budgetBalanceGroupAmtElt = document.getElementById("budgetBalanceGroupAmt");

        this.moneyElts = document.querySelectorAll(".js-money");

        this.evalBudgetResourcesAmtElts = this.evalBudgetElt.querySelectorAll(".js-resourcesAmt");
        this.evalBudgetChargesAmtElts = this.evalBudgetElt.querySelectorAll(".js-chargesAmt");
        this.evalBudgetDebtsAmtElts = this.evalBudgetElt.querySelectorAll(".js-debtsAmt");
        this.evalBudgetRepaymentAmtElts = this.evalBudgetElt.querySelectorAll(".js-repaymentAmt");
        this.evalBudgetBudgetBalancAmtElts = this.evalBudgetElt.querySelectorAll(".js-budgetBalanceAmt");

        this.resourcesAmtElts = document.querySelectorAll("input[data-id='resourcesAmt']");

        this.now = new Date();
        this.dateElts = document.querySelectorAll("input[type='date']");

        this.validationInput = new ValidationInput();
        this.init();
    }

    init() {
        this.evalSocialGroup();
        this.evalFamilyGroup();
        this.evalBudgetGroup();
        this.evalHousingGroup();

        let prefix = this.prefix + "evaluationPeople_";
        this.initEvalPerson(prefix);
        this.evalSocialPerson(prefix);
        this.evalFamily(prefix);
        this.evalProfPerson(prefix);
        this.evalBudgetPerson(prefix);
        this.evalAdmPerson(prefix);

        document.getElementsByClassName("card").forEach(cardElt => {
            let btnPersonElts = cardElt.querySelectorAll("button.js-person");
            btnPersonElts.forEach(btnElt => {
                btnElt.addEventListener("click", this.activeBtn.bind(this, btnPersonElts, btnElt));
            });
        });

        document.querySelectorAll(".js-evalGroup[data-support-id]").forEach(elt => {
            this.initSelects(elt);
            this.initInputs(elt);
        })
        document.querySelectorAll(".collapse[data-support-id]").forEach(elt => {
            this.initSelects(elt);
            this.initInputs(elt);
        })

        this.moneyElts.forEach(moneyElt => {
            moneyElt.addEventListener("change", this.checkMoney.bind(this, moneyElt));
        });
        this.dateElts.forEach(dateElt => {
            dateElt.addEventListener("focusout", this.checkDate.bind(this, dateElt));
        });
    }


    // Evaluation sociale du groupe
    evalSocialGroup() {
        new DisplayInputs(this.prefix + "evalSocialGroup_", "animal", "select", [1]);
    }

    // Evaluation familiale du groupe
    evalFamilyGroup() {
        new DisplayInputs(this.prefix + "evalFamilyGroup_", "famlReunification", "select", [1, 3, 4, 5]);
    }

    // Evaluation budgétaire
    evalBudgetGroup() {
        this.editAmtPers("resources");
        this.editAmtPers("charges");
        this.editAmtPers("debts");
        this.editAmtPers("repayment");
    }

    // Evaluation liée au logement
    evalHousingGroup() {
        let prefix = this.prefix + "evalHousingGroup_";
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
        new DisplayInputs(prefix, "domiciliation", "select", [1]);
        this.editElt("", "_evalHousingGroup_hsgHelps", "d-table-row");
        this.selectTrElts("eval_housing", "evalHousingGroup", "", "hsgHelps");
    }

    // Evaluation situation initiale individuelle
    initEvalPerson(prefix) {
        document.getElementById("accordion-init_eval").querySelectorAll("button.js-person").forEach(personElt => {
            let i = personElt.getAttribute("data-key");
            new DisplayInputs(prefix, i + "_initEvalPerson_rightSocialSecurity", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_initEvalPerson_profStatus", "select", [3, 5, 8]);
            new DisplayInputs(prefix, i + "_initEvalPerson_resources_resources", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_initEvalPerson_debts", "select", [1]);
            this.editElt(i, "_initEvalPerson_resources_type", "d-table-row");
            this.selectTrElts("init_eval", "initEvalPerson", i, "resources_type");
            this.editAmt(prefix, "init_eval", "initEvalPerson", i, "resources");
        });
    }

    // Evaluation sociale individuelle
    evalSocialPerson(prefix) {
        document.getElementById("accordion-eval_social").querySelectorAll("button.js-person").forEach(personElt => {
            let i = personElt.getAttribute("data-key");
            new DisplayInputs(prefix, i + "_evalSocialPerson_rightSocialSecurity", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_evalSocialPerson_healthProblem", "select", [1]);
            new DisplayInputs(prefix, i + "_evalSocialPerson_careSupport", "select", [1]);
            new DisplayInputs(prefix, i + "_evalSocialPerson_violenceVictim", "select", [1]);
            this.editElt(i, "_evalSocialPerson_healthProblemType", "d-table-row");
            this.selectTrElts("eval_social", "evalSocialPerson", i, "healthProblemType");
        });
    }

    // Evaluation administrative individuelle
    evalAdmPerson(prefix) {
        document.getElementById("accordion-eval_adm").querySelectorAll("button.js-person").forEach(personElt => {
            let i = personElt.getAttribute("data-key");
            new DisplayInputs(prefix, i + "_evalAdmPerson_nationality", "select", [2, 3, 4]);
            new DisplayInputs(prefix, i + "_evalAdmPerson_paper", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_evalAdmPerson_paperType", "select", [20, 21, 22, 30, 31, 97]);
            new DisplayInputs(prefix, i + "_evalAdmPerson_asylumBackground", "select", [1]);
        });
    }

    // Evaluation familiale individuelle
    evalFamily(prefix) {
        document.getElementById("accordion-eval_family").querySelectorAll("button.js-person").forEach(personElt => {
            let i = personElt.getAttribute("data-key");
            new DisplayInputs(prefix, i + "_evalFamilyPerson_unbornChild", "select", [1]);
            new DisplayInputs(prefix, i + "_evalFamilyPerson_protectiveMeasure", "select", [1, 3]);

        });
    }

    // Evaluation professionnelle individuelle
    evalProfPerson(prefix) {
        document.getElementById("accordion-eval_prof").querySelectorAll("button.js-person").forEach(personElt => {
            let i = personElt.getAttribute("data-key");
            new DisplayInputs(prefix, i + "_evalProfPerson_profStatus", "select", [3, 5, 8]);
            new DisplayInputs(prefix, i + "_evalProfPerson_transportMeansType", "select", [1, 2, 3]);
            new DisplayInputs(prefix, i + "_evalProfPerson_rqth", "select", [1]);
        });
    }

    // Evaluation budgétaire individuelle
    evalBudgetPerson(prefix) {
        let entity = "evalBudgetPerson";
        document.getElementById("accordion-eval_budget").querySelectorAll("button.js-person").forEach(personElt => {
            let i = personElt.getAttribute("data-key");
            new DisplayInputs(prefix, i + "_evalBudgetPerson_resources_resources", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_evalBudgetPerson_charges", "select", [1]);
            new DisplayInputs(prefix, i + "_evalBudgetPerson_debts", "select", [1]);
            new DisplayInputs(prefix, i + "_evalBudgetPerson_overIndebtRecord", "select", [1]);
            this.editElt(i, "_evalBudgetPerson_resources_type", "d-table-row");
            this.editElt(i, "_evalBudgetPerson_charges_type", "d-table-row");
            this.editElt(i, "_evalBudgetPerson_debts_type", "d-table-row");
            this.selectTrElts("eval_budget", entity, i, "resources_type");
            this.selectTrElts("eval_budget", entity, i, "charges_type");
            this.selectTrElts("eval_budget", entity, i, "debts_type");
            this.editAmt(prefix, "eval_budget", entity, i, "resources");
            this.editAmt(prefix, "eval_budget", entity, i, "charges");
        });
    }

    // Initialise les Inputs pour les éléments de la situations initiale
    initInputs(elt) {
        elt.querySelectorAll("input.js-initEval").forEach(inputElt => {
            inputElt.setAttribute("data-support-id", elt.getAttribute("data-support-id"));
            if (!inputElt.value) {
                inputElt.classList.add("border-warning");
            }
            inputElt.addEventListener("change", this.changeInput.bind(this, inputElt));
        });
    }

    // Initialise les Selects pour les éléments de la situations initiale
    initSelects(elt) {
        elt.querySelectorAll("select.js-initEval").forEach(selectElt => {
            selectElt.setAttribute("data-support-id", elt.getAttribute("data-support-id"));
            if (!this.getOption(selectElt)) {
                selectElt.classList.add("border-warning");
            }
            selectElt.addEventListener("change", this.changeSelect.bind(this, selectElt));
        });
    }

    // Si modification d'un Input, met à jour l'autre champ semblable si vide
    changeInput(elt) {
        if (elt.value) {
            let dataId = elt.getAttribute("data-id");
            let supportPersonId = elt.getAttribute("data-support-id");
            document.querySelectorAll("input[data-id='" + dataId + "'][data-support-id='" + supportPersonId + "']").forEach(inputElt => {
                if (!inputElt.value && this.editMode === "false") {
                    inputElt.value = elt.value;
                    inputElt.classList.remove("border-warning");
                }
            });
            elt.classList.remove("border-warning");
        } else {
            elt.classList.add("border-warning");
        }
    }

    // Si modification d'un Select, met à jour l'autre champ semblable si vide
    changeSelect(elt) {
        let optionSelected = this.getOption(elt);
        if (optionSelected) {
            document.querySelectorAll("select[data-id='" + elt.getAttribute("data-id") + "'][data-support-id='" + elt.getAttribute("data-support-id") + "']").forEach(selectElt => {
                if (!selectElt.querySelector("option[selected]")) {
                    this.setOption(selectElt, optionSelected);
                    selectElt.classList.remove("border-warning");
                    selectElt.click();
                }
            });
            elt.classList.remove("border-warning");
        } else {
            elt.classList.add("border-warning");
        }
    }

    // Donne l'option sélectionné d'un Select
    getOption(selectElt) {
        let value = null;
        selectElt.querySelectorAll("option").forEach(optionElt => {
            optionElt.selected ? value = optionElt.value : null;
        });
        return value;
    }

    // Modifie l'option d'un Select
    setOption(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(option => {
            option.value === value ? option.setAttribute("selected", "selected") : option.selected = "";
        });
    }


    // Masque ou affiche un élement
    editElt(i, eltId, display) {
        let selectElt = document.getElementById("js-" + i + eltId);
        let inputElts = document.querySelectorAll(".js-" + i + eltId);
        selectElt.addEventListener("change", this.addOption.bind(this, selectElt, i, eltId, display));
        inputElts.forEach(inputElt => {
            inputElt.addEventListener("click", e => {
                this.displayNone(inputElt, display);
            });
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
                this.trElt = document.getElementById("js-" + i + eltId + "-" + option.value);
                let dataId = this.trElt.getAttribute("data-id");
                let supportPersonId = this.trElt.getAttribute("data-support-id");
                let trElts = document.querySelectorAll("tr[data-id='" + dataId + "'][data-support-id='" + supportPersonId + "']");
                if (selectElt.getAttribute("data-id") === "resourcesType" && this.editMode === "false") {
                    trElts.forEach(trElt => {
                        trElt.querySelector("input[type='number']").value = 1;
                        trElt.classList.replace("d-none", display);
                    })
                } else {
                    this.trElt.querySelector("input[type='number']").value = 1;
                    this.trElt.classList.replace("d-none", display);
                }
            }
            // Met tous les autres inputs du tableau à 0 si vide
            document.querySelectorAll(".js-" + i + eltId).forEach(trElt => {
                let inputElt = trElt.querySelector("input[type='number']");
                if (!inputElt.value) {
                    inputElt.value = 0;
                }
            });
        });
        // Remplace le select sur l'option par défaut
        window.setTimeout(e => {
            selectElt.querySelector("option").selected = "selected";
            let inputTextElt = this.trElt.querySelector("input[type='text']");
            if (display === "" && inputTextElt) {
                inputTextElt.focus();
            }
        }, 200);
    }

    // Sélectionne toutes les lignes d'un tableau
    selectTrElts(collapseId, entity, i, type) {
        let trElts = document.querySelectorAll(".js-" + i + "_" + entity + "_" + type);
        trElts.forEach(trElt => {
            trElt.querySelector("button.js-remove").addEventListener("click", e => {
                e.preventDefault();
                this.removeTr(collapseId, entity, i, trElt);
            });
        });
    }

    // Retire la ligne correspondante dans le tableau
    removeTr(collapseId, entity, i, trElt) {
        trElt.querySelectorAll("input").forEach(inputElt => {
            inputElt.getAttribute("type") === "number" ? inputElt.value = 0 : inputElt.value = null;
        });
        trElt.classList.replace("d-table-row", "d-none");
        if (entity === "evalBudgetPerson" || entity === "initEvalPerson") {
            this.updateSumAmt(collapseId, entity, i, "resources");
        }
        if (entity === "evalBudgetPerson") {
            this.updateSumAmt(collapseId, entity, i, "charges");
        }
    }

    // Met à jour la somme des montants après la saisie d'un input
    editAmt(prefix, collapseId, entity, i, type) {
        let inputElts = document.getElementById("collapse-" + collapseId + "-" + i).querySelectorAll("input.js-" + type);
        let amtElt = document.getElementById(prefix + i + "_" + entity + (type === "resources" ? "_resources_" : "_") + type + "Amt");
        inputElts.forEach(inputElt => {
            inputElt.addEventListener("input", e => {
                amtElt.value = this.getSumAmts(inputElts);
                this.updateAmtGroup(type);
            });
        });

        inputElts.forEach(inputElt => {
            inputElt.addEventListener("focusout", e => {
                this.resourcesAmtElts.forEach(ressourcesAmtElt => {
                    ressourcesAmtElt.click();
                });
            });
        });

        if (amtElt) {
            amtElt.addEventListener("click", e => {
                let sumAlts = this.getSumAmts(inputElts);
                if (sumAlts != 0) {
                    amtElt.value = sumAlts;
                    this.updateAmtGroup(type);
                }
            });
        }
    }

    // Retourne la somme des montants
    getSumAmts(inputElts) {
        let array = [];
        inputElts.forEach(inputElt => {
            if (inputElt.value) {
                array.push(parseInt(inputElt.value));
            }
        });

        let sumAmts = array.reduce((a, b) => a + b, 0);

        if (!isNaN(sumAmts)) {
            return sumAmts;
        }
        return "Erreur";
    }

    // Met à jour la somme des montants de la personne
    updateSumAmt(collapseId, entity, i, type) {
        let inputElts = document.getElementById("collapse-" + collapseId + "-" + i).querySelectorAll("input.js-" + type);
        document.getElementById("evaluation_group_evaluationPeople_" + i + "_" + entity + (type === "resources" ? "_resources_" : "_") + type + "Amt").value = this.getSumAmts(inputElts);
        this.updateAmtGroup(type);
    }

    // Met à jour le montant total du groupe lors d'une modification des montants individuels
    editAmtPers(type) {
        this.amtElts(type).forEach(amountElt => {
            amountElt.addEventListener("input", this.updateAmtGroup.bind(this, type));
        });
    }

    // Met à jour le montant total du groupe (resources, charges ou dettes)
    updateAmtGroup(type) {
        let array = [];
        this.amtElts(type).forEach(amountElt => {
            if (amountElt.value) {
                array.push(parseInt(amountElt.value));
            }
        });

        this.groupAmtElt(type).textContent = array.reduce((a, b) => a + b, 0);

        this.budgetBalanceGroupAmtElt.textContent = parseInt(this.resourcesGroupAmtElt.textContent - this.chargesGroupAmtElt.textContent - this.repaymentGroupAmtElt.textContent);
    }

    groupAmtElt(type) {
        let groupAmtElt;
        switch (type) {
            case "resources":
                groupAmtElt = this.resourcesGroupAmtElt;
                break;
            case "charges":
                groupAmtElt = this.chargesGroupAmtElt;
                break;
            case "debts":
                groupAmtElt = this.debtsGroupAmtElt;
                break;
            case "repayment":
                groupAmtElt = this.repaymentGroupAmtElt;
                break;
        }
        return groupAmtElt;
    }

    amtElts(type) {
        let amtElts;
        switch (type) {
            case "resources":
                amtElts = this.evalBudgetResourcesAmtElts;
                break;
            case "charges":
                amtElts = this.evalBudgetChargesAmtElts;
                break;
            case "debts":
                amtElts = this.evalBudgetDebtsAmtElts;
                break;
            case "repayment":
                amtElts = this.evalBudgetRepaymentAmtElts;
                break;
        }
        return amtElts;
    }

    checkMoney(moneyElt) {
        moneyElt.value = moneyElt.value.replace(" ", "");
        if (Number(moneyElt.value) >= 0) {
            return this.validationInput.valid(moneyElt);
        }
        return this.validationInput.invalid(moneyElt, "Montant invalide.");
    }

    checkDate(dateElt) {
        let interval = Math.round((this.now - new Date(dateElt.value)) / (24 * 3600 * 1000));
        if ((dateElt.value && !Number.isInteger(interval)) || interval > (365 * 99) || interval < -(365 * 99)) {
            return this.validationInput.invalid(dateElt, "Date invalide.");
        }
        return this.validationInput.valid(dateElt);
    }
}