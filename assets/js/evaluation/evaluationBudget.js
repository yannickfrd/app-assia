import FormValidator from '../utils/form/formValidator'
import ItemsListManager from '../utils/form/itemsListManager'
import ContributionCalcul from '../payment/contributionCalcul'

/**
 * Evaluation sociale.
 */
export default class evaluationBudget {

    constructor() {
        this.formValidator = new FormValidator()

        this.prefix = 'evaluation_'

        this.evalBudgetElt = document.getElementById('accordion-parent-eval_budget')

        this.resourcesGroupAmtElt = document.getElementById('resourcesGroupAmt')
        this.chargesGroupAmtElt = document.getElementById('chargesGroupAmt')
        this.debtsGroupAmtElt = document.getElementById('debtsGroupAmt')
        this.repaymentGroupAmtElt = document.getElementById('repaymentGroupAmt')
        this.budgetBalanceGroupAmtElt = document.getElementById('budgetBalanceGroupAmt')

        this.evalBudgetResourcesAmtElts = this.evalBudgetElt.querySelectorAll('input[data-amount="resourcesAmt"]')
        this.evalBudgetChargesAmtElts = this.evalBudgetElt.querySelectorAll('input[data-amount="chargesAmt"]')
        this.evalBudgetDebtsAmtElts = this.evalBudgetElt.querySelectorAll('input[data-amount="debtsAmt"]')
        this.evalBudgetRepaymentAmtElts = this.evalBudgetElt.querySelectorAll('input[data-amount="repaymentAmt"]')
        this.evalBudgetBudgetBalancAmtElts = this.evalBudgetElt.querySelectorAll('input[data-amount="budgetBalanceAmt"]')

        this.resourcesAmtElts = document.querySelectorAll('input[data-twin-field="resourcesAmt"]')

        this.NO = 2

        this.init()
    }

    init() {
        this.evalBudgetGroup()

        const prefix = this.prefix + 'evaluationPeople_'
        this.initEvalPerson(prefix)
        this.evalBudgetPerson(prefix)

        if (document.getElementById('calcul-contribution-btn')) {
            new ContributionCalcul(null, this.afterCalculContribution.bind(this))
        }
    }

    /**
     * Evaluation budgétaire.
     */
    evalBudgetGroup() {
        this.editAmtPers('resources')
        this.editAmtPers('charges')
        this.editAmtPers('debts')
        this.editAmtPers('repayment')
    }

    /**
     * Evaluation situation initiale individuelle.
     * @param {String} prefix 
     */
    initEvalPerson(prefix) {
        document.getElementById('accordion-init_eval').querySelectorAll('button[data-person-key]')
            .forEach(personElt => {
                const key = personElt.dataset.personKey
                new ItemsListManager(`${key}_initEvalPerson_resourcesType`,
                    this.updateSumAmt.bind(this, 'init_eval', 'initEvalPerson', key, 'resources'))
                this.editAmt(prefix, 'init_eval', 'initEvalPerson', key, 'resources')
                this.changeSelectToNo(prefix, 'eval_budget', key, 'initEvalPerson', 'resources')
            })
    }

    /**
     * Evaluation budgétaire individuelle.
     * @param {String} prefix 
     */
    evalBudgetPerson(prefix) {
        const entity = 'evalBudgetPerson'
        document.getElementById('accordion-eval_budget').querySelectorAll('button[data-person-key]')
            .forEach(personElt => {
                const key = personElt.dataset.personKey
                new ItemsListManager(`${key}_evalBudgetPerson_resourcesType`,
                    this.updateSumAmt.bind(this, 'eval_budget', entity, key, 'resources'))
                new ItemsListManager(`${key}_evalBudgetPerson_chargesType`,
                    this.updateSumAmt.bind(this, 'eval_budget', entity, key, 'charges'))
                new ItemsListManager(`${key}_evalBudgetPerson_debtsType`)
                this.editAmt(prefix, 'eval_budget', entity, key, 'resources')
                this.editAmt(prefix, 'eval_budget', entity, key, 'charges')
                this.changeSelectToNo(prefix, 'eval_budget', key, entity, 'resources')
                this.changeSelectToNo(prefix, 'eval_budget', key, entity, 'charges')
            })
    }

    /**
     * Si changement des ressources à 'Non', alors efface tous les types de ressources saisies de la personne.
     * @param {String} prefix 
     * @param {String} collapseId 
     * @param {Number} key 
     * @param {String} entity 
     */
    changeSelectToNo(prefix, collapseId, key, entity, type) {
        const resourceInput = document.getElementById(`${prefix}${key}_${entity}${type === 'resources' ? '_resources' : ''}_${type}`)

        if (!resourceInput) {
            return null
        }

        resourceInput.addEventListener('change', () => {
            if (this.NO === resourceInput.value) {
                document.querySelectorAll(`tr[data-parent-select="${key}_${entity}_${type}Type"]`).forEach(trElt => {
                    trElt.querySelectorAll('input').forEach(inputElt => {
                        inputElt.getAttribute('type') === 'number' ? inputElt.value = 0 : inputElt.value = null
                    })
                    trElt.classList.replace('d-table-row', 'd-none')
                })
                const sumAmtElt = document.getElementById(`${prefix}${key}_${entity}${type === 'resources' ? '_resources' : ''}_${type}Amt`)
                sumAmtElt.value = 0 // met le montant à zéro
                
                this.updateAmtGroup(type)
            }
        })
    }

