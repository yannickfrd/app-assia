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
                if (this.loader.isInLoading()) {
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
     * Envoie requête Ajax.
     * @param {Object} data 
     */
    sendAjaxRequest() {
        this.loader.on()

        $.ajax({
            url: '/support/change_service',
            type: 'POST',
            data: this.getData(),
            success: data => {
                this.responseAjax(data)
            }
        })
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
        const fields = ['subService', 'device', 'referent', 'referent2', 'originRequest_organization', 'accommodation'] // 'accommodationGroups_0_accommodation'

        fields.forEach(field => {
            let oldElt = document.querySelector('#support_' + field)
            let newElt = html.querySelector('#support_' + field)

            if (field === 'accommodation') {
                oldElt = document.querySelector('#support_accommodationGroups_0_accommodation')
            }

            if (oldElt && newElt) {
                this.updateField(oldElt, newElt)
            }
        })
        this.loader.off()
    }

    /**
     * Met à jour les items d 'un select.
     * @param {HTMLElement} oldElt 
     * @param {HTMLElement} newElt 
     */
    updateField(oldElt, newElt) {
        const option = this.selectType.getOption(oldElt)
        this.selectType.setOption(oldElt, option)

        this.visibleElt(document.querySelector(`div[data-parent-field='service'`), this.selectType.getOption(this.serviceSelectElt) ? true : false)
        this.visibleElt(this.subServiceBlockElt, this.subServiceSelectElt.querySelectorAll('option').length > 1 ? true : false)

        oldElt.innerHTML = newElt.innerHTML

        const optionElts = oldElt.querySelectorAll('option')
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

    // $serviceElt.change(function () {
    //     // ... retrieve the corresponding form.
    //     const $form = $(this).closest('form')
    //     // Simulate form data, but only include the selected sport value.
    //     let data = {}
    //     data[$serviceElt.attr('name')] = $serviceElt.val()
    //     // Submit data via AJAX to the form's action path.
    //     $.ajax({
    //         url: $form.attr('action'),
    //         type: $form.attr('method'),
    //         data: data,
    //         success: function (html) {
    //             // Replace current position field ...
    //             $('#support_device').replaceWith(
    //                 // ... with the returned one from the AJAX response.
    //                 $(html).find('#support_device')
    //             )
    //             // Position field now displays the appropriate positions.
    //         }
    //     })
    // })
}