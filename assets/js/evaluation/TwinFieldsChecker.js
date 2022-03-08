import SelectCollectionManager from '../utils/form/SelectCollectionManager'

/**
 * Gestion des champs jumeaux.
 */
export default class TwinFieldsChecker {

    constructor() {
        this.twinFieldElts = document.querySelectorAll('[data-twin-field]')
        this.editMode = document.querySelector('div[data-edit-mode]').dataset.editMode
        this.yes = 1
        this.other = 1000

        this.init()
    }

    init() {
        this.twinFieldElts.forEach(fieldElt => {
            const splitId = fieldElt.id.split('_')

            fieldElt.dataset.twinField = splitId[2] + '_' + splitId.pop() 
            fieldElt.addEventListener('change', () => this.onChangeField(fieldElt))
        })

        this.evalInitResourceTypeFields()
    }
    
    evalInitResourceTypeFields() {
        const nbPeople = document.querySelectorAll('#accordion_evalBudget button[data-person-key]').length
        for (let i = 0; i < nbPeople; i++) {
            document.querySelectorAll(`select[data-twin-field="${i}_resourceType"]`).forEach(selectElt => {
                selectElt.addEventListener('change', () => this.onChangeResourceType(selectElt, i))
            })
        }
    }

    /**
     * @param {HTMLElement} selectElt 
     * @param {number} i 
     */
    onChangeResourceType(selectElt, i) {
        document.querySelectorAll(`select[data-twin-field="${i}_resourceType"]`).forEach(twinElt => {
            if (twinElt.id === selectElt.id) return

            const resourceInputElt = document.getElementById(twinElt.id.replace('Type', ''))
            if (resourceInputElt.value == this.yes && this.editMode === 'false' && !twinElt.dataset.edit) {
                new SelectCollectionManager(twinElt.id, this.afterAddElt.bind(this)).addElt(false, true)
            }
            twinElt.querySelector('option').selected = 'selected'
        })
        selectElt.dataset.edit = true
    }

    /**
     * @param {HTMLTableRowElement} newTrElt 
     */
    afterAddElt(newTrElt) {
        const typeinputElt = newTrElt.querySelector('input[type="hidden"]')
        const amountInputElt = newTrElt.querySelector('input[data-amount]')
        const splitId = amountInputElt.id.split('_')
        
        amountInputElt.dataset.twinField = splitId[2] + '_' + splitId[4] + '_' + typeinputElt.value + '_' + splitId.pop()
        amountInputElt.addEventListener('change', () => this.onChangeField(amountInputElt))

        if (parseInt(typeinputElt.value) === 1000) {
            const commentInputElt = newTrElt.querySelector('input[type="text"]')
            const splitId = commentInputElt.id.split('_')

            commentInputElt.dataset.twinField = splitId[2] + '_' + splitId[4] + '_' + typeinputElt.value + '_' + splitId.pop()
            commentInputElt.addEventListener('change', () => this.onChangeField(commentInputElt))
        }
    }

    /**
     * Si modification d'un input ou d'un select, met à jour l'autre champ semblable si ce dernier est vide.
     * @param {HTMLElement} fieldElt 
     */
    onChangeField(fieldElt) {
        if (!fieldElt.value) {
            return
        }

        document.querySelectorAll(`[data-twin-field="${fieldElt.dataset.twinField}"]`).forEach(twinElt => {
            if (twinElt.id === fieldElt.id) {
                return
            }

            if (!twinElt.value && this.editMode === 'false') {
                twinElt.value = fieldElt.value
            }

            twinElt.classList.remove('border-warning')
            twinElt.click()
        })
    }
}