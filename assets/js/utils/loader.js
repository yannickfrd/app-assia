// Animmation du loader spinner
export default class Loader {

    constructor(modalId) {
        this.inLoading = false;
        this.loaderElt = document.getElementById("loader");
        this.modalElt = $(modalId);
    }
    // Active le loader
    on(hideModal) {
        this.inLoading = true;
        this.loaderElt.classList.remove("d-none");
        if (hideModal === true) {
            this.hideModal();
        }
    }
    // Désactive le loader
    off(hideModal) {
        this.inLoading = false;
        this.loaderElt.classList.add("d-none");
        if (hideModal === true) {
            this.hideModal();
        }
    }

    isInLoading() {
        return this.inLoading;
    }

    hideModal() {
        this.modalElt.modal("hide");
    }
}