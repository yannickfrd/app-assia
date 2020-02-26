import DisplayInputs from "../utils/displayInputs";

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

        this.evalBudgetResourcesAmtElts = this.evalBudgetElt.querySelectorAll(".js-resourcesAmt");
        this.evalBudgetChargesAmtElts = this.evalBudgetElt.querySelectorAll(".js-chargesAmt");
        this.evalBudgetDebtsAmtElts = this.evalBudgetElt.querySelectorAll(".js-debtsAmt");
        this.evalBudgetRepaymentAmtElts = this.evalBudgetElt.querySelectorAll(".js-repaymentAmt");
        this.evalBudgetBudgetBalancAmtElts = this.evalBudgetElt.querySelectorAll(".js-budgetBalanceAmt");

        this.resourcesAmtElts = document.querySelectorAll("input[data-id='resourcesAmt']");

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

        document.querySelectorAll(".js-evalGroup[data-supportId]").forEach(elt => {
            this.initSelects(elt);
            this.initInputs(elt);
        })
        document.querySelectorAll(".collapse[data-supportId]").forEach(elt => {
            this.initSelects(elt);
            this.initInputs(elt);
        })
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
        // js-evalHousingGroup_hsgHelps d-table-row"
        // js-_evalHousingGroup_hsgHelps

        this.selectTrElts("eval_housing", "evalHousingGroup", "", "hsgHelps");
    }

    // Evaluation situation initiale individuelle
    initEvalPerson(prefix) {
        let length = document.getElementById("accordion-init_eval").querySelectorAll("button.js-person").length;
        for (let i = 0; i < length; i++) {
            new DisplayInputs(prefix, i + "_initEvalPerson_rightSocialSecurity", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_initEvalPerson_profStatus", "select", [3, 5, 8]);
            new DisplayInputs(prefix, i + "_initEvalPerson_resources", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_initEvalPerson_debts", "select", [1]);
            this.editElt(i, "_initEvalPerson_resources_type", "d-table-row");
            this.selectTrElts("init_eval", "initEvalPerson", i, "resources_type");
            this.editAmt(prefix, "init_eval", "initEvalPerson", i, "resources");
        }
    }

    // Evaluation sociale individuelle
    evalSocialPerson(prefix) {
        let length = document.getElementById("accordion-eval_social").querySelectorAll("button.js-person").length;
        for (let i = 0; i < length; i++) {
            new DisplayInputs(prefix, i + "_evalSocialPerson_rightSocialSecurity", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_evalSocialPerson_healthProblem", "select", [1]);
            new DisplayInputs(prefix, i + "_evalSocialPerson_careSupport", "select", [1]);
            new DisplayInputs(prefix, i + "_evalSocialPerson_violenceVictim", "select", [1]);
            this.editElt(i, "_evalSocialPerson_healthProblemType", "d-table-row");
            this.selectTrElts("eval_social", "evalSocialPerson", i, "healthProblemType");
        }
    }

    // Evaluation administrative individuelle
    evalAdmPerson(prefix) {
        let length = document.getElementById("accordion-eval_adm").querySelectorAll("button.js-person").length;
        for (let i = 0; i < length; i++) {
            new DisplayInputs(prefix, i + "_evalAdmPerson_nationality", "select", [2, 3, 4]);
            new DisplayInputs(prefix, i + "_evalAdmPerson_paper", "select", [1, 3]);
            new DisplayInputs(prefix, i + "_evalAdmPerson_paperType", "select", [20, 21, 22, 30, 31, 97]);
            new DisplayInputs(prefix, i + "_evalAdmPerson_asylumBackground", "select", [1]);
        }
    }

    // Evaluation familiale individuelle
    evalFamily(prefix) {
        let length = document.getElementById("accordion-eval_family").querySelectorAll("button.js-person").length;
        for (let i = 0; i < length; i++) {
            new DisplayInputs(prefix, i + "_evalFamilyPerson_unbornChild", "select", [1]);
            new DisplayInputs(prefix, i + "_evalFamilyPerson_protectiveMeasure", "select", [1, 3]);
        }
    }

    // Evaluation professionnelle individuelle
    evalProfPerson(prefix) {
        let length = document.getElementById("accordion-eval_prof").querySelectorAll("button.js-person").length;
        for (let i = 0; i < length; i++) {
            new DisplayInputs(prefix, i + "_evalProfPerson_profStatus", "select", [3, 5, 8]);
            new DisplayInputs(prefix, i + "_evalProfPerson_rqth", "select", [1]);
        }
    }

    // Evaluation budgétaire individuelle
    evalBudgetPerson(prefix) {
        let length = document.getElementById("accordion-eval_budget").querySelectorAll("button.js-person").length;
        let entity = "evalBudgetPerson";
        for (let i = 0; i < length; i++) {
            new DisplayInputs(prefix, i + "_evalBudgetPerson_resources", "select", [1, 3]);
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
        }
    }


    // Initialise les Inputs pour les éléments de la situations initiale
    initInputs(elt) {
        elt.querySelectorAll("input.js-initEval").forEach(inputElt => {
            inputElt.setAttribute("data-supportId", elt.getAttribute("data-supportId"));
            if (!inputElt.value) {
                inputElt.classList.add("border-warning");
            }
            inputElt.addEventListener("change", this.changeInput.bind(this, inputElt));
        });
    }

    // Initialise les Selects pour les éléments de la situations initiale
    initSelects(elt) {
        elt.querySelectorAll("select.js-initEval").forEach(selectElt => {
            selectElt.setAttribute("data-supportId", elt.getAttribute("data-supportId"));
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
            let supportPersonId = elt.getAttribute("data-supportId");
            document.querySelectorAll("input[data-id='" + dataId + "'][data-supportId='" + supportPersonId + "']").forEach(inputElt => {
                if (inputElt.value === "") {
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
            document.querySelectorAll("select[data-id='" + elt.getAttribute("data-id") + "'][data-supportId='" + elt.getAttribute("data-supportId") + "']").forEach(selectElt => {
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
            if (optionElt.selected) {
                value = optionElt.value;
            }
        });
        return value;
    }

    // Modifie l'option d'un Select
    setOption(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.value === value) {
                option.setAttribute("selected", "selected");
            } else {
                option.selected = "";
            }
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
                this.trElt = document.getElementById("js-" + i + eltId + "-" + option.value);
                if (selectElt.getAttribute("data-id") === "resourcesType" && this.editMode === "false") {
                    let dataId = this.trElt.getAttribute("data-id");
                    let supportPersonId = this.trElt.getAttribute("data-supportId");
                    let trElts = document.querySelectorAll("tr[data-id='" + dataId + "'][data-supportId='" + supportPersonId + "']");
                    trElts.forEach(trElt => {
                        trElt.querySelector("input").value = 1;
                        trElt.classList.replace("d-none", display);
                    })
                } else {
                    this.trElt.querySelector("input").value = 1;
                    this.trElt.classList.replace("d-none", display);
                }
            }
        });
        // Remplace le select sur l'option par défaut
        window.setTimeout(function () {
            selectElt.querySelector("option").selected = "selected";
            let inputTextElt = this.trElt.querySelector("input[type='text']");
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
                this.removeTr(collapseId, entity, i, trElt);
            }.bind(this));
        });
    }

    // Retire la ligne correspondante dans le tableau
    removeTr(collapseId, entity, i, trElt) {
        trElt.querySelectorAll("input").forEach(inputElt => {
            if (inputElt.getAttribute("type") === "number")
                inputElt.value = 0;
            else {
                inputElt.value = null;
            }
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
        let amtElt = document.getElementById(prefix + i + "_" + entity + "_" + type + "Amt");

        inputElts.forEach(inputElt => {
            inputElt.addEventListener("input", function () {
                amtElt.value = this.getSumAmts(inputElts);
                this.updateAmtGroup(type);
            }.bind(this));
        });

        inputElts.forEach(inputElt => {
            inputElt.addEventListener("focusout", function () {
                this.resourcesAmtElts.forEach(ressourcesAmtElt => {
                    ressourcesAmtElt.click();
                })
            }.bind(this));
        });

        amtElt.addEventListener("click", function () {
            let sumAlts = this.getSumAmts(inputElts);
            if (sumAlts != 0) {
                amtElt.value = sumAlts;
                this.updateAmtGroup(type);
            }
        }.bind(this));
    }

    // Retourne la somme des montants
    getSumAmts(inputElts) {
        let array = [];
        inputElts.forEach(inputElt => {
            if (inputElt.value) {
                array.push(parseInt(inputElt.value));
            }
        });
        return array.reduce((a, b) => a + b, 0);
    }

    // Met à jour la somme des montants de la personne
    updateSumAmt(collapseId, entity, i, type) {
        let inputElts = document.getElementById("collapse-" + collapseId + "-" + i).querySelectorAll("input.js-" + type);
        document.getElementById("evaluation_group_evaluationPeople_" + i + "_" + entity + "_" + type + "Amt").value = this.getSumAmts(inputElts);
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
}