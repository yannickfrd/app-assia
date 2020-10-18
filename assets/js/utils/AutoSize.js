/**
 * Ajuste la taille des éléments HTML (Texarea).
 */
export default class AutoSize {
    
    /**
     * @param {String} selectors
     * @param {Number} rows
     */ 
    constructor(selectors = 'textarea', rows = 2) {
        this.rows = rows
        this.lineHeight = 22.8
        document.querySelectorAll(selectors).forEach(elt => this.observeElt(elt))
    }

    /**
     * Ajuste la taille de l'élément HTML.
     * @param {elt} elt 
     */
    observeElt(elt) {        
        const observer = new IntersectionObserver(() => {
            if (elt.scrollHeight > 0) {
                this.setRows(elt)
                elt.addEventListener('input', () => {
                    this.setRows(elt)
                })
                observer.unobserve(elt)
            }
        })
        observer.observe(elt)
    }

    /**
     * Définit le nombre de lignes
     * @param {HTMLElement} elt 
     */
    setRows(elt) {
        elt.setAttribute('rows', this.rows)
        if (elt.value) {
            const style = window.getComputedStyle(elt)
            elt.setAttribute('rows', Math.round(elt.scrollHeight / parseFloat(style.lineHeight)))
        }
    }
}