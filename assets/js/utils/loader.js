/**
 * Animation du loader spinner.
 */
export default class Loader {

    constructor() {
        this.inLoading = false
        this.loaderElt = document.getElementById('loader')
        this.infoElt = this.loaderElt.querySelector('span.small')
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
        this.infoElt.textContent = this.infoElt.dataset.value
    }

    /**
     * @param {String} info 
     */
    updateInfo(info) {
        if (info != this.infoElt.textContent) {
            this.infoElt.textContent = info
        }
    }

    /** 
     * Est en cours de chargement.
     * @return {Boolean}
     */
    isActive() {
        return false === this.loaderElt.classList.contains('d-none')
    }
}