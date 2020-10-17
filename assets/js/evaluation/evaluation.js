import DisplayFields from "../utils/displayFields"
import ValidationForm from "../utils/validationForm"
import SelectType from '../utils/selectType'

/**
 * Evaluation sociale.
 */
export default class evaluation {

    constructor() {
        this.evalBudgetElt = document.getElementById("accordion-parent-eval_budget")
        this.prefix = "evaluation_"
        this.editMode = document.querySelector("div[data-edit-mode]").getAttribute("data-edit-mode")

        this.accordionElts = document.querySelectorAll('section.accordion')

        this.resourcesGroupAmtElt = document.getElementById("resourcesGroupAmt")
        this.chargesGroupAmtElt = document.getElementById("chargesGroupAmt")
        this.debtsGroupAmtElt = document.getElementById("debtsGroupAmt")
        this.repaymentGroupAmtElt = document.getElementById("repaymentGroupAmt")
        this.budgetBalanceGroupAmtElt = document.getElementById("budgetBalanceGroupAmt")

        this.moneyElts = document.querySelectorAll(".js-money")

        this.evalBudgetResourcesAmtElts = this.evalBudgetElt.querySelectorAll(".js-resourcesAmt")
        this.evalBudgetChargesAmtElts = this.evalBudgetElt.querySelectorAll(".js-chargesAmt")
        this.evalBudgetDebtsAmtElts = this.evalBudgetElt.querySelectorAll(".js-debtsAmt")
        this.evalBudgetRepaymentAmtElts = this.evalBudgetElt.querySelectorAll(".js-repaymentAmt")
        this.evalBudgetBudgetBalancAmtElts = this.evalBudgetElt.querySelectorAll(".js-budgetBalanceAmt")

        this.contributionAmtInput = document.getElementById("evaluation_evalBudgetGroup_contributionAmt")
        this.updateContributionBtnElt = document.getElementById("update_contribution")
        this.calculationMethodElt = document.getElementById("calculationMethod")

        this.resourcesAmtElts = document.querySelectorAll("input[data-id='resourcesAmt']")

        this.now = new Date()
        this.dateElts = document.querySelectorAll("input[type='date']")

        this.validationForm = new ValidationForm()
        this.selectType = new SelectType()
        this.init()
    }

    init() {
        this.evalSocialGroup()
        this.evalFamilyGroup()
        this.evalBudgetGroup()
        this.evalHousingGroup()

        const prefix = this.prefix + "evaluationPeople_"
        this.initEvalPerson(prefix)
        this.evalSocialPerson(prefix)
        this.evalFamily(prefix)
        this.evalProfPerson(prefix)
        this.evalBudgetPerson(prefix)
        this.evalAdmPerson(prefix)

        document.getElementsByClassName("card").forEach(cardElt => {
            const btnPersonElts = cardElt.querySelectorAll("button.js-person")
            btnPersonElts.forEach(btnElt => {
                btnElt.addEventListener("click", this.activeBtn.bind(this, btnPersonElts, btnElt))
            })
        })

        document.querySelectorAll(".js-evalGroup[data-support-id]").forEach(elt => {
            this.initSelects(elt)
            this.initInputs(elt)
        })
        document.querySelectorAll(".collapse[data-support-id]").forEach(elt => {
            this.initSelects(elt)
            this.initInputs(elt)
        })

        this.moneyElts.forEach(moneyElt => {
            moneyElt.addEventListener("change", this.checkMoney.bind(this, moneyElt))
        })
        this.dateElts.forEach(dateElt => {
            dateElt.addEventListener("focusout", this.checkDate.bind(this, dateElt))
        })

        if (this.updateContributionBtnElt) {
            this.updateContributionBtnElt.addEventListener("click", e => {
                e.preventDefault()
                this.updateContribution()
            })
        }
        if (this.contributionAmtInput) {
            this.contributionAmtInput.addEventListener("input", e => {
                e.preventDefault()
                this.calculationMethodElt.textContent = ""
            })
        }

        this.countEmptyImportantElts()
    }

    /**
     * Evaluation sociale du groupe.
     */
    evalSocialGroup() {
        new DisplayFields(this.prefix + "evalSocialGroup_", "animal", [1])
    }

