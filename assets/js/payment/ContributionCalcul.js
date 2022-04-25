import Ajax from '../utils/ajax'
import Loader from '../utils/loader'
import { Modal, Popover } from 'bootstrap'

/**
 * Récupère les ressources et calcul le montant à payer du suivi au clic sur le bouton.
 */
export default class ContributionCalcul {

    /**
     * @param {HTMLFormElement} formElt 
     * @param {CallableFunction} callback 
     */
    constructor(formElt = null, callback = null) {
        this.formElt = formElt
        this.callback = callback
        this.loader = new Loader()
        this.ajax = null

        this.calculContribBtnElt = document.getElementById('calcul-contribution-btn')
        this.showCalculContribBtnElt = document.getElementById('show-calcul-contribution-btn')
        this.contribCalculModal = new Modal(document.getElementById('contribution-calcul-modal'))

        this.paymentFormElt = document.querySelector('form[name=payment]')

        this.resourcesChecked = false // Ressources vérifiées dans la base de données
        this.resourcesAmt = null
        this.contributionAmt = null
        this.toPayAmt = null
        this.rentAmt = null

        this.init()
    }

    init() {
        this.calculContribBtnElt.addEventListener('click', e => {
            e.preventDefault()
            if (false === this.loader.isActive()) {
                if (null === this.ajax) {
                    this.ajax = new Ajax(this.loader)
                }
                this.loader.on() 
                const url = this.calculContribBtnElt.dataset.url
                const data = this.formElt ? new FormData(this.formElt) : null

                this.ajax.send('POST', url, this.responseAjax.bind(this), data)
            }
        })

        this.showCalculContribBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.contribCalculModal.show()
        })
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    responseAjax(response) {
        switch (response.action) {
            case 'get_contribution':
                this.getContribution(response.data)
                break
            }
        this.loader.off()
        this.loading = false
    }

    /**
     * Donne le montant des ressources du ménage.
     * @param {Object} data 
     */
    getContribution(data) {
        const modalBody = document.getElementById('contribution-calcul-modal').querySelector('.modal-body')
        modalBody.innerHTML = data.view
        modalBody.querySelectorAll('[data-toggle="popover"]').forEach(popover => {  
            new Popover(popover)
        })

        this.contribCalculModal.show()
        this.showCalculContribBtnElt.classList.remove('d-none')

        if (this.callback) {
            this.callback(data.payment)
        }
    }
}