import AjaxRequest from '../utils/ajaxRequest'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'

// Requête Ajax pour mettre à jour les informations individuelles
export default class UpdateEvaluation {

    constructor() {
        this.ajaxRequest = new AjaxRequest()
        this.formElt = document.querySelector('form[name="evaluation"]')
        this.btnSubmitElts = this.formElt.querySelectorAll('button[type="submit"]')
        this.editMode = document.querySelector('div[data-edit-mode]').getAttribute('data-edit-mode')
        this.loader = new Loader()
        this.init()
    }

    init() {
        this.btnSubmitElts.forEach(btnSubmitElt => {
            btnSubmitElt.addEventListener('click', e => {
                if (this.editMode === 'true') {
                    e.preventDefault()
                    if (this.loader.isActive() === false) {
                        this.loader.on()
                        let formToString = new URLSearchParams(new FormData(this.formElt)).toString()
                        this.ajaxRequest.send('POST', btnSubmitElt.getAttribute('data-url'), this.response.bind(this), true, formToString)
                    }
                }
            })
        })
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