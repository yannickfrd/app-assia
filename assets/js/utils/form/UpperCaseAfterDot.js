/**
 * Add a uppercase after a dot.
 */
 export default class UpperCaseAfterDot {

    /**
     * @param {string} selector 
     */
    constructor(selector) {
        document.querySelectorAll(selector).forEach(elt => {
            elt.addEventListener('keyup', () => this.execute(elt))
        })
    }

    /**
     * @param {HTMLElement} elt 
     */
    execute(elt) {
        const eltValue = elt.value
        if (elt.selectionEnd === eltValue.length) {
            const lastPart = '.' + eltValue.split('.').pop()
            elt.value = eltValue.replace(lastPart, lastPart.replace(/\b\w/, l => l.toUpperCase()))
        }   
    }
}