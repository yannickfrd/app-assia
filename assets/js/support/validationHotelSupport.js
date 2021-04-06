import DisplayFields from '../utils/displayFields'
import ValidationForm from '../utils/validationForm'
import SelectType from '../utils/selectType'
import ValidationDate from '../utils/date/validationDate'
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
                    msgStartDate
                )
            })
        })

        dateElts = [this.startDateElt, this.agreementDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.validationForm.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.agreementDateElt,
                    msgStartDate
                )
            })
        })

        dateElts = [this.startDateElt, this.endDateElt]
        dateElts.forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.validationForm.checkIntervalBeetweenDates(
                    this.startDateElt,
                    this.endDateElt,
                    msgStartDate
                )
            })
        })
        this.checkFormBeforeSubmit()
    }

    /**
     * Masque ou affiche les champs conditionnels.
     */
    displayFields() {
        new DisplayFields(this.prefix, 'device')
        new DisplayFields(this.prefix, 'status')
        new DisplayFields('support_originRequest_', 'orientationDate')
        new DisplayFields('support_originRequest_', 'organization')
        new DisplayFields(this.prefix + 'hotelSupport_', 'evaluationDate')
        new DisplayFields(this.prefix + 'hotelSupport_', 'emergencyActionDone')
        new DisplayFields(this.prefix, 'startDate')
        new DisplayFields(this.prefix, 'endDate')
    }

    /**
     * Vérifie la validité du formualire avant la soumission.
     */
    checkFormBeforeSubmit() {
        this.btnSubmitElts.forEach(btnElt => {
            btnElt.addEventListener('click', e => {
                this.checkFields()
            })
        })
    }

    checkFields() {
        if (this.endDateElt.value && this.HOTEL_URG === this.selectType.getOption(this.deviceSelectElt)) {
            this.checkSelectEltIsNotEmpty(this.evaluationDateElt)
            this.checkSelectEltIsNotEmpty(this.emergencyActionRequestSelectElt)
            this.checkSelectEltIsNotEmpty(this.emergencyActionDoneSelectElt)       
        }
        if (this.endDateElt.value && this.HOTEL_SUPPORT === this.selectType.getOption(this.deviceSelectElt)) {
            this.checkSelectEltIsNotEmpty(this.levelSupportSelectElt)
            this.checkSelectEltIsNotEmpty(this.departmentAnchorSelectElt)
            this.checkSelectEltIsNotEmpty(this.recommendationSelectElt)
        }
        if (this.STATUS_PRE_ADD_FAILED === this.selectType.getOption(this.statusSelectElt)) {
            this.checkSelectEltIsNotEmpty(this.reasonNoInclusionSelectElt)
        } else {
            this.validationForm.validField(this.reasonNoInclusionSelectElt)
        }
    }
    
    /**
     * Check is a select element is not empty.
     * @param {HTMLSelectElement} field 
     * @param {String} msg 
     */
    checkSelectEltIsNotEmpty(field, msg = 'Saisie obligatoire') {
        if (!this.selectType.getOption(field)) {
            this.validationForm.invalidField(field, msg)
        } else {
            this.validationForm.validField(field)
        }  
    }

    /**
     * Vérifie la valeur du champ date.
     * @param {HTMLElement} inputElt 
     */
    checkDate(inputElt) {
        const validationDate = new ValidationDate(inputElt, this.validationForm)

        if (validationDate.isValid() === false) {
            return false
        }
        this.validationForm.validField(inputElt)
    }
}