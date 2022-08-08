export default class DeletePlace {

    constructor() {
        this.deleteBtnElt = document.getElementById('modal_delete_btn')
        this.modalConfirmElt = document.getElementById('modal_confirm_btn')
        this.init()
    }

    init() {
        if (this.deleteBtnElt) {
            this.deleteBtnElt.addEventListener('click', () => {
                this.modalConfirmElt.removeAttribute('data-dismiss')
                this.modalConfirmElt.removeAttribute('type')
                this.modalConfirmElt.setAttribute('href', this.deleteBtnElt.dataset.path)
            })
        }
    }
}