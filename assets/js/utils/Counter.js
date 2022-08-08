
export default class Counter {
    /**
     * @param {HTMLElement | string} element
     */
    constructor(element = '[data-counter]') {
        this.counterElt = element instanceof HTMLElement ? element : document.querySelector(element)

        if (!this.counterElt) {
            new Error(`No element ${element} for the counter`)
        }
    }

    increment() {
        this.#updateCounter(+1)
    } 
    
    decrement() {
        this.#updateCounter(-1)
    } 
    
    /**
     * @returns {number}
     */
    getValue() {
        return parseInt(this.counterElt.textContent.replace(/(\s|\xc2\xa0){1,}/g, ''))
    } 
    
    /**
     * @param {number} value 
     */
     #updateCounter(value) {
        this.counterElt.textContent = this.getValue() + value
    }
}
