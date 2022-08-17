import FormValidator from '../utils/form/formValidator'
import SelectCollectionManager from '../utils/form/SelectCollectionManager'
import ContributionCalculator from '../payment/ContributionCalculator'

/**
 * Evaluation sociale.
 */
export default class evaluationBudget {

    constructor() {
        this.formValidator = new FormValidator()

        this.evalBudgetElt = document.getElementById('accordion_item_evalBudget')
        this.resourcesGroupAmtElt = document.getElementById('resourcesGroupAmt')
        this.chargesGroupAmtElt = document.getElementById('chargesGroupAmt')
        this.resourcesAmtElts = document.querySelectorAll('input[data-amount="resourcesAmt"]')
        this.no = 2
        this.other = 1000

        this.init()
    }

    init() {
        this.evalBudgetGroup()

        this.evalInitPerson()
        this.evalBudgetPerson()

        document.querySelectorAll('input[data-amount]').forEach(amountElt => {
            amountElt.addEventListener('input', () => this.formValidator.checkAmount(amountElt, 0, 999999))
            amountElt.addEventListener('focusout', () => this.formValidator.checkAmount(amountElt, 0, 999999, true))
        })

        if (document.getElementById('calcul_contribution_btn')) {
            new ContributionCalculator(null, (payment) => this.afterCalculContribution(payment))
        }
    }

    /**
     * Evaluation budgétaire.
     */
    evalBudgetGroup() {
        this.updateAmtPers('resource')
        this.updateAmtPers('charge')
        this.updateAmtPers('debt')
    }

    /**
     * Evaluation situation initiale individuelle.
     */
    evalInitPerson() {
        const personElts = document.getElementById('accordion_eval_init').querySelectorAll('button[data-person-key]')
            
        personElts.forEach(personElt => {
            const key = personElt.dataset.personKey
            const id = `evaluation_evaluationPeople_${key}_evalInitPerson_resource`
            this.updateAmt(id, 'eval_init')
            this.switchSelectToNo(id)
            new SelectCollectionManager(
                id + 'Type',
                this.afterAddItem.bind(this, id),
                this.afterRemoveItem.bind(this, id),
            ).init()
        })
    }

    /**
     * Evaluation budgétaire individuelle.
     */
    evalBudgetPerson() {
        const personElts = document.getElementById('accordion_evalBudget').querySelectorAll('button[data-person-key]')
        
        personElts.forEach(personElt => {
            const key = personElt.dataset.personKey
            const types = ['resource', 'charge', 'debt']

            types.forEach(type => {
                const id = `evaluation_evaluationPeople_${key}_evalBudgetPerson_${type}`
                this.updateAmt(id)
                this.switchSelectToNo(id)
                new SelectCollectionManager(
                    id + 'Type',
                    this.afterAddItem.bind(this, id),
                    this.afterRemoveItem.bind(this, id),
                ).init()
            })
        })
    }

    /**
     * Si changement des ressources à 'Non', alors supprime les différentes ressources de la personne.
     * @param {string} id 
     */
    switchSelectToNo(id) {
        const resourceInputElt = document.getElementById(id)
        if (!resourceInputElt) {
            return null
        }
        resourceInputElt.addEventListener('change', () => {
            if (parseInt(resourceInputElt.value) !== this.no) {
                return null   
            }

            const trElts = document.querySelectorAll(`div[data-parent-field="${resourceInputElt.id}"] table tbody tr`)
            trElts.forEach(trElt => trElt.remove())

            const sumAmtElt = document.getElementById(id + 'sAmt')
            sumAmtElt.value = 0

            const type = id.split('_').pop()
            this.updateAmtGroup(type)
        })
    }

    /**
     * Met à jour la somme des montants après la saisie d'un input.
     * @param {id} id 
     */
    updateAmt(id) {
        const type = id.split('_').pop()
        const inputElts = this.getInputElts(id)
        const sumAmtElt = document.getElementById(id + 'sAmt')
        
        inputElts.forEach(inputElt => {
            inputElt.addEventListener('input', () => {
                sumAmtElt.value = this.getSumAmts(inputElts)
                sumAmtElt.classList.remove('border-warning')
                this.updateAmtGroup(type)
            })
            inputElt.addEventListener('focusout', () => {
                this.resourcesAmtElts.forEach(resourcesAmtElt => {
                    resourcesAmtElt.click()
                })
            })
        })

        if (sumAmtElt) {
            sumAmtElt.addEventListener('click', () => {
                const sumAmts = this.getSumAmts(this.getInputElts(id))
                if (sumAmts !== 0) {
                    sumAmtElt.value = sumAmts
                    this.updateAmtGroup(type)
                }
            })
        }
    }

