import DisplayFields from '../utils/displayFields'
import ValidationForm from '../utils/validationForm'
import SelectType from '../utils/selectType'
import ValidationDate from '../utils/validationDate'
import Loader from '../utils/loader'

/**
 * Validation des données du suivi social.
 */
export default class ValidationSupport {

    constructor() {
        this.validationForm = new ValidationForm()
        this.selectType = new SelectType()
        this.loader = new Loader()

        this.prefix = 'support_'
        this.serviceSelectElt = document.getElementById(this.prefix + 'service')
        this.subServiceSelectElt = document.getElementById(this.prefix + 'subService')
        this.statusSelectElt = document.getElementById(this.prefix + 'status')
        this.startDateInputElt = document.getElementById(this.prefix + 'startDate')
        this.endDateInputElt = document.getElementById(this.prefix + 'endDate')
        this.endStatusInputElt = document.getElementById(this.prefix + 'endStatus')
        this.btnSubmitElts = document.querySelectorAll('button[type="submit"]')
        this.dateInputElts = document.querySelectorAll('input[type="date"]')
        this.now = new Date()

        this.init()
    }

    init() {
        this.serviceSelectElt.addEventListener('change', this.changeService.bind(this))
        this.subServiceSelectElt.addEventListener('change', this.changeService.bind(this))

        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt))
        })

        if (this.statusSelectElt) {
            this.startDateInputElt.addEventListener('focusout', this.checkStartDate.bind(this))
            this.endDateInputElt.addEventListener('focusout', this.checkEndDate.bind(this))
            this.endStatusInputElt.addEventListener('change', this.checkEndStatus.bind(this))
            this.checkFormBeforeSubmit()
        }
        this.displayFields()
    }

    /**
     * Vérifie la validité du formualire avant la soumission.
     */
    checkFormBeforeSubmit() {
        this.btnSubmitElts.forEach(btnElt => {

            btnElt.addEventListener('click', e => {
                if (this.statusSelectElt) {
                    this.checkStartDate()
                    this.checkEndDate()
                    this.checkEndStatus()
                }

                if (this.validationForm.checkForm(e) > 0) {
                    e.preventDefault(), {
                        once: true
                    }
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
        let validationDate = new ValidationDate(dateInputElt, this.validationForm)

        if (validationDate.isValid() === false) {
            return
        }
        this.validationForm.validField(dateInputElt)
    }

    /**
     * Vérifie la date de début.
     */
    checkStartDate() {
        let intervalWithNow = (this.now - new Date(this.startDateInputElt.value)) / (24 * 3600 * 1000)
        let status = this.selectType.getOption(this.statusSelectElt)

        if ((this.startDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            return this.validationForm.invalidField(this.startDateInputElt, 'Date invalide.')
        }
        if (intervalWithNow < -30) {
            return this.validationForm.invalidField(this.startDateInputElt, 'La date ne peut pas être supérieur de 30 jours par rapport à la date du jour.')
        }

        if (!intervalWithNow && [2, 3, 4].indexOf(status) != -1) { // Statut = En cours, Supsendu, Terminé
            return this.validationForm.invalidField(this.startDateInputElt, 'Saisie obligatoire.')
        }
        if (intervalWithNow && [1, 5].indexOf(status) != -1) { // Statut =  Orientation/pré-adm.
            return this.validationForm.invalidField(this.startDateInputElt, 'Il ne peut pas y avoir de date début de suivi pour une pré-admission.')
        }
        if (intervalWithNow || (!intervalWithNow && [1, 6].indexOf(status) != -1)) { // Statut =  Orientation/pré-adm. / Liste d'attente
            return this.validationForm.validField(this.startDateInputElt)
        }
    }

    /**
     * Vérifie la date de fin.
     */
    checkEndDate() {
        let startDate = new Date(this.startDateInputElt.value)
        let endDate = new Date(this.endDateInputElt.value)
        let intervalWithStart = (endDate - startDate) / (24 * 3600 * 1000)
        let intervalWithNow = (this.now - endDate) / (24 * 3600 * 1000)

        if ((this.endDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 9)) {
            return this.validationForm.invalidField(this.endDateInputElt, 'Date invalide.')
        }
        if (intervalWithStart < 0) {
            return this.validationForm.invalidField(this.endDateInputElt, 'La date ne peut pas être antérieure au début du suivi.')
        }
        if (intervalWithNow < 0) {
            return this.validationForm.invalidField(this.endDateInputElt, 'La date ne peut pas être postérieure à la date du jour.')
        }
        if (!this.endDateInputElt.value && this.selectType.getOption(this.statusSelectElt) === 4) { // Statut = Terminé
            return this.validationForm.invalidField(this.endDateInputElt, 'La date de fin ne peut pas être vide si le suivi est terminé.')
        }
        if (this.endDateInputElt.value) { // Statut = Terminé
            this.selectType.setOption(this.statusSelectElt, 4)
        }
    }

    /**
     * Vérifie le motif de fin de suivi.
     */
    checkEndStatus() {
        if (!this.endStatusInputElt.value && this.selectType.getOption(this.statusSelectElt) === 4) { // Statut = Terminé
            return this.validationForm.invalidField(this.endStatusInputElt, 'La situation à la fin du suivi ne peut pas être vide.')
        }
        return this.validationForm.validField(this.endStatusInputElt)
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
     * Met à jour les items d'un select.
     * @param {HTMLElement} oldElt 
     * @param {HTMLElement} newElt 
     */
    updateField(oldElt, newElt) {
        const option = this.selectType.getOption(oldElt)
        this.selectType.setOption(oldElt, option)

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
}