import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import AutoSaver from '../utils/form/autoSaver'

/**
 * Requête Ajax pour mettre à jour les informations individuelles.
 */
export default class UpdateEvaluation {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.autoSaver = new AutoSaver(this.sendRequest.bind(this), this.formElt, 5 * 60, 20, ['SELECT', 'INPUT', 'TEXTAREA'])
        this.formElt = document.querySelector('form[name="evaluation"]')
        this.btnSubmitElts = this.formElt.querySelectorAll('button[type="submit"]')
        this.init()
    }

    init() {
        this.btnSubmitElts.forEach(btnSubmitElt => {
            this.url = btnSubmitElt.dataset.url
            btnSubmitElt.addEventListener('click', e => this.save(e))
        })
        this.autoSaver.init()
    }

    /**
     * Essaie de sauvegarder.
     * @param {Event} e 
     */
    save(e) {
        e.preventDefault()
        if (this.loader.isActive()) {
            return null
        }
        this.sendRequest()
    }

    sendRequest() {
        this.ajax.send('POST', this.url, this.response.bind(this), new FormData(this.formElt))
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    response(response) {
        console.log(response)
        this.loader.off()

        if (!response.alert) {
            console.error(response)
            return new MessageFlash('danger', 'Attention, une erreur s\'est produite.')
        }
        
        new MessageFlash(response.alert, response.msg)

        if (!response.data) {
            return null
        }

        document.getElementById('evaluation-updateAt').textContent = '(modifiée le ' + response.data.updatedAt + ')'
    }
}