import {Modal} from 'bootstrap'

export default class ModalConfirmation {
    /**
     * @param {string} selector 
     * @param {string | null} buttonSelector 
     * @param {CallableFunction} callback 
     */
    constructor(modalSelector, buttonSelector, callback) {
        this.modalConfirmElt = document.querySelector(modalSelector)

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
     * @param {CallableFunction} callback 
     */
    setNewCallback(callback) {
        this.btnConfirmElt.removeEventListener('click', this.callback)
        this.btnConfirmElt.addEventListener('click', () => callback())
        this.callback = callback
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
