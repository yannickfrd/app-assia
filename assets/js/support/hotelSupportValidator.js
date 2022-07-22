import FormValidator from '../utils/form/formValidator'
import DateValidator from '../utils/date/dateValidator'
import SelectManager from '../utils/form/SelectManager'

/**
 * Validation des données d'un suivi hôtel.
 */
export default class HotelSupportValidator extends FormValidator {

    constructor() {
        super()

        this.prefix = 'support_'

        this.btnSubmitElts = document.querySelectorAll('button[type="submit"]')
        this.dateInputElts = document.querySelectorAll('input[type="date"]')

        this.deviceSelectElt = document.getElementById(this.prefix + 'device')
        this.deviceSelectElt = document.getElementById(this.prefix + 'device')
        this.referentSelectElt = document.getElementById(this.prefix + 'referent')
        this.startDateElt = document.getElementById(this.prefix + 'startDate')
        this.endDateElt = document.getElementById(this.prefix + 'endDate')

        this.orientationDateElt = document.getElementById('support_originRequest_orientationDate')

        this.statusSelectElt = document.getElementById(this.prefix + 'status')
        this.emergencyActionRequestSelectElt = document.getElementById(this.prefix + 'hotelSupport_emergencyActionRequest')
        this.reasonNoInclusionSelectElt = document.getElementById(this.prefix + 'hotelSupport_reasonNoInclusion')
        this.evaluationDateElt = document.getElementById(this.prefix + 'hotelSupport_evaluationDate')
        this.agreementDateElt = document.getElementById(this.prefix + 'hotelSupport_agreementDate')
        this.emergencyActionDoneSelectElt = document.getElementById(this.prefix + 'hotelSupport_emergencyActionDone')
        this.levelSupportSelectElt = document.getElementById(this.prefix + 'hotelSupport_levelSupport')
        this.departmentAnchorSelectElt = document.getElementById(this.prefix + 'hotelSupport_departmentAnchor')
        this.recommendationSelectElt = document.getElementById(this.prefix + 'hotelSupport_recommendation')

        this.STATUS_PRE_ADD_FAILED = 5
        this.ASE_MAJ= 15
        this.ASE_HEB = 16
        this.HOTEL_SUPPORT = 19
        this.HOTEL_URG = 20

        this.init()
    }

    init() {
        document.querySelectorAll('select[multiple]').forEach(selectElt => {
            new SelectManager('#' + selectElt.id)
        })

        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt))
        })

        let dateElts = [this.orientationDateElt, this.startDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.checkIntervalBeetweenDates(
                    this.orientationDateElt,
                    this.startDateElt,
                    'La date ne peut pas être antérieure à la date de la demande.')
            })
        })

        const msgStartDate = 'La date ne peut pas être antérieure au début de l\'accompagnement.'

        dateElts = [this.startDateElt, this.evaluationDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.evaluationDateElt,
                    msgStartDate
                )
            })
        })

        dateElts = [this.startDateElt, this.agreementDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.agreementDateElt,
                    msgStartDate
                )
            })
        })

        dateElts = [this.startDateElt, this.endDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.endDateElt,
                    msgStartDate
                )
            })
        })

        this.checkFormBeforeSubmit()
    }

    /**
     * Vérifie la validité du formualire avant la soumission.
     */
    checkFormBeforeSubmit() {
        this.btnSubmitElts.forEach(btnElt => {
            btnElt.addEventListener('click', () => {
                if (this.endDateElt.value && this.HOTEL_URG === parseInt(this.deviceSelectElt.value)) {
                    this.checkField(this.evaluationDateElt)
                    this.checkField(this.emergencyActionRequestSelectElt)
                    this.checkField(this.emergencyActionDoneSelectElt)       
                }
                if (this.endDateElt.value && this.HOTEL_SUPPORT === parseInt(this.deviceSelectElt.value)) {
                    this.checkField(this.levelSupportSelectElt)
                    this.checkField(this.departmentAnchorSelectElt)
                    this.checkField(this.recommendationSelectElt)
                }
                if (this.STATUS_PRE_ADD_FAILED === parseInt(this.statusSelectElt.value)) {
                    this.checkField(this.reasonNoInclusionSelectElt)
                } else {
                    this.validField(this.reasonNoInclusionSelectElt)
                }
            })
        })
    }

    /**
     * Vérifie la valeur du champ date.
     * @param {HTMLElement} inputElt 
     */
    checkDate(inputElt) {
        const dateValidator = new DateValidator(inputElt, this)

        if (dateValidator.isValid() === false) {
            return false
        }
        this.validField(inputElt)
    }
}