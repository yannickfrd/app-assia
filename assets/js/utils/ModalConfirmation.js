import {Modal} from 'bootstrap'

export default class ModalConfirmation {
    /**
     * @param {string | null} selector 
     * @param {string | null} buttonSelector 
     * @param {CallableFunction} callback 
     */
    constructor(modalSelector, buttonSelector, callback) {
        this.modalConfirmElt = document.querySelector(modalSelector ?? '#modal_confirm')

        if (!this.modalConfirmElt) {
            return
        }

        this.modalBodyElt = this.modalConfirmElt.querySelector('.modal-body')
        this.btnConfirmElt = this.modalConfirmElt.querySelector(buttonSelector ?? 'button[data-action="confirm"]')
        this.callback = callback

        this.confirmModal = new Modal(this.modalConfirmElt)

        this.#init()
    }

    #init() {
        this.btnConfirmElt.addEventListener('click', () => this.callback())
    }

    /**
     * @param {CallableFunction} action 
     */
    setNewAction(action) {
        this.btnConfirmElt.removeEventListener('click', this.callback)
        this.btnConfirmElt.addEventListener('click', () => callback())
        this.callback = action
    }

    /**
     * @param {string} id
     */
    show() {
        this.confirmModal.show()
    }

    hide() {
        this.confirmModal.hide()
    }
}
