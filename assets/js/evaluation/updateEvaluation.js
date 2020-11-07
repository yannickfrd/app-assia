import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import AutoSave from '../utils/autoSave'

// Requête Ajax pour mettre à jour les informations individuelles
export default class UpdateEvaluation {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.formElt = document.querySelector('form[name="evaluation"]')
        this.btnSubmitElts = this.formElt.querySelectorAll('button[type="submit"]')
        this.editMode = document.querySelector('div[data-edit-mode]').getAttribute('data-edit-mode')
        this.autoSave = new AutoSave(this.sendRequest.bind(this), this.formElt, 5 * 60, 20)
        this.init()
    }

    init() {
        this.btnSubmitElts.forEach(btnSubmitElt => {
            this.url = btnSubmitElt.getAttribute('data-url')
            btnSubmitElt.addEventListener('click', e => this.save(e))
        })
        this.autoSave.init()
    }

    /**
     * Essaie de sauvegarder.
     * @param {Event} e 
     */
    save(e) {
        if (this.editMode === 'true') {
            e.preventDefault()
            if (this.loader.isActive() === false) {
                this.sendRequest()
            }
        }
    }

    sendRequest() {
        this.ajax.send('POST', this.url, this.response.bind(this), new FormData(this.formElt))
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    response(response) {
        this.loader.off()
        new MessageFlash(response.alert, response.msg)
    }
}