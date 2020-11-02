import AjaxRequest from './ajaxRequest'
import MessageFlash from './messageFlash'
import Loader from './loader'

/**
 * Supprime une ligne d'un tableau et l'objet associé via requête AJAX.
 */
export default class RemoveTableRow {

    constructor(selectors = '', btnConfirmId = 'modal-confirm', updatedFieldId = null) {
        this.loader = new Loader()
        this.ajaxRequest = new AjaxRequest(this.loader)
        this.trElts = document.querySelectorAll(selectors)
        this.modalConfirmElt = document.getElementById(btnConfirmId)
        this.updatedField = document.getElementById(updatedFieldId)
        this.trElt = null
        this.init()
    }

    init() {
        this.trElts.forEach(trElt => {
            let btnElt = trElt.querySelector('button.js-remove')
            btnElt.addEventListener('click', e => {
                e.preventDefault()
                this.modalConfirmElt.addEventListener('click', this.sendRequest.bind(this, btnElt, trElt), {
                    once: true
                })
            })
        })
    }

    /**
     * Envoie la requête Ajax après confirmation de l'action.
     * @param {HTMLButtonElement} btnElt 
     * @param {HTMLTableRowElement} trElt 
     */
    sendRequest(btnElt, trElt) {
        this.loader.on()
        this.trElt = trElt
        this.ajaxRequest.send('GET', btnElt.getAttribute('data-url'), this.response.bind(this), true), {
            once: true
        }
    }

    /**
     * Récupère les données envoyées par le serveur.
     * @param {Object} data 
     */
    response(data) {
        if (data.action === 'delete') {
            this.deleteTr(this.trElt)
            this.updatedField ? this.updatedField.value = data.data : null
        }
        new MessageFlash(data.alert, data.msg)
        this.loader.off()
    }

    /**
     * Supprime la ligne correspondante dans le tableau.
     */
    deleteTr() {
        this.trElt.remove()
    }
}