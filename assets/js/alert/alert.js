import Ajax from '../utils/ajax.js'
import Loader from '../utils/loader'
import MessageFlash from '../utils/messageFlash'

export default class Alert {
    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader);
        this.checkboxStatusElts = document.querySelectorAll('.alert-checkbox-status');
        this.init()
    }
    init() {
        this.checkboxStatusElts.forEach(checkboxStatusElt => {
            checkboxStatusElt.addEventListener('click', e => {
                this.requestToggleStatusAlert(checkboxStatusElt)
            })
        });
    }
    /**
     * @param {HTMLInputElement} checkboxElt 
     */
    requestToggleStatusAlert(checkboxElt) {
        let id = checkboxElt.id.split('alert-checkbox-status-')[1];
        this.ajax.send('GET', "/alert/" + id + "/toggle-status", this.responseAjax.bind(this))
    }

    /**
 * Réponse du serveur.
 * @param {Object} response 
 */
    responseAjax(response) {

        if (response.msg) {
            new MessageFlash(response.alert, response.msg)
        }
        this.loader.off()
    }
}