/**
 * Animmation du loader spinner.
 */
export default class Loader {

    constructor(modalId) {
        this.loaderElt = document.getElementById('loader')
        this.inLoading = false
        this.modalElt = $(modalId)
    }

    /**
     * Active le loader.
     * @param {Boolean} hideModal 
     */
    on(hideModal) {
        this.inLoading = true
        this.loaderElt.classList.remove('d-none')
        if (hideModal === true) {
            this.hideModal()
        }
    }

    /**
     * Désactive le loader.
     * @param {Boolean} hideModal 
     */
    off(hideModal) {
        this.inLoading = false
        this.loaderElt.classList.add('d-none')
        if (hideModal === true) {
            this.hideModal()
        }
    }

    /** 
     * Est en cours de chargement.
     * @return {Boolean}
     */
    isInLoading() {
        return this.inLoading
    }

    /**
     * Masque la fenêtre modale active
     */
    hideModal() {
        this.modalElt.modal('hide')
    }
}