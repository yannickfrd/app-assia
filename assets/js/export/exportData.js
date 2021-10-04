import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'

export default class ExportData {

    constructor() {
        this.formElt = document.querySelector('#form-search>form')
        this.btnSubmitElts = this.formElt.querySelectorAll('button[type="submit"]')
        this.resultsElt = document.getElementById('results')
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader, 30 * 60)
        this.init()
    }

    init() {
        this.btnSubmitElts.forEach(btnElt => {
            btnElt.addEventListener('click', e => {
                this.loader.on()
                e.preventDefault()
                this.ajax.send('POST', btnElt.dataset.url, this.response.bind(this), new FormData(this.formElt))
            })
        })
    }

    /**
     * Réponse du serveur.
     * @param {Object} data 
     */
    response(data) {
        if (data.type === 'count') {
            this.resultsElt.textContent = parseInt(data.count).toLocaleString('fr') + ' résultat' + (data.count > 0 ? 's' : '') + '.'
        }

        this.loader.off()
        new MessageFlash(data.alert, data.msg)
    }
}