import DisplayFields from '../utils/displayFields'
import ValidationForm from '../utils/validationForm'
import SelectType from '../utils/selectType'
import ValidationDate from '../utils/validationDate'
import Loader from '../utils/loader'

/**
 * Validation des données d'un suivi hôtel.
 */
export default class ValidationHotelSupport {

    constructor() {
        this.validationForm = new ValidationForm()
        this.selectType = new SelectType()
        this.loader = new Loader()

        this.prefix = 'support_'

        this.btnSubmitElts = document.querySelectorAll('button[type="submit"]')
        this.dateInputElts = document.querySelectorAll('input[type="date"]')

        this.orientationDateElt = document.getElementById('support_originRequest_orientationDate')
        this.startDateElt = document.getElementById(this.prefix + 'startDate')
        this.evaluationDateElt = document.getElementById(this.prefix + 'hotelSupport_evaluationDate')
        this.agreementDateElt = document.getElementById(this.prefix + 'hotelSupport_agreementDate')
        this.endDateElt = document.getElementById(this.prefix + 'endDate')

        this.AseMab = 15
        this.AseHeb = 16
        this.HotelSupport = 19
        this.HotelUrg = 20

        this.init()
    }

    init() {
        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt))
        })

        this.displayFields()

        let dateElts = [this.orientationDateElt, this.startDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.validationForm.checkIntervalBeetweenDates(
                    this.orientationDateElt,
                    this.startDateElt,
                    'La date ne peut pas être antérieure à la date de la demande.')
            })
        })

        const msgStartDate = 'La date ne peut pas être antérieure au début de l\'accompagnement.'

        dateElts = [this.startDateElt, this.evaluationDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.validationForm.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.evaluationDateElt,
                    msgStartDate)
            })
        })

        dateElts = [this.startDateElt, this.agreementDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.validationForm.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.agreementDateElt,
                    msgStartDate)
            })
        })

        dateElts = [this.startDateElt, this.endDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.validationForm.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.endDateElt,
                    msgStartDate)
            })
        })
    }

    /**
     * Masque ou affiche les champs conditionnels
     */
    displayFields() {
        new DisplayFields(this.prefix, 'device')
        new DisplayFields('support_originRequest_', 'orientationDate')
        new DisplayFields('support_originRequest_', 'organization')
        new DisplayFields(this.prefix + 'hotelSupport_', 'evaluationDate')
        new DisplayFields(this.prefix, 'startDate')
        new DisplayFields(this.prefix, 'endDate')
    }

    /**
     * Vérifie la valeur du champ date
     * @param {HTMLElement} inputElt 
     */
    checkDate(inputElt) {
        let validationDate = new ValidationDate(inputElt, this.validationForm)

        if (validationDate.isValid() === false) {
            return false
        }
        this.validationForm.validField(inputElt)
    }
}