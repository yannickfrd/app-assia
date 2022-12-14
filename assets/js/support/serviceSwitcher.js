import Ajax from '../utils/ajax'
import Loader from '../utils/loader'
import SiSiaoLogin from '../siSiao/siSiaoLogin'

/**
 * Changement du type de service du suivi.
 */
export default class ServiceSwitcher {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.siSiaoLogin = new SiSiaoLogin()

        this.formElt = document.getElementById('modal-new-support')
        this.serviceSelectElt = document.getElementById('support_service')
        this.subServiceBlockElt = document.getElementById('sub-service-block')
        this.subServiceSelectElt = document.getElementById('support_subService')
        this.btnSubmitElt = this.formElt ? this.formElt.querySelector('button[type="submit"]') : null
        this.init()
    }

    init() {
        this.serviceSelectElt.addEventListener('change', () => {
            this.visibleElt(document.querySelector('div[data-parent-field="service"]'), false)
            this.changeService()
        })

        if (this.formElt) {
            this.btnSubmitElt.addEventListener('click', e => {
                if (this.loader.isActive()) {
                    e.preventDefault()
                }
            })
        }

        this.updateVisibilityFields()
        this.changeService()
        this.siSiaoLogin.init('support__siSiaoImport')
    }
    /**
     * Au changement de service dans la liste déroulante.
     */
    changeService() {
        if (this.serviceSelectElt.value) {
            const url = this.serviceSelectElt.dataset.url
            this.ajax.send('POST', url, this.responseAjax.bind(this), new URLSearchParams(this.getData()))
        }
    }

    /**
     * Donne les données à envoyer.
     */
    getData() {
        const selectElts = [this.serviceSelectElt, this.subServiceSelectElt]
        const data = {}

        selectElts.forEach(selectElt => {
            if (selectElt) {
                data[selectElt.getAttribute('name')] = selectElt.value
            }
        })

        return data
    }

    /**
     * Réponse à la requête Ajax.
     * @param {Object} response 
     */
    responseAjax(response) {
        const html = new DOMParser().parseFromString(response.html.content, "text/xml")
        const fields = ['subService', 'device', 'referent', 'referent2', 'originRequest_organization', 'place']

        fields.forEach(field => {
            let selectElt = document.querySelector('#support_' + field)
            const newSelectElt = html.querySelector('#support_' + field)

            if ('place' === field) {
                selectElt = document.querySelector('#support_placeGroups_0_place')
            }
  
            if (selectElt && newSelectElt) {
                this.updateField(selectElt, newSelectElt)
                if ('referent' === field) {
                    const referent2Elt = document.querySelector('#support_referent2')
                    if (referent2Elt) {
                        this.updateField(referent2Elt, newSelectElt)
                    }
                }
            }
        })

        this.siSiaoLogin.checkConnection()
        this.loader.off()
    }

    /**
     * Met à jour les items d'un select.
     * @param {HTMLElement} selectElt 
     * @param {HTMLElement} newElt 
     */
    updateField(selectElt, newElt) {
        const previousOption = selectElt.value

        this.updateVisibilityFields()

        selectElt.innerHTML = newElt.innerHTML

        selectElt.value = previousOption

        const optionElts = selectElt.querySelectorAll('option')
        if (optionElts.length <= 2) {
            optionElts.forEach(optionElt => {
                if (optionElt != null) {
                    optionElt.selected = true
                }
            })
        }
    }

    /**
     * Vérifie la visibilté des champs Sous-service et Dispositif.
     */
    updateVisibilityFields() {
        this.visibleElt(document.querySelector(`div[data-parent-field='service']`), parseInt(this.serviceSelectElt.value) >= 1)
        this.visibleElt(this.subServiceBlockElt, this.subServiceSelectElt.querySelectorAll('option').length > 1)
    }

    /**
     * Rend visible ou non un élément HTML.
     * @param {HTMLElement} elt 
     * @param {Boolean} visibility 
     */
    visibleElt(elt, visibility) {
        if (visibility === true) {
            elt.classList.remove('d-none')
            setTimeout(() => {
                elt.classList.add('fade-in')
                elt.classList.remove('fade-out')
            }, 10)
        } else {
            elt.classList.add('d-none', 'fade-out')
            elt.classList.remove('fade-in')
        }
    }
}