    /**
     * Met à jour la somme des montants après la saisie d'un input.
     * @param {String} prefix 
     * @param {String} collapseId 
     * @param {String} entity 
     * @param {Numbert} key 
     * @param {String} type 
     */
    editAmt(prefix, collapseId, entity, key, type) {
        const inputElts = document.getElementById(`collapse-${collapseId}-${key}`)
            .querySelectorAll(`input[data-amount="${type}"]`)
        const amtElt = document.getElementById(`${prefix}${key}_${entity}${type === 'resources' ? '_resources' : ''}_${type}Amt`)
        inputElts.forEach(inputElt => {
            inputElt.addEventListener('input', () => {
                amtElt.value = this.getSumAmts(inputElts)
                amtElt.classList.remove('border-warning')
                this.updateAmtGroup(type)
            })
        })

        inputElts.forEach(inputElt => {
            inputElt.addEventListener('focusout', () => {
                this.resourcesAmtElts.forEach(ressourcesAmtElt => {
                    ressourcesAmtElt.click()
                })
            })
        })

        if (amtElt) {
            amtElt.addEventListener('click', () => {
                const sumAmts = this.getSumAmts(inputElts)
                if (sumAmts != 0) {
                    amtElt.value = sumAmts
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
        let sumAmts = 0
        inputElts.forEach(inputElt => {
            if (inputElt.value) {
                sumAmts += parseFloat(inputElt.value)
            }
        })
        if (!isNaN(sumAmts)) {
            return sumAmts
        }
        return 'Erreur'
    }

    /**
     * Met à jour la somme des montants de la personne.
     * @param {String} collapseId 
     * @param {String} entity 
     * @param {Number} key 
     * @param {String} type 
     */
    updateSumAmt(collapseId, entity, key, type) {
        const inputElts = document.getElementById(`collapse-${collapseId}-${key}`)
            .querySelectorAll(`input[data-amount="${type}"]`)
        document.getElementById(`evaluation_evaluationPeople_${key}_${entity}${type === 'resources' ? '_resources' : ''}_${type}Amt`)
            .value = this.getSumAmts(inputElts)
        this.updateAmtGroup(type)
    }

    /**
     * Met à jour le montant total du groupe lors d'une modification des montants individuels.
     * @param {String} type 
     */
    editAmtPers(type) {
        this.amtElts(type).forEach(amountElt => {
            amountElt.addEventListener('input', () => this.updateAmtGroup(type))
        })
    }

    /**
     * Met à jour le montant total du groupe (resources, charges ou dettes).
     * @param {String} type 
     */
    updateAmtGroup(type) {
        let sumAmts = 0
        this.amtElts(type).forEach(amountElt => {
            if (amountElt.value) {
                sumAmts += parseFloat(amountElt.value)

            }
        })

        this.groupAmtElt(type).textContent = sumAmts.toLocaleString() + ' €'
        this.updateBudgetBalanceAmt()
    }

    /**
     * Met à jour le reste à vivre du groupe.
     */
    updateBudgetBalanceAmt() {
        this.budgetBalanceGroupAmtElt.textContent = 
            (this.convertsToNumber(this.resourcesGroupAmtElt.textContent)
            - this.convertsToNumber(this.chargesGroupAmtElt.textContent)
            - this.convertsToNumber(this.repaymentGroupAmtElt.textContent)).toLocaleString() + ' €'
    }

    /**
     * 
     * @param {String} value 
     * @returns {Number} 
     */
    convertsToNumber(value) {
        return parseFloat(value.replace(' ', ''.replace(',', '.')))
    }
    
    /**
     * 
     * @param {String} type 
     */
    groupAmtElt(type) {
        switch (type) {
            case 'resources':
                return this.resourcesGroupAmtElt
                break
            case 'charges':
                return this.chargesGroupAmtElt
                break
            case 'debts':
                return this.debtsGroupAmtElt
                break
            case 'repayment':
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
            case 'resources':
                return this.evalBudgetResourcesAmtElts
                break
            case 'charges':
                return this.evalBudgetChargesAmtElts
                break
            case 'debts':
                return this.evalBudgetDebtsAmtElts
                break
            case 'repayment':
                return this.evalBudgetRepaymentAmtElts
                break
        }
    }

    /**
     * @param {Object} payment 
     */
    afterCalculContribution(payment) {
        document.getElementById('evaluation_evalBudgetGroup_contributionAmt').value = payment.toPayAmt
    }
}