    /**
     * Evaluation familiale du groupe.
     */
    evalFamilyGroup() {
        new DisplayFields(this.prefix + "evalFamilyGroup_", "famlReunification", [1, 3, 4, 5])
    }

    /**
     * Evaluation budgétaire.
     */
    evalBudgetGroup() {
        this.editAmtPers("resources")
        this.editAmtPers("charges")
        this.editAmtPers("debts")
        this.editAmtPers("repayment")
    }

    /**
     * Evaluation liée au logement.
     */
    evalHousingGroup() {
        const prefix = this.prefix + "evalHousingGroup_"
        // new DisplayFields(prefix, "housingAccessType", [1, 2, 3, 4, 5, 6, 7, 8, 9])
        new DisplayFields(prefix, "housingStatus", [200, 201, 202, 203, 204, 205, 206, 207, 300, 301, 302, 303, 304])
        new DisplayFields(prefix, "siaoRequest", [1])
        new DisplayFields(prefix, "socialHousingRequest", [1])
        new DisplayFields(prefix, "syplo", [1])
        new DisplayFields(prefix, "daloCommission", [1])
        new DisplayFields(prefix, "daloTribunalAction", [1])
        new DisplayFields(prefix, "collectiveAgreementHousing", [1])
        new DisplayFields(prefix, "hsgActionEligibility", [1])
        new DisplayFields(prefix, "expulsionInProgress", [1])
        new DisplayFields(prefix, "housingExperience", [1])
        new DisplayFields(prefix, "domiciliation", [1])
        this.editElt("", "_evalHousingGroup_hsgHelps", "d-table-row")
        this.selectTrElts("eval_housing", "evalHousingGroup", "", "hsgHelps")
    }

    /**
     * Evaluation situation initiale individuelle.
     * @param {String} prefix 
     */
    initEvalPerson(prefix) {
        document.getElementById("accordion-init_eval").querySelectorAll("button.js-person").forEach(personElt => {
            const i = personElt.getAttribute("data-key")
            new DisplayFields(prefix, i + "_initEvalPerson_rightSocialSecurity", [1, 3])
            new DisplayFields(prefix, i + "_initEvalPerson_profStatus", [3, 5, 8])
            new DisplayFields(prefix, i + "_initEvalPerson_resources_resources", [1, 3])
            new DisplayFields(prefix, i + "_initEvalPerson_debts", [1])
            this.editElt(i, "_initEvalPerson_resources_type", "d-table-row")
            this.selectTrElts("init_eval", "initEvalPerson", i, "resources_type")
            this.editAmt(prefix, "init_eval", "initEvalPerson", i, "resources")
            this.changeResources("eval_budget", prefix, i, "initEvalPerson")
        })
    }

    /**
     * Evaluation sociale individuelle.
     * @param {String} prefix 
     */
    evalSocialPerson(prefix) {
        document.getElementById("accordion-eval_social").querySelectorAll("button.js-person").forEach(personElt => {
            const i = personElt.getAttribute("data-key")
            new DisplayFields(prefix, i + "_evalSocialPerson_rightSocialSecurity", [1, 3])
            new DisplayFields(prefix, i + "_evalSocialPerson_healthProblem", [1])
            new DisplayFields(prefix, i + "_evalSocialPerson_careSupport", [1])
            new DisplayFields(prefix, i + "_evalSocialPerson_violenceVictim", [1])
            this.editElt(i, "_evalSocialPerson_healthProblemType", "d-table-row")
            this.selectTrElts("eval_social", "evalSocialPerson", i, "healthProblemType")
        })
    }

    /**
     * Evaluation administrative individuelle.
     * @param {String} prefix 
     */
    evalAdmPerson(prefix) {
        document.getElementById("accordion-eval_adm").querySelectorAll("button.js-person").forEach(personElt => {
            const i = personElt.getAttribute("data-key")
            new DisplayFields(prefix, i + "_evalAdmPerson_nationality", [2, 3, 4])
            new DisplayFields(prefix, i + "_evalAdmPerson_paper", [1, 3])
            new DisplayFields(prefix, i + "_evalAdmPerson_paperType", [20, 21, 22, 30, 31, 97])
            new DisplayFields(prefix, i + "_evalAdmPerson_asylumBackground", [1])
        })
    }

