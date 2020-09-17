import AjaxRequest from './ajaxRequest'
import Loader from './loader'
import SwitchServiceSupport from '../support/switchServiceSupport'

/**
 * Requête Ajax pour afficher un nouveau formulaire.
 */
export default class NewForm {

    constructor(btnId, containerId, modalId) {
        this.ajaxRequest = new AjaxRequest()
        this.loader = new Loader()
        this.btnElt = document.getElementById(btnId)
        this.containerElt = document.getElementById(containerId)
        this.modalElt = $('#' + modalId)
        this.init()
    }

    init() {
        if (this.btnElt) {
            this.btnElt.addEventListener('click', e => {
                e.preventDefault()
                if (this.loader.isInLoading() === false) {
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
        this.ajaxRequest.init('GET', btnElt.getAttribute('data-url'), this.response.bind(this), true), {
            once: true
        }
    }

    /**
     * Récupère les données envoyées par le serveur.
     * @param {Array} data 
     */
    response(data) {
        this.containerElt.innerHTML = JSON.parse(data).data.form.content
        this.modalElt.modal('show')
        this.loader.off()
        new SwitchServiceSupport()
    }
}