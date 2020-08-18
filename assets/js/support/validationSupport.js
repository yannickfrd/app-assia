import MessageFlash from '../utils/messageFlash'
import DisplayFields from '../utils/displayFields'
import ValidationForm from '../utils/validationForm'
import SelectType from '../utils/selectType'
import ValidationDate from '../utils/validationDate'
import Loader from '../utils/loader'

// Validation des données de la fiche personne
export default class ValidationSupport {

    constructor() {
        this.validationForm = new ValidationForm()
        this.selectType = new SelectType()
        this.loader = new Loader()


        this.prefix = 'support_'
        this.serviceSelectElt = document.getElementById(this.prefix + 'service')
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
        this.service = this.selectType.getOption(this.serviceSelectElt)
        this.serviceSelectElt.addEventListener('change', this.changeService.bind(this))

        this.dateInputElts.forEach(dateInputElt => {
            dateInputElt.addEventListener('focusout', this.checkDate.bind(this, dateInputElt))
        })

        if (this.statusSelectElt) {
            this.startDateInputElt.addEventListener('focusout', this.checkStartDate.bind(this))
            this.endDateInputElt.addEventListener('focusout', this.checkEndDate.bind(this))
            this.endStatusInputElt.addEventListener('change', this.checkEndStatus.bind(this))
        }

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
        this.displayFields()
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
     * Si changement de service.
     */
    changeService() {
        if (window.confirm('Le changement de service va recharger la page actuelle. Confirmer ?')) {
            this.loader.on()
            document.getElementById('send').click()
        } else {
            this.selectType.setOption(this.serviceSelectElt, this.service)
        }
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
}