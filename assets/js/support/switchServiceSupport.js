import SelectType from '../utils/selectType'
import Loader from '../utils/loader'
import AjaxRequest from '../utils/ajaxRequest'

/**
 * Changement du type de service du suivi.
 */
export default class SwitchServiceSupport {

    constructor() {
        this.selectType = new SelectType()
        this.loader = new Loader()
        this.ajaxRequest = new AjaxRequest()

        this.formElt = document.getElementById('modal-new-support')
        this.prefix = 'support_'
        this.serviceSelectElt = document.getElementById(this.prefix + 'service')
        this.subServiceBlockElt = document.getElementById('sub-service-block')
        this.subServiceSelectElt = document.getElementById(this.prefix + 'subService')
        this.deviceSelectElt = document.getElementById(this.prefix + 'device')
        this.referentSelectElt = document.getElementById(this.prefix + 'referent')
        this.referent2SelectElt = document.getElementById(this.prefix + 'referent2')
        this.btnSubmitElt = this.formElt ? this.formElt.querySelector('button[type="submit"]') : null
        this.init()
    }

    init() {
        this.service = this.selectType.getOption(this.serviceSelectElt)
        this.serviceSelectElt.addEventListener('change', () => {
            this.visibleElt(document.querySelector(`div[data-parent-field='service'`), false)
            this.switchService()
        })

        if (this.formElt) {
            this.btnSubmitElt.addEventListener('click', e => {
                if (this.loader.isInLoading()) {
                    e.preventDefault()
                }
            })
        }

        this.visibleElt(document.querySelector(`div[data-parent-field='service'`), this.serviceSelectElt.querySelector('option[selected]').value ? true : false)
        this.visibleElt(this.subServiceBlockElt, this.subServiceSelectElt.querySelectorAll('option').length > 1 ? true : false)
        this.switchService()
    }


    /**
     * Si changement de service.
     */
    switchService() {
        const serviceId = this.selectType.getOption(this.serviceSelectElt)

        if (serviceId) {
            this.loader.on()
            let url = `/service/${serviceId}/devices`
            this.ajaxRequest.init('GET', url, this.response.bind(this), true)
        }
    }

    /**
     * Récupère les résultats de la requête.
     * @param {*} data 
     */
    response(data) {
        const dataJSON = JSON.parse(data)

        this.updateOptionsSelect(this.subServiceSelectElt, dataJSON.subServices)
        this.updateOptionsSelect(this.deviceSelectElt, dataJSON.devices)
        this.updateOptionsSelect(this.referentSelectElt, dataJSON.users)

        if (this.referent2SelectElt) {
            this.updateOptionsSelect(this.referent2SelectElt, dataJSON.users)
        }

        this.visibleElt(document.querySelector(`div[data-parent-field='service'`), this.selectType.getOption(this.serviceSelectElt) ? true : false)
        this.visibleElt(this.subServiceBlockElt, Object.entries(dataJSON.subServices).length > 0 ? true : false)
    }

    /**
     * Met à jour les items d'un select.
     * @param {HTMLElement} selectElt 
     * @param {Object} options 
     */
    updateOptionsSelect(selectElt, options) {
        const selectedOption = this.selectType.getOption(selectElt)

        selectElt.querySelectorAll('option').forEach(optionElt => {
            if (optionElt.value) {
                optionElt.remove()
            }
        })

        const length = Object.keys(options).length

        Object.entries(options).forEach(([key, value]) => {
            let optionElt = document.createElement('option')
            optionElt.setAttribute('value', key)
            optionElt.textContent = value
            if (length == 1 || optionElt.value == selectedOption) {
                optionElt.selected = true
            }
            selectElt.appendChild(optionElt)
        })

        this.loader.off()
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