import SelectType from '../utils/selectType'
import Loader from '../utils/loader'

/**
 * Changement du type de service du suivi.
 */
export default class SwitchServiceSupport {

    constructor() {
        this.selectType = new SelectType()
        this.loader = new Loader()

        this.formElt = document.getElementById('modal-new-support')
        this.serviceSelectElt = document.getElementById('support_service')
        this.subServiceBlockElt = document.getElementById('sub-service-block')
        this.subServiceSelectElt = document.getElementById('support_subService')
        this.btnSubmitElt = this.formElt ? this.formElt.querySelector('button[type="submit"]') : null
        this.init()
    }

    init() {
        this.service = this.selectType.getOption(this.serviceSelectElt)
        this.serviceSelectElt.addEventListener('change', () => {
            this.visibleElt(document.querySelector('div[data-parent-field="service"'), false)
            this.changeService()
        })

        if (this.formElt) {
            this.btnSubmitElt.addEventListener('click', e => {
                if (this.loader.isActive()) {
                    e.preventDefault()
                }
            })
        }

        this.visibleElt(document.querySelector(`div[data-parent-field='service'`), this.serviceSelectElt.querySelector('option[selected]').value ? true : false)
        this.visibleElt(this.subServiceBlockElt, this.subServiceSelectElt.querySelectorAll('option').length > 1 ? true : false)
        this.changeService()
    }
    /**
     * Au changement de service dans la liste déroulante.
     */
    changeService() {
        if (this.selectType.getOption(this.serviceSelectElt)) {
            this.sendAjaxRequest()
        }
    }

    /**
     * Envoie la requête Ajax.
     */
    async sendAjaxRequest() {
        if (this.selectType.getOption(this.serviceSelectElt)) {
            this.loader.on()
            await fetch('/support/change_service', {
                method: 'POST',
                body: new URLSearchParams(this.getData())
            }).then(response => {
                response.text().then((data) => {
                    return this.responseAjax(data)
                })
            }).catch(error => {
            console.error('Error : ' + error)
            })
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
                data[selectElt.getAttribute('name')] = this.selectType.getOption(selectElt)
            }
        })

        return data
    }

    /**
     * Réponse à la requête Ajax.
     * @param {String} data 
     */
    responseAjax(data) {
        const html = new DOMParser().parseFromString(data, "text/xml")
        const fields = ['subService', 'device', 'referent', 'referent2', 'originRequest_organization', 'accommodation']

        fields.forEach(field => {
            let selectElt = document.querySelector('#support_' + field)
            let newElt = html.querySelector('#support_' + field)

            if (field === 'accommodation') {
                selectElt = document.querySelector('#support_accommodationGroups_0_accommodation')
            }

            if (selectElt && newElt) {
                this.updateField(selectElt, newElt)
            }
        })
        this.loader.off()
    }

    /**
     * Met à jour les items d'un select.
     * @param {HTMLElement} selectElt 
     * @param {HTMLElement} newElt 
     */
    updateField(selectElt, newElt) {
        const previousOption = this.selectType.getOption(selectElt)

        this.visibleElt(document.querySelector(`div[data-parent-field='service'`), this.selectType.getOption(this.serviceSelectElt) ? true : false)
        this.visibleElt(this.subServiceBlockElt, this.subServiceSelectElt.querySelectorAll('option').length > 1 ? true : false)

        selectElt.innerHTML = newElt.innerHTML

        this.selectType.setOption(selectElt, previousOption)

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