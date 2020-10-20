/**
 * Animmation du loader spinner.
 */
export default class Loader {

    constructor() {
        this.loaderElt = document.getElementById('loader')
        this.inLoading = false
    }

    /**
     * Active le loader.
     */
    on() {
        this.inLoading = true
        this.loaderElt.classList.remove('d-none')
    }

    /**
     * Désactive le loader.
     */
    off() {
        this.inLoading = false
        this.loaderElt.classList.add('d-none')
    }

    /** 
     * Est en cours de chargement.
     * @return {Boolean}
     */
    isActive() {
        return this.inLoading
    }
}