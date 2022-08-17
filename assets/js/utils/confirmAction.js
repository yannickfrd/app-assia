import Ajax from './ajax'
import AlertMessage from './AlertMessage'
import Loader from './loader'

/**
 * Confirmation d'une action (supprimer, editer, ajouter).
 */
export default class ConfirmAction {

    /**
     * @param {String} selectors 
     * @param {String} btnConfirmId 
     * @param {String} updatedFieldId 
     * @param {Boolean} async 
     */
    constructor(selectors = '', btnConfirmId = 'modal_confirm_btn', updatedFieldId = null, async = false) {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.trElts = document.querySelectorAll(selectors)
        this.modalConfirmElt = document.getElementById(btnConfirmId)
        this.updatedField = document.getElementById(updatedFieldId)
        this.asynch = async
        this.trElt = null
        this.init()
    }

    init() {
        this.trElts.forEach(trElt => {
            const btnElt = trElt.querySelector('button[data-action]')
            if (!btnElt) {
                return
            }
            
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

        if (this.asynch === false) {
            return location.assign(btnElt.dataset.path)
        }
        
        return this.ajax.send('GET', btnElt.dataset.path, this.response.bind(this)), {
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
        new AlertMessage(data.alert, data.msg)
        this.loader.off()
    }

    /**
     * Supprime la ligne correspondante dans le tableau.
     */
    deleteTr() {
        this.trElt.remove()
    }
}