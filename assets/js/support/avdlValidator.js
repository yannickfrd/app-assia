import FormValidator from '../utils/form/formValidator'
import FieldDisplayer from '../utils/form/fieldDisplayer'
import DateValidator from '../utils/date/dateValidator'

/**
 * Validation des données d'un suivi hôtel.
 */
export default class AvdlValidatorSupport extends FormValidator {

    constructor() {
        super()

        this.prefix = 'support_avdl_'

        this.btnSubmitElts = document.querySelectorAll('button[type="submit"]')
        this.dateInputElts = document.querySelectorAll('input[type="date"]')

        this.deviceSelectElt = document.getElementById('support_device')
        this.orientationDateElt = document.getElementById('support_originRequest_orientationDate')
        this.diagStartDateElt = document.getElementById(this.prefix + 'diagStartDate')
        this.diagEndDateElt = document.getElementById(this.prefix + 'diagEndDate')
        this.supportStartDateElt = document.getElementById(this.prefix + 'supportStartDate')
        this.supportEndDateElt = document.getElementById(this.prefix + 'supportEndDate')
        this.supportTypeElt = document.getElementById(this.prefix + 'supportType')

        this.AVDL_DALO = 10
        this.AVDL_NO_DALO = 4
        
        this.init()
    }

    init() {
        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt))
        })

        this.displayFields()

        this.diagStartDateElt.addEventListener('focusout', () => {
            this.checkIntervalBeetweenDates(
                this.orientationDateElt,
                this.diagStartDateElt,
                'La date ne peut pas être antérieure à la date de mandatement.')
        })

        this.diagEndDateElt.addEventListener('focusout', () => {
            this.checkIntervalBeetweenDates(
                this.diagStartDateElt,
                this.diagEndDateElt,
                'La date de fin ne peut pas être antérieure au début du diagnostic.')
        })

        this.supportStartDateElt.addEventListener('focusout', () => {
            this.checkIntervalBeetweenDates(
                this.diagEndDateElt,
                this.supportStartDateElt,
                'La date ne peut pas être antérieure à la fin du diagnostic.')
        })

        this.supportEndDateElt.addEventListener('focusout', () => {
            this.checkIntervalBeetweenDates(
                this.supportStartDateElt,
                this.supportEndDateElt,
                'La date ne peut pas être antérieure au début de l\'accompagnement.')
        })
        
        this.checkFormBeforeSubmit()
    }

    /**
     * Masque ou affiche les champs conditionnels
     */
    displayFields() {
        new FieldDisplayer('support_', 'device')
        new FieldDisplayer('support_originRequest_', 'orientationDate')
        new FieldDisplayer('support_originRequest_', 'organization')
        new FieldDisplayer(this.prefix, 'diagStartDate')
        new FieldDisplayer(this.prefix, 'supportStartDate')
        new FieldDisplayer(this.prefix, 'supportEndDate')
        new FieldDisplayer(this.prefix, 'propoHousingDate')
        new FieldDisplayer(this.prefix, 'propoResult', [1])
    }

    /**
     * Vérifie la validité du formualire avant la soumission.
     */
    checkFormBeforeSubmit() {
        this.btnSubmitElts.forEach(btnElt => {
            btnElt.addEventListener('click', () => {
                const device = this.selectType.getOption(this.deviceSelectElt)
                if (this.AVDL_DALO === device && this.supportStartDateElt.value) {
                    this.checkField(this.diagStartDateElt)    
                    this.checkField(this.diagEndDateElt)    
                    this.checkField(document.getElementById(this.prefix + 'diagType'))    
                    this.checkField(document.getElementById(this.prefix + 'recommendationSupport'))    
                }
                if (this.supportEndDateElt.value) {
                    this.checkField(this.supportStartDateElt)    
                } else {
                    this.validField(this.supportTypeElt)    
                }
                if (this.AVDL_DALO === device && this.supportEndDateElt.value) {
                    this.checkField(this.supportTypeElt)    
                } else {
                    this.validField(this.supportTypeElt)    
                }
            })
        })
    }

    /**
     * Vérifie la valeur du champ date
     * @param {HTMLElement} inputElt 
     */
    checkDate(inputElt) {
        const dateValidator = new DateValidator(inputElt, this)

        if (dateValidator.isValid() === false || dateValidator.isNotAfterOneYear() === false) {
            return false
        }
        this.validField(inputElt)
    }
}