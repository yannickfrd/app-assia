
/**
 * Vérification de la complétion des champs importants.
 */
export default class ImportantFieldsChecker {

    /**
     * @param {string} selectors 
     */
    constructor(selectors) {
        this.accordionElts = document.querySelectorAll(selectors)
        this.ImportantFields = document.querySelectorAll('[data-important]')
        this.init()
    }

    init() {
        this.ImportantFields.forEach(fieldElt => {
            if (!fieldElt.value) {
                fieldElt.classList.add('border-warning')
            }
            fieldElt.addEventListener('change', () => this.onChangeField(fieldElt))
            fieldElt.addEventListener('focusout', () => this.onChangeField(fieldElt))
        })
        this.countEmptyImportantElts()
    }

    /**
     * @param {HTMLElement} fieldElt 
     */
    onChangeField(fieldElt) {
        this.updateClassList(fieldElt)
        this.countEmptyImportantElts()
    }

    /**
     * @param {HTMLElement} fieldElt 
     */
    updateClassList(fieldElt) {
        if (fieldElt.value) {
            return fieldElt.classList.remove('border-warning')
        } 
        return fieldElt.classList.add('border-warning')
    }

    countEmptyImportantElts() {
        this.accordionElts.forEach(accordionElt => {
            let count = 0
            accordionElt.querySelectorAll('[data-important].border-warning').forEach(elt => {
                const parent2xElt = elt.parentElement.parentElement
                if (window.getComputedStyle(parent2xElt).display !== 'none'
                    && window.getComputedStyle(parent2xElt.parentElement).display !== 'none') {
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
}