import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import { Modal } from 'bootstrap'

export default class Rdvs {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.rdvElts = document.querySelectorAll('.js-rdv')
        this.modalConfirmElt = document.getElementById('modal-confirm')
        this.modalDeleteElt = new Modal(document.getElementById('modal-block'))
        this.rdvId = null

        this.init()
    }

    init() {
        this.rdvElts.forEach(rdvElt => {
            const btnDeleteElt = rdvElt.querySelector('button.js-delete')
            if (btnDeleteElt) {
                btnDeleteElt.addEventListener('click', () => {
                    this.modalDeleteElt.show()
                    this.modalConfirmElt.setAttribute('data-url', btnDeleteElt.getAttribute('data-url'))
                })
            }
        })  
        
        this.modalConfirmElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.modalConfirmElt.getAttribute('data-url'), this.responseAjax.bind(this))
        })
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    responseAjax(response) {
        if (response.code === 200) {
            switch (response.action) {
                case 'delete':
                    this.deleteRdv(response.rdv)
                    break
                }
            }
            new MessageFlash(response.alert, response.msg)
            this.loader.off()
    }

    /**
     * Supprime la ligne du rendez-vous.
     * @param {Object} rdv 
     */
    deleteRdv(rdv) {
        document.getElementById('rdv-' + rdv.id).remove()
    }
}