import DisplayFields from '../utils/displayFields'
import ValidationForm from '../utils/validationForm'
import SelectType from '../utils/selectType'
import ValidationDate from '../utils/date/validationDate'
import Ajax from '../utils/ajax'
import Loader from '../utils/loader'

/**
 * Validation des données du suivi social.
 */
export default class ValidationSupport {

    constructor() {
        this.validationForm = new ValidationForm()
        this.selectType = new SelectType()
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

        this.displayFields()

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
                    this.checkEndStatus()
                }
                if (this.loader.isActive() || this.validationForm.checkForm() > 0) {
                    e.preventDefault()
                }
            })
        })
    }

    /**
     * Masque ou affiche les champs conditionnels
     */
    displayFields() {
        new DisplayFields(this.prefix + 'originRequest_', 'orientationDate')
        new DisplayFields(this.prefix, 'startDate')
        new DisplayFields(this.prefix, 'endStatus')
    }

    checkDate(dateInputElt) {
        const validationDate = new ValidationDate(dateInputElt, this.validationForm)

        if (validationDate.isValid() === false) {
            return
        }
        this.validationForm.validField(dateInputElt)
    }

    /**
     * Vérifie la date de début.
     */
    checkStartDate() {
        const intervalWithNow = (this.now - new Date(this.startDateInputElt.value)) / (24 * 3600 * 1000)
        const status = this.statusSelectElt ? this.selectType.getOption(this.statusSelectElt) : null

        if ((this.startDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            return this.validationForm.invalidField(this.startDateInputElt, 'Date invalide.')
        }
        if (intervalWithNow < -30) {
            return this.validationForm.invalidField(this.startDateInputElt, 'La date ne peut pas être supérieure de 30 jours par rapport à la date du jour.')
        }

        if (!intervalWithNow && [2, 3, 4].includes(status)) { // Statut = En cours, Suspendu, Terminé
            return this.validationForm.invalidField(this.startDateInputElt, 'Saisie obligatoire.')
        }
        if (intervalWithNow && [1, 5].includes(status)) { // Statut = Orientation/pré-adm.
            return this.validationForm.invalidField(this.startDateInputElt, 'Il ne peut pas y avoir de date début de suivi pour une pré-admission.')
        }
        if (intervalWithNow || (!intervalWithNow && [1, 6].includes(status))) { // Statut = Orientation/pré-adm. / Liste d'attente
            return this.validationForm.validField(this.startDateInputElt)
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
            return this.validationForm.invalidField(this.endDateInputElt, 'Date invalide.')
        }
        if (intervalWithStart < 0) {
            return this.validationForm.invalidField(this.endDateInputElt, 'La date ne peut pas être antérieure au début du suivi.')
        }
        if (intervalWithNow < 0) {
            return this.validationForm.invalidField(this.endDateInputElt, 'La date ne peut pas être postérieure à la date du jour.')
        }
        // if (!this.endDateInputElt.value && this.statusSelectElt && this.selectType.getOption(this.statusSelectElt) === 4) { // Statut = Terminé
        //     return this.validationForm.invalidField(this.endDateInputElt, 'La date de fin ne peut pas être vide si le suivi est terminé.')
        // }
        if (this.endDateInputElt.value && this.statusSelectElt) { // Statut = Terminé
            this.selectType.setOption(this.statusSelectElt, 4)
        }
    }

    /**
     * Vérifie le motif de fin de suivi.
     */
    checkEndStatus() {
        if (this.endDateInputElt.value && !this.selectType.getOption(this.endStatusSelectElt)) {
            return this.validationForm.invalidField(this.endStatusSelectElt, 'La situation à la fin du suivi ne peut pas être vide.')
        }
        return this.validationForm.validField(this.endStatusSelectElt)
    }
    
    /**
     * Envoie la requête Ajax.
     */
    sendAjaxRequest() {
        if (this.selectType.getOption(this.serviceSelectElt)) {
            this.ajax.send('POST', '/support/change_service', this.responseAjax.bind(this), new URLSearchParams(this.getData()))
        }
    }

    /**
     * Donne les données à envoyer.
     */
    getData() {
        const selectElts = [this.serviceSelectElt, this.subServiceSelectElt]
        const data = {}

        selectElts.forEach(selectElt => {
            data[selectElt.getAttribute('name')] = this.selectType.getOption(selectElt)
        })
        return data
    }

    /**
     * Réponse à la requête Ajax.
     * @param {String} data 
     */
    responseAjax(data) {
        const html = new DOMParser().parseFromString(data, "text/xml")
        const fields = ['subService', 'device', 'referent', 'referent2', 'originRequest_organization', 'place']

        fields.forEach(field => {
            let selectElt = document.querySelector('#support_' + field)
            let newSelectElt = html.querySelector('#support_' + field)

            if (field === 'place') {
                selectElt = document.querySelector('#support_placeGroups_0_place')
            }

            if (selectElt && newSelectElt) {
                this.updateField(selectElt, newSelectElt)
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
        const previousOption = this.selectType.getOption(selectElt)

        selectElt.innerHTML = newSelectElt.innerHTML

        this.selectType.setOption(selectElt, previousOption)

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