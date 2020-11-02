import AjaxRequest from './ajaxRequest'
import Loader from './loader'
import SwitchServiceSupport from '../support/switchServiceSupport'
import { Modal } from 'bootstrap'

/**
 * Requête Ajax pour afficher un nouveau formulaire.
 */
export default class NewForm {

    constructor(btnId, containerId, modalId) {
        this.loader = new Loader()
        this.ajaxRequest = new AjaxRequest(this.loader)
        this.modalElt = new Modal(document.getElementById(modalId))
        this.btnElt = document.getElementById(btnId)
        this.containerElt = document.getElementById(containerId)
        this.init()
    }

    init() {
        if (this.btnElt) {
            this.btnElt.addEventListener('click', e => {
                e.preventDefault()
                if (this.loader.isActive() === false) {
                    this.sendRequest(this.btnElt)
                }
            })
        }
    }

    /**
     * Envoie la requête Ajax.
     * @param {HTMLButtonElement} btnElt 
     */
    sendRequest(btnElt) {
        this.loader.on()
        this.ajaxRequest.send('GET', btnElt.getAttribute('data-url'), this.response.bind(this), true), {
            once: true
        }
    }

    /**
     * Récupère les données envoyées par le serveur.
     * @param {Object} data 
     */
    response(data) {
        this.containerElt.innerHTML = data.data.form.content
        this.modalElt.show()
        this.loader.off()
        new SwitchServiceSupport()
    }
}