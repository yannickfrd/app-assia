import DisplayFields from '../utils/displayFields'
import ValidationForm from '../utils/validationForm'
import SelectType from '../utils/selectType'
import ValidationDate from '../utils/date/validationDate'
import Loader from '../utils/loader'

/**
 * Validation des données d'un suivi hôtel.
 */
export default class ValidationAvdlSupport {

    constructor() {
        this.validationForm = new ValidationForm()
        this.selectType = new SelectType()
        this.loader = new Loader()

        this.prefix = 'support_avdl_'

        this.btnSubmitElts = document.querySelectorAll('button[type="submit"]')
        this.dateInputElts = document.querySelectorAll('input[type="date"]')

        this.orientationDateElt = document.getElementById('support_originRequest_orientationDate')
        this.diagStartDateElt = document.getElementById(this.prefix + 'diagStartDate')
        this.diagEndDateElt = document.getElementById(this.prefix + 'diagEndDate')
        this.supportStartDateElt = document.getElementById(this.prefix + 'supportStartDate')
        this.supportEndDateElt = document.getElementById(this.prefix + 'supportEndDate')

        this.init()
    }

    init() {
        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt))
        })

        this.displayFields()

        this.diagStartDateElt.addEventListener('focusout', () => {
            this.validationForm.checkIntervalBeetweenDates(
                this.orientationDateElt,
                this.diagStartDateElt,
                'La date ne peut pas être antérieure à la date de mandatement.')
        })

        this.diagEndDateElt.addEventListener('focusout', () => {
            this.validationForm.checkIntervalBeetweenDates(
                this.diagStartDateElt,
                this.diagEndDateElt,
                'La date de fin ne peut pas être antérieure au début du diagnostic.')
        })

        this.supportStartDateElt.addEventListener('focusout', () => {
            this.validationForm.checkIntervalBeetweenDates(
                this.diagEndDateElt,
                this.supportStartDateElt,
                'La date ne peut pas être antérieure à la fin du diagnostic.')
        })

        this.supportEndDateElt.addEventListener('focusout', () => {
            this.validationForm.checkIntervalBeetweenDates(
                this.supportStartDateElt,
                this.supportEndDateElt,
                'La date ne peut pas être antérieure au début de l\'accompagnement.')
        })
    }

    /**
     * Masque ou affiche les champs conditionnels
     */
    displayFields() {
        new DisplayFields('support_', 'device')
        new DisplayFields('support_originRequest_', 'orientationDate')
        new DisplayFields('support_originRequest_', 'organization')
        new DisplayFields(this.prefix, 'diagStartDate')
        new DisplayFields(this.prefix, 'supportStartDate')
        new DisplayFields(this.prefix, 'supportEndDate')
        new DisplayFields(this.prefix, 'propoHousingDate')
        new DisplayFields(this.prefix, 'propoResult', [1])
    }

    /**
     * Vérifie la valeur du champ date
     * @param {HTMLElement} inputElt 
     */
    checkDate(inputElt) {
        let validationDate = new ValidationDate(inputElt, this.validationForm)

        if (validationDate.isValid() === false || validationDate.isNotAfterOneYear() === false) {
            return false
        }
        this.validationForm.validField(inputElt)
    }
}