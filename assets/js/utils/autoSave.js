/** 
 * Système de sauvegarde automatique avec décompte. 
 */
export default class AutoSave {

/**
 * @param {CallableFunction} callback 
 * @param {HTMLElement} htmlElt 
 * @param {Number} delay in secondes
 * @param {Number} minCount 
 */
    constructor(callback, htmlElt, delay = 10 * 60, minCount = 10) {
        this.callback = callback
        this.htmlElt = htmlElt
        this.delay = delay * 1000
        this.minCount = minCount
        this.active = false
        this.count = 0

        this.htmlElt.addEventListener('keydown', () => this.counter())
        this.htmlElt.addEventListener('click', () => this.counter())
    }
    /**
     * Compte le nombre de saisie.
     */
    counter() {
        this.count++
    }

    /**
     * Timer pour la sauvegarde automatique.
     */
    init() {
        clearInterval(this.countdownID)
        this.countdownID = setTimeout(this.init.bind(this), this.delay)
        if (this.count > this.minCount) {
            this.count = 0
            this.active = true
            console.log('Auto save...')
            this.callback();
        }
    }

    /**
     * Remet à zéro le compteur.
     * @param {Event} e 
     */
    clear(e) {
        e.preventDefault()
        this.count = 0
        this.active = false
        clearInterval(this.countdownID)
    }
}