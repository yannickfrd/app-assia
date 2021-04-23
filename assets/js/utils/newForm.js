import Ajax from './ajax'
import Loader from './loader'
import SwitchServiceSupport from '../support/switchServiceSupport'
import { Modal } from 'bootstrap'

/**
 * Requête Ajax pour afficher un nouveau formulaire.
 */
export default class NewForm {

    constructor(btnId, containerId, modalId) {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.modalElt = new Modal(document.getElementById(modalId))
        this.btnElt = document.getElementById(btnId)
        this.containerElt = document.getElementById(containerId)
        this.isLoaded = false
        this.init()
    }

    init() {
        if (this.btnElt) {
            this.btnElt.addEventListener('click', e => {
                e.preventDefault()
                if (this.isLoaded) {
                   return this.showForm() 
                }
                if (this.loader.isActive() === false) {
                    return this.sendRequest(this.btnElt)
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
        this.ajax.send('GET', btnElt.getAttribute('data-url'), this.response.bind(this)), {
            once: true
        }
    }

    /**
     * Récupère les données envoyées par le serveur.
     * @param {Object} response 
     */
    response(response) {
        this.containerElt.innerHTML = response.html.content
        this.showForm()
    }

    /**
     * Affiche le formulaire.
     */
    showForm() {
        this.modalElt.show()
        this.loader.off()
        this.isLoaded = true
        new SwitchServiceSupport()
    }
}