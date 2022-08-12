/** 
 * Système de sauvegarde automatique avec décompte. 
 */
export default class AutoSaver {

/**
 * @param {string} target
 * @param {CallableFunction} callback 
 * @param {number} delay in secondes
 * @param {number} minCount 
 * @param {array} validTargets 
 */
    constructor(target, callback, delay = 10 * 60, minCount = 10, validTargets = null) {
        this.targetElt = document.querySelector(target)
        this.callback = callback
        this.delay = delay * 1000
        this.minCount = minCount
        this.validTargets = validTargets
        this.active = false
        this.count = 0
        this.addEvents()
    }

    /**
     * Ajoute les eventListener.
     */
    addEvents() {
        this.targetElt.addEventListener('keydown', () => this.counter())
        this.targetElt.addEventListener('click', e => {
            if (null === this.validTargets || this.validTargets.includes(e.target.nodeName)) {
                this.counter()
            }
        })
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
            return this.callback()
        }
    }

    /**
     * Compte le nombre de saisie.
     */
    counter() {
        this.count++
    }

    /**
     * Remet à zéro le compteur.
     */
    clear() {
        this.count = 0
        this.active = false
        this.init()
    }

    /**
     * @returns {boolean}
     */
    isActive() {
        return this.active
    }
}