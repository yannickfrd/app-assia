// Animmation du loader spinner
export default class Loader {

    constructor(modalId) {
        this.loaderElt = document.getElementById("loader");
        this.modalElt = $(modalId);
    }
    // Active le loader
    on(hideModal) {
        this.loaderElt.classList.remove("d-none");
        if (hideModal === true) {
            this.hideModal();
        }
    }
    // Désactive le loader
    off(hideModal) {
        this.loaderElt.classList.add("d-none");
        if (hideModal === true) {
            this.hideModal();
        }
    }

    hideModal() {
        this.modalElt.modal("hide");
    }
}