    /**
     * Evaluation familiale individuelle.
     * @param {String} prefix 
     */
    evalFamily(prefix) {
        document.getElementById("accordion-eval_family").querySelectorAll("button.js-person").forEach(personElt => {
            const i = personElt.getAttribute("data-key")
            new DisplayFields(prefix, i + "_evalFamilyPerson_maritalStatus", [6])
            new DisplayFields(prefix, i + "_evalFamilyPerson_unbornChild", [1])
            new DisplayFields(prefix, i + "_evalFamilyPerson_protectiveMeasure", [1, 3])

        })
    }

    /**
     * Evaluation professionnelle individuelle.
     * @param {String} prefix 
     */
    evalProfPerson(prefix) {
        document.getElementById("accordion-eval_prof").querySelectorAll("button.js-person").forEach(personElt => {
            const i = personElt.getAttribute("data-key")
            new DisplayFields(prefix, i + "_evalProfPerson_profStatus", [3, 5, 8])
            new DisplayFields(prefix, i + "_evalProfPerson_transportMeansType", [1, 2, 3])
            new DisplayFields(prefix, i + "_evalProfPerson_rqth", [1])
        })
    }

    /**
     * Evaluation budgétaire individuelle.
     * @param {String} prefix 
     */
    evalBudgetPerson(prefix) {
        const entity = "evalBudgetPerson"
        document.getElementById("accordion-eval_budget").querySelectorAll("button.js-person").forEach(personElt => {
            const i = personElt.getAttribute("data-key")
            new DisplayFields(prefix, i + "_evalBudgetPerson_resources_resources", [1, 3])
            new DisplayFields(prefix, i + "_evalBudgetPerson_charges", [1])
            new DisplayFields(prefix, i + "_evalBudgetPerson_debts", [1])
            new DisplayFields(prefix, i + "_evalBudgetPerson_overIndebtRecord", [1])
            new DisplayFields(prefix, i + "_evalBudgetPerson_incomeTax", [1])
            this.editElt(i, "_evalBudgetPerson_resources_type", "d-table-row")
            this.editElt(i, "_evalBudgetPerson_charges_type", "d-table-row")
            this.editElt(i, "_evalBudgetPerson_debts_type", "d-table-row")
            this.selectTrElts("eval_budget", entity, i, "resources_type")
            this.selectTrElts("eval_budget", entity, i, "charges_type")
            this.selectTrElts("eval_budget", entity, i, "debts_type")
            this.editAmt(prefix, "eval_budget", entity, i, "resources")
            this.editAmt(prefix, "eval_budget", entity, i, "charges")
            this.changeResources("eval_budget", prefix, i, entity)
        })
    }

    countEmptyImportantElts() {
        this.accordionElts.forEach(accordionElt => {
            let count = 0
            accordionElt.querySelectorAll('.important.border-warning').forEach(elt => {
                ++count
            })
            const badge = accordionElt.querySelector('.badge')
            if (badge) {
                badge.textContent = count
                if (count === 0) {
                    return badge.classList.add('d-none')
                } 
                return badge.classList.remove('d-none')
            }
        })
    }

    /**
     * Si changement des ressources à "Non", alors efface tous les types de ressources saisies de la personne.
     * @param {String} collapseId 
     * @param {String} prefix 
     * @param {Number} i 
     * @param {String} entity 
     */
    changeResources(collapseId, prefix, i, entity) {
        const resourceInput = document.getElementById(prefix + i + "_" + entity + "_resources_resources")
        resourceInput.addEventListener("change", e => {
            if (this.selectType.getOption(resourceInput) === "2") {
                document.querySelectorAll(".js-" + i + "_" + entity + "_resources_type").forEach(trElt => {
                    this.removeTr(collapseId, entity, i, trElt)
                })
                document.getElementById(prefix + i + "_" + entity + "_resources_resourcesAmt").value = 0 // met le total des ressources à zéro
            }
        })
    }

    /**
     * Initialise les Inputs pour les éléments de la situations initiale.
     * @param {HTMLElement} elt 
     */
    initInputs(elt) {
        elt.querySelectorAll("input.js-initEval").forEach(inputElt => {
            inputElt.setAttribute("data-support-id", elt.getAttribute("data-support-id"))
            if (!inputElt.value) {
                inputElt.classList.add("border-warning")
            }
            inputElt.addEventListener("change", this.changeInput.bind(this, inputElt))
        })
    }

