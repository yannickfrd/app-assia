/**
 * Gestion des champs importants ou jumeaux.
 */
export default class importantFields {

    constructor() {
        this.editMode = document.querySelector('div[data-edit-mode]').dataset.editMode
        this.accordionElts = document.querySelectorAll('section.accordion')
        this.init()
    }

    init() {
        document.querySelectorAll('div[data-sp-id], div[data-sp-id]').forEach(elt => this.initFields(elt))
        this.countEmptyImportantElts()
    }
    
    countEmptyImportantElts() {
        this.accordionElts.forEach(accordionElt => {
            let count = 0
            accordionElt.querySelectorAll('select[data-important].border-warning, input[data-important].border-warning').forEach(elt => {
                const parent2xElt = elt.parentElement.parentElement
                if (window.getComputedStyle(parent2xElt).display != 'none'
                    && window.getComputedStyle(parent2xElt.parentElement).display != 'none') {
                    ++count
                }
            })
            const badge = accordionElt.querySelector('span.badge')
            if (badge) {
                count > 0 ? badge.classList.replace('fade-out', 'fade-in') : badge.classList.replace('fade-in', 'fade-out')
                badge.textContent = count
            }
        })
    }

    /**
     * Initialise les inputs et les selects pour les éléments de la situations initiale.
     * @param {HTMLElement} htmlElt 
     */
    initFields(htmlElt) {
        htmlElt.querySelectorAll('input[data-twin-field], select[data-twin-field]').forEach(fieldElt => {
            fieldElt.dataset.spId = htmlElt.dataset.spId
            if (fieldElt.dataset.important && (!fieldElt.value || ('select-one' === fieldElt.type && !fieldElt.value))) {
                fieldElt.classList.add('border-warning')
            }
            fieldElt.addEventListener('change', () => this.changeField(fieldElt))
        })
    }

    /**
     * Si modification d'un input ou d'un select, met à jour l'autre champ semblable si ce dernier est vide.
     * @param {HTMLElement} fieldElt 
     */
    changeField(fieldElt) {
        if (fieldElt.value || ('select-one' === fieldElt.type && fieldElt.value)) {
            const twinField = fieldElt.dataset.twinField
            const spId = fieldElt.dataset.spId
            document.querySelectorAll(`[data-twin-field="${twinField}"][data-sp-id="${spId}"]`).forEach(twinElt => {
                if (('select-one' === fieldElt.type && !twinElt.value)
                    || (!twinElt.value && 'false' === this.editMode && (!twinElt.dataset.edit))) {
                    twinElt.value = fieldElt.value
                }
                twinElt.classList.remove('border-warning')
                twinElt.click()
            })

        } else if (fieldElt.dataset.important) {
            fieldElt.classList.add('border-warning')
        }
        this.countEmptyImportantElts()
    }
}