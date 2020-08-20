export default class DeleteAccommodation {

    constructor() {
        this.btnDeleteElt = document.getElementById('modal-btn-delete')
        this.modalConfirmElt = document.getElementById('modal-confirm')
        this.init()
    }

    init() {
        if (this.btnDeleteElt) {
            this.btnDeleteElt.addEventListener('click', () => {
                this.modalConfirmElt.removeAttribute('data-dismiss')
                this.modalConfirmElt.removeAttribute('type')
                this.modalConfirmElt.setAttribute('href', this.btnDeleteElt.getAttribute('data-url'))
            })
        }
    }
}