    /**
     * @param {string} id 
     * @returns {NodeList}
     */
    getInputElts(id) {
        const type = id.split('_').pop()
        const collapseId = 'collapse_' + id.replace('_' + type, '')
        return document.getElementById(collapseId).querySelectorAll(`input[data-amount="${type}"]`)
    }

    /**
     * Retourne la somme des montants.
     * @param {NodeList} inputElts 
     */
    getSumAmts(inputElts) {
        let sumAmts = 0
        inputElts.forEach(inputElt => {
            if (inputElt.value) {
                sumAmts += parseFloat(inputElt.value.replaceAll(' ', '').replace(',', '.'))
            }
        })
        if (!isNaN(sumAmts)) {
            return Math.round(sumAmts * 100) / 100
        }
        return 'Erreur'
    }

    /**
     * Met à jour la somme des montants de la personne.
     * @param {string} id 
     */
    updateSumAmt(id) {
        const type = id.split('_').pop()
        const collapseId = 'collapse_' + id.replace(('_' + type), '')
        const inputElts = document.getElementById(collapseId).querySelectorAll(`input[data-amount="${type}"]`)
        const sumAmtElt = document.getElementById(id + 'sAmt')
        sumAmtElt.value = this.getSumAmts(inputElts)
        this.updateAmtGroup(type)
    }

    /**
     * Met à jour le montant total du groupe lors d'une modification des montants individuels.
     * @param {string} type 
     */
    updateAmtPers(type) {
        this.amtElts(type).forEach(amountElt => {
            amountElt.addEventListener('input', () => this.updateAmtGroup(type))
        })
    }

    /**
     * Met à jour le montant total du groupe (resources, charges ou dettes).
     * @param {string} type 
     */
    updateAmtGroup(type) {
        let sumAmts = 0
        this.amtElts(type).forEach(amountElt => {
            if (amountElt.value) {
                sumAmts += parseFloat(amountElt.value)

            }
        })

        const groupAmtElt = document.getElementById(`${type}sGroupAmt`)
        groupAmtElt.textContent = sumAmts.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' €'
        groupAmtElt.dataset.value = Math.round(sumAmts * 100) / 100
        this.updateBudgetBalanceAmt()
    }

    /**
     * Met à jour le reste à vivre du groupe.
     */
    updateBudgetBalanceAmt() {
        const budgetBalanceGroupAmt = Math.round(parseFloat(
            this.resourcesGroupAmtElt.dataset.value
            - this.chargesGroupAmtElt.dataset.value) * 100) / 100;
        
        document.getElementById('budgetBalanceGroupAmt').textContent =
            budgetBalanceGroupAmt.toLocaleString(undefined, { minimumFractionDigits: 2 }) + ' €'
    }


    /**
     * @param {string} type 
     */
    amtElts(type) {
        return this.evalBudgetElt.querySelectorAll(`input[data-amount="${type}sAmt"]`)
    }

    /**
     * After to add element into a collection².
     * @param {string} id 
     */
    afterAddItem(id) {
        this.updateAmt(id)
        
        const newTrElt = document.getElementById(id + 'Type_list').lastChild
        const typeinputElt = newTrElt.querySelector('input[type="hidden"]')
        const amountInputElt = newTrElt.querySelector('input[data-amount]')
        const splitId = amountInputElt.id.split('_')

        amountInputElt.dataset.twinField = splitId[2] + '_' + splitId[4] + '_' + typeinputElt.value + '_' + splitId.pop()
        amountInputElt.addEventListener('change', () => this.onChangeField(amountInputElt))

        if (parseInt(typeinputElt.value) ===  this.other) {
            const commentInputElt = newTrElt.querySelector('input[type="text"]')
            const splitId = commentInputElt.id.split('_')

            commentInputElt.dataset.twinField = splitId[2] + '_' + splitId[4] + '_' + typeinputElt.value + '_' + splitId.pop()
            commentInputElt.addEventListener('change', () => this.onChangeField(commentInputElt))
        }
    }

    /**
     * @param {HTMLElement} fieldElt 
     */
    onChangeField(fieldElt) {
        const twinField = fieldElt.dataset.twinField
        document.querySelectorAll(`[data-twin-field="${twinField}"]`).forEach(twinElt => {
            if (twinElt.id !== fieldElt.id && !twinElt.value) {
                twinElt.value = fieldElt.value
            }
        })
    }
    
    /**
     * After to remove element into a collection.
     * @param {string} id 
     */
    afterRemoveItem(id) {
        this.updateSumAmt(id)
    }

    /**
     * @param {Object} payment 
     */
    afterCalculContribution(payment) {
        document.getElementById('evaluation_evalBudgetGroup_contributionAmt').value = payment.toPayAmt
    }
}