    /**
     * Initialise les Selects pour les éléments de la situations initiale.
     * @param {HTMLElement} elt 
     */
    initSelects(elt) {
        elt.querySelectorAll("select.js-initEval").forEach(selectElt => {
            selectElt.setAttribute("data-support-id", elt.getAttribute("data-support-id"))
            if (!this.selectType.getOption(selectElt)) {
                selectElt.classList.add("border-warning")
            }
            selectElt.addEventListener("change", this.changeSelect.bind(this, selectElt))
        })
    }

    /**
     * Si modification d'un Input, met à jour l'autre champ semblable si vide.
     * @param {HTMLElement} elt 
     */
    changeInput(elt) {
        if (elt.value) {
            const dataId = elt.getAttribute("data-id")
            const supportPersonId = elt.getAttribute("data-support-id")
            document.querySelectorAll("input[data-id='" + dataId + "'][data-support-id='" + supportPersonId + "']").forEach(inputElt => {
                if (!inputElt.value && this.editMode === "false") {
                    inputElt.value = elt.value
                    inputElt.classList.remove("border-warning")
                }
            })
            elt.classList.remove("border-warning")
        } else {
            elt.classList.add("border-warning")
        }
        this.countEmptyImportantElts()
    }

    /**
     * Si modification d'un Select, met à jour l'autre champ semblable si vide.
     * @param {HTMLElement} elt 
     */
    changeSelect(elt) {
        const optionSelected = this.selectType.getOption(elt)
        if (optionSelected) {
            document.querySelectorAll("select[data-id='" + elt.getAttribute("data-id") + "'][data-support-id='" + elt.getAttribute("data-support-id") + "']").forEach(selectElt => {
                if (!selectElt.querySelector("option[selected]")) {
                    this.selectType.setOption(selectElt, optionSelected)
                    selectElt.classList.remove("border-warning")
                    selectElt.click()
                }
            })
            elt.classList.remove("border-warning")
        } else {
            elt.classList.add("border-warning")
        }
        this.countEmptyImportantElts()
    }

    /**
     * Masque ou affiche un élement.
     * @param {Number} i 
     * @param {String} eltId 
     * @param {String} display 
     */
    editElt(i, eltId, display) {
        const selectElt = document.getElementById("js-" + i + eltId)
        const inputElts = document.querySelectorAll(".js-" + i + eltId)
        selectElt.addEventListener("change", this.addOption.bind(this, selectElt, i, eltId, display))
        inputElts.forEach(inputElt => {
            inputElt.addEventListener("click", e => {
                this.displayNone(inputElt, display)
            })
            this.displayNone(inputElt, display)
        })
    }

    /**
     * Masque l'affichage de l'input.
     * @param {HTMLElement} inputElt 
     * @param {String} display 
     */
    displayNone(inputElt, display) {
        if (inputElt.querySelector("input").value != 1) {
            inputElt.classList.replace(display, "d-none")
        }
    }

    /**
     * Active/Désactive le bouton d'une personne au clic.
     * @param {HTMLElement} btnElts 
     * @param {HTMLButtonElement} selectedBtnElt 
     */
    activeBtn(btnElts, selectedBtnElt) {
        let active = false
        if (selectedBtnElt.classList.contains("active")) {
            active = true
        }
        btnElts.forEach(btn => {
            btn.classList.remove("active")
        })
        if (!active) {
            selectedBtnElt.classList.add("active")
        }
    }

