import DisplayFields from '../utils/displayFields'
import ValidationForm from '../utils/validationForm'
import SelectType from '../utils/selectType'
import ValidationDate from '../utils/validationDate'
import Loader from '../utils/loader'

// Contrôle saisie AVDL :
// - Date de fin de diag sans date de début
// - Date de fin de diag sans type de diag
// - Date de fin de diag sans préco d'acc.
// - Date de début d'acc. sans niv d'acc.
// - Date de fin d'acc. sans date de début
// - Date de fin d'acc. sans PAL
// - Date de fin d'acc. sans motif de fin d'acc.
// - Date de fin d'acc. sans situation résidentielle à l'issue
// - Date de propo logement sans modalité d'accès au logement
// - Date de propo logement sans origine de la propo (?)
// - Résultat de la propo sans date de propo ou sans modalité d'accès
// ...

/**
 * Validation des données de la fiche personne
 */
export default class ValidationAvdlSupport {

    constructor() {
        this.validationForm = new ValidationForm()
        this.selectType = new SelectType()
        this.loader = new Loader()

        this.prefix = 'support_avdl_'

        this.serviceSelectElt = document.getElementById('support_service')
        this.btnSubmitElts = document.querySelectorAll('button[type="submit"]')
        this.dateInputElts = document.querySelectorAll('input[type="date"]')

        this.init()
    }

    init() {
        this.service = this.selectType.getOption(this.serviceSelectElt)
        this.serviceSelectElt.addEventListener('change', this.changeService.bind(this))

        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt))
        })
        this.displayFields()

        this.btnSubmitElts.forEach(btnElt => {
            btnElt.addEventListener('click', e => {
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
        new DisplayFields('support_originRequest_', 'organization')
        new DisplayFields(this.prefix, 'diagStartDate')
        new DisplayFields(this.prefix, 'supportStartDate')
        new DisplayFields(this.prefix, 'supportEndDate')
        new DisplayFields(this.prefix, 'accessHousingModality')
    }

    /**
     * Si champ de la valeur du SELECT service
     */
    changeService() {
        if (window.confirm('Le changement de service va recharger la page actuelle. Voulez-vous confirmer ?')) {
            this.loader.on()
            document.getElementById('send').click()
        } else {
            this.selectType.setOption(this.serviceSelectElt, this.service)
        }
    }

    /**
     * Vérifie la valeur du champ date
     * @param {HTMLElement} inputElt 
     */
    checkDate(inputElt) {
        let validationDate = new ValidationDate(inputElt, this.validationForm)

        if (validationDate.isValid() === false || validationDate.isNotAfterToday() === false) {
            return false
        }
        this.validationForm.validField(inputElt)
    }
}