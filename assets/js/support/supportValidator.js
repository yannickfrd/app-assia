import FormValidator from '../utils/form/formValidator'
import FieldDisplayer from '../utils/form/fieldDisplayer'
import DateValidator from '../utils/date/dateValidator'
import Ajax from '../utils/ajax'
import Loader from '../utils/loader'

/**
 * Validation des données du suivi social.
 */
export default class SupportValidator extends FormValidator
{
    constructor() {
        super()

        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.prefix = 'support_'
        this.serviceSelectElt = document.getElementById(this.prefix + 'service')
        this.subServiceSelectElt = document.getElementById(this.prefix + 'subService')
        this.statusSelectElt = document.getElementById(this.prefix + 'status')
        this.startDateInputElt = document.getElementById(this.prefix + 'startDate')
        this.endDateInputElt = document.getElementById(this.prefix + 'endDate')
        this.endStatusSelectElt = document.getElementById(this.prefix + 'endStatus')
        this.btnSubmitElts = document.querySelectorAll('button[type="submit"]')
        this.dateInputElts = document.querySelectorAll('input[type="date"]')
        this.now = new Date()

        this.init()
    }

    init() {
        document.querySelectorAll('div[data-parent-field]').forEach(elt => {
            new FieldDisplayer(elt)
        })

        this.serviceSelectElt.addEventListener('change', () => this.sendAjaxRequest())
        this.subServiceSelectElt.addEventListener('change', () => this.sendAjaxRequest())

        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', () => this.checkDate(dateInputElt))
        })

        if (this.startDateInputElt) {
            this.startDateInputElt.addEventListener('focusout', () => this.checkStartDate())
            this.endDateInputElt.addEventListener('focusout', () => this.checkEndDate())
            this.endStatusSelectElt.addEventListener('change', () => this.checkEndStatus())
        }
        this.checkFormBeforeSubmit()

        this.visibleElt(this.subServiceSelectElt.parentNode.parentNode, this.subServiceSelectElt.querySelectorAll('option').length > 1 ? true : false)
    }

    /**
     * Vérifie la validité du formualire avant la soumission.
     */
    checkFormBeforeSubmit() {
        this.btnSubmitElts.forEach(btnElt => {
            btnElt.addEventListener('click', e => {
                if (this.startDateInputElt) {
                    this.checkStartDate()
                    this.checkEndDate()
                }
                this.checkEndStatus()

                if (this.loader.isActive() || this.checkForm() > 0) {
                    e.preventDefault()
                }
            })
        })
    }


    checkDate(dateInputElt) {
        const dateValidator = new DateValidator(dateInputElt, this)

        if (dateValidator.isValid() === false) {
            return
        }
        this.validField(dateInputElt)
    }

    /**
     * Vérifie la date de début.
     */
    checkStartDate() {
        const intervalWithNow = (this.now - new Date(this.startDateInputElt.value)) / (24 * 3600 * 1000)
        const status = this.statusSelectElt ? parseInt(this.statusSelectElt.value) : null

        if ((this.startDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            return this.invalidField(this.startDateInputElt, 'Date invalide.')
        }
        if (intervalWithNow < -30) {
            return this.invalidField(this.startDateInputElt, 'La date ne peut pas être supérieure de 30 jours par rapport à la date du jour.')
        }
        if (!intervalWithNow && [2, 3, 4].includes(status)) { // Statut = En cours, Suspendu, Terminé
            return this.invalidField(this.startDateInputElt, 'Saisie obligatoire.')
        }
        if (intervalWithNow && [1, 5].includes(status)) { // Statut = Orientation/pré-adm.
            return this.invalidField(this.startDateInputElt, 'Il ne peut pas y avoir de date début de suivi pour une pré-admission.')
        }
        if (intervalWithNow || (!intervalWithNow && [1, 6].includes(status))) { // Statut = Orientation/pré-adm. / Liste d'attente
            return this.validField(this.startDateInputElt)
        }
    }

    /**
     * Vérifie la date de fin.
     */
    checkEndDate() {
        const startDate = new Date(this.startDateInputElt.value)
        const endDate = new Date(this.endDateInputElt.value)
        const intervalWithStart = (endDate - startDate) / (24 * 3600 * 1000)
        const intervalWithNow = (this.now - endDate) / (24 * 3600 * 1000)

        if ((this.endDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 9)) {
            return this.invalidField(this.endDateInputElt, 'Date invalide.')
        }
        if (intervalWithStart < 0) {
            return this.invalidField(this.endDateInputElt, 'La date ne peut pas être antérieure au début du suivi.')
        }
        if (intervalWithNow < 0) {
            return this.invalidField(this.endDateInputElt, 'La date ne peut pas être postérieure à la date du jour.')
        }
        if (this.endDateInputElt.value && this.statusSelectElt) { // Statut = Terminé
            this.statusSelectElt.value = '4'
        }
    }
    
    checkEndStatus() {
        if (this.endDateInputElt && this.endDateInputElt.value && !this.endStatusSelectElt.value) {
            return this.invalidField(this.endStatusSelectElt, 'Saisie obligatoire.')
        }
        return this.validField(this.endStatusSelectElt)
    }

    /**
     * Envoie la requête Ajax.
     */
    sendAjaxRequest() {
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
            data[selectElt.getAttribute('name')] = selectElt.value
        })
        return data
    }

    /**
     * Réponse à la requête Ajax.
     * @param {String} response 
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
        this.loader.off()
    }

    /**
     * Met à jour les items d'un select.
     * @param {HTMLElement} selectElt 
     * @param {HTMLElement} newSelectElt 
     */
    updateField(selectElt, newSelectElt) {
        const previousOption = selectElt.value

        selectElt.innerHTML = newSelectElt.innerHTML

        selectElt.value = previousOption

        const optionElts = selectElt.querySelectorAll('option')
        if (optionElts.length <= 2) {
            optionElts.forEach(optionElt => {
                if (optionElt != null) {
                    optionElt.selected = true
                }
            })
        }
        this.visibleElt(selectElt.parentNode.parentNode, selectElt.querySelectorAll('option').length > 1 ? true : false)
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