    /**
     * Ajoute l'option sélectionnée de la liste déroulante.
     * @param {HTMLElement} selectElt 
     * @param {Number} i 
     * @param {String} eltId 
     * @param {String} display 
     */
    addOption(selectElt, i, eltId, display) {
        const optionElts = selectElt.querySelectorAll("option")
        optionElts.forEach(option => {
            if (option.selected) {
                this.trElt = document.getElementById("js-" + i + eltId + "-" + option.value)
                const dataId = this.trElt.getAttribute("data-id")
                const supportPersonId = this.trElt.getAttribute("data-support-id")
                const trElts = document.querySelectorAll("tr[data-id='" + dataId + "'][data-support-id='" + supportPersonId + "']")
                if (selectElt.getAttribute("data-id") === "resourcesType" && this.editMode === "false") {
                    trElts.forEach(trElt => {
                        trElt.querySelector("input[type='number']").value = 1
                        trElt.classList.replace("d-none", display)
                    })
                } else {
                    this.trElt.querySelector("input[type='number']").value = 1
                    this.trElt.classList.replace("d-none", display)
                }
            }
            // Met tous les autres inputs du tableau à 0 si vide
            document.querySelectorAll(".js-" + i + eltId).forEach(trElt => {
                const inputElt = trElt.querySelector("input[type='number']")
                if (!inputElt.value) {
                    inputElt.value = 0
                }
            })
        })
        // Remplace le select sur l'option par défaut
        window.setTimeout(e => {
            selectElt.querySelector("option").selected = "selected"
            const inputTextElt = this.trElt.querySelector("input[type='text']")
            if (inputTextElt) {
                inputTextElt.focus()
            }
        }, 200)
    }

    /**
     * Sélectionne toutes les lignes d'un tableau.
     * @param {String} collapseId 
     * @param {String} entity 
     * @param {Number} i 
     * @param {String} type 
     */
    selectTrElts(collapseId, entity, i, type) {
        const trElts = document.querySelectorAll(".js-" + i + "_" + entity + "_" + type)
        trElts.forEach(trElt => {
            trElt.querySelector("button.js-remove").addEventListener("click", e => {
                e.preventDefault()
                this.removeTr(collapseId, entity, i, trElt)
            })
        })
    }

    /**
     * Retire la ligne correspondante dans le tableau.
     * @param {String} collapseId 
     * @param {String} entity 
     * @param {Number} i 
     * @param {HTMLElement} trElt 
     */
    removeTr(collapseId, entity, i, trElt) {
        trElt.querySelectorAll("input").forEach(inputElt => {
            inputElt.getAttribute("type") === "number" ? inputElt.value = 0 : inputElt.value = null
        })
        trElt.classList.replace("d-table-row", "d-none")
        if (entity === "evalBudgetPerson" || entity === "initEvalPerson") {
            this.updateSumAmt(collapseId, entity, i, "resources")
        }
        if (entity === "evalBudgetPerson") {
            this.updateSumAmt(collapseId, entity, i, "charges")
        }
    }

    /**
     * Met à jour la somme des montants après la saisie d'un input.
     * @param {String} prefix 
     * @param {String} collapseId 
     * @param {String} entity 
     * @param {Numbert} i 
     * @param {String} type 
     */
    editAmt(prefix, collapseId, entity, i, type) {
        const inputElts = document.getElementById("collapse-" + collapseId + "-" + i).querySelectorAll("input.js-" + type)
        const amtElt = document.getElementById(prefix + i + "_" + entity + (type === "resources" ? "_resources_" : "_") + type + "Amt")
        inputElts.forEach(inputElt => {
            inputElt.addEventListener("input", e => {
                amtElt.value = this.getSumAmts(inputElts)
                amtElt.classList.remove('border-warning')
                this.updateAmtGroup(type)
            })
        })

        inputElts.forEach(inputElt => {
            inputElt.addEventListener("focusout", e => {
                this.resourcesAmtElts.forEach(ressourcesAmtElt => {
                    ressourcesAmtElt.click()
                })
            })
        })

        if (amtElt) {
            amtElt.addEventListener("click", e => {
                const sumAlts = this.getSumAmts(inputElts)
                if (sumAlts != 0) {
                    amtElt.value = sumAlts
                    this.updateAmtGroup(type)
                }
            })
        }
    }

    /**
     * Retourne la somme des montants.
     * @param {Array} inputElts 
     */
    getSumAmts(inputElts) {
        const array = []
        inputElts.forEach(inputElt => {
            if (inputElt.value) {
                array.push(parseFloat(inputElt.value))
            }
        })

        const sumAmts = array.reduce((a, b) => a + b, 0)

        if (!isNaN(sumAmts)) {
            return sumAmts
        }
        return "Erreur"
    }

    /**
     * Met à jour la somme des montants de la personne.
     * @param {String} collapseId 
     * @param {String} entity 
     * @param {Number} i 
     * @param {String} type 
     */
    updateSumAmt(collapseId, entity, i, type) {
        const inputElts = document.getElementById("collapse-" + collapseId + "-" + i).querySelectorAll("input.js-" + type)
        document.getElementById("evaluation_evaluationPeople_" + i + "_" + entity + (type === "resources" ? "_resources_" : "_") + type + "Amt").value = this.getSumAmts(inputElts)
        this.updateAmtGroup(type)
    }

    /**
     * Met à jour le montant total du groupe lors d'une modification des montants individuels.
     * @param {String} type 
     */
    editAmtPers(type) {
        this.amtElts(type).forEach(amountElt => {
            amountElt.addEventListener("input", this.updateAmtGroup.bind(this, type))
        })
    }

    /**
     * Met à jour le montant total du groupe (resources, charges ou dettes).
     * @param {String} type 
     */
    updateAmtGroup(type) {
        const array = []
        this.amtElts(type).forEach(amountElt => {
            if (amountElt.value) {
                array.push(parseFloat(amountElt.value))
            }
        })

        this.groupAmtElt(type).textContent = array.reduce((a, b) => a + b, 0)

        this.budgetBalanceGroupAmtElt.textContent = parseFloat(this.resourcesGroupAmtElt.textContent - this.chargesGroupAmtElt.textContent - this.repaymentGroupAmtElt.textContent)
    }

    /**
     * 
     * @param {String} type 
     */
    groupAmtElt(type) {
        switch (type) {
            case "resources":
                return this.resourcesGroupAmtElt
                break
            case "charges":
                return this.chargesGroupAmtElt
                break
            case "debts":
                return this.debtsGroupAmtElt
                break
            case "repayment":
                return this.repaymentGroupAmtElt
                break
        }
    }

    /**
     * 
     * @param {String} type 
     */
    amtElts(type) {
        switch (type) {
            case "resources":
                return this.evalBudgetResourcesAmtElts
                break
            case "charges":
                return this.evalBudgetChargesAmtElts
                break
            case "debts":
                return this.evalBudgetDebtsAmtElts
                break
            case "repayment":
                return this.evalBudgetRepaymentAmtElts
                break
        }
    }

    /**
     * Vérifie la validité des montants.
     * @param {HTMLElement} type 
     */
    checkMoney(moneyElt) {
        moneyElt.value = moneyElt.value.replace(" ", "")
        moneyElt.value = moneyElt.value.replace(",", ".")
        if (Number(moneyElt.value) >= 0) {
            return this.validationForm.validField(moneyElt)
        }
        return this.validationForm.invalidField(moneyElt, "Montant invalide.")
    }

    /**
     * Vérifie la la validité de la date.
     * @param {HTMLInputElement} dateElt 
     */
    checkDate(dateElt) {
        const interval = Math.round((this.now - new Date(dateElt.value)) / (24 * 3600 * 1000))
        if ((dateElt.value && !Number.isInteger(interval)) || interval > (365 * 99) || interval < -(365 * 99)) {
            return this.validationForm.invalidField(dateElt, "Date invalide.")
        }
        return this.validationForm.validField(dateElt)
    }

    /**
     * Met à jour la contribution financière.
     */
    updateContribution() {
        const contributionType = parseFloat(this.updateContributionBtnElt.getAttribute("data-contribution-type"))
        const resourcesGroupAmt = parseFloat(this.resourcesGroupAmtElt.textContent.replace(" ", ""))
        const contributionRate = this.updateContributionBtnElt.getAttribute("data-contribution-rate")

        if ([1, 3].indexOf(contributionType) != -1 && !isNaN(resourcesGroupAmt) && !isNaN(contributionRate)) {
            this.contributionAmtInput.value = Math.round(resourcesGroupAmt * contributionRate * 100) / 100
            this.calculationMethodElt.innerHTML = "Mode de calcul : Montant des ressources (" + resourcesGroupAmt +
                "&nbsp€) x Taux de participation (" + (contributionRate * 100) + "&nbsp%)."
        } else {
            this.calculationMethodElt.innerHTML = "Type de redevance non défini dans le service."
        }
    }
}