import FormValidator from '../utils/form/formValidator'
import DateValidator from '../utils/date/dateValidator'

/**
 * Validation des données de la fiche personne.
 */
export default class ValidationPerson {
    constructor(lastname, firstname, birthdate, gender, email, role, typo, nbPeople) {
        this.formValidator = new FormValidator(document.getElementById('person'))

        this.lastnameInputElt = document.getElementById(lastname)
        this.firstnameInputElt = document.getElementById(firstname)
        this.birthdateInputElt = document.getElementById(birthdate)
        this.genderInputElt = document.getElementById(gender)
        this.emailInputElt = document.getElementById(email)
        this.roleInputElt = document.getElementById(role)
        this.nbPeopleInputElt = document.getElementById(nbPeople)

        this.init()
    }

    init() {
        this.lastnameInputElt.addEventListener('focusout', this.checkLastname.bind(this))
        this.firstnameInputElt.addEventListener('focusout', this.checkFirstname.bind(this))
        this.birthdateInputElt.addEventListener('focusout', this.checkBirthdate.bind(this))
        if (this.emailInputElt) {
            this.emailInputElt.addEventListener('focusout', this.checkEmail.bind(this))
        }
        if (this.nbPeopleInputElt) {
            this.nbPeopleInputElt.addEventListener('change', this.checkNbPeople.bind(this))
        }
    }

    getNbErrors() {
        return this.formValidator.checkForm()
    }

    checkLastname() {
        if (this.lastnameInputElt.value === '') {
            return this.formValidator.invalidField(this.lastnameInputElt, 'Le nom peut pas être vide.')
        }
        if (this.lastnameInputElt.value.length >= 50) {
            return this.formValidator.invalidField(this.lastnameInputElt, 'Le nom est trop long (50 caractères max.).')
        }
        return this.formValidator.validField(this.lastnameInputElt)
    }

    checkFirstname() {
        if (this.firstnameInputElt.value === '')  {
            return this.formValidator.invalidField(this.firstnameInputElt, 'Le prénom ne peut pas être vide.')
        }
        if (this.firstnameInputElt.value.length >= 50) {
            return this.formValidator.invalidField(this.firstnameInputElt, 'Le prénom est trop long (50 caractères max.).')
        } 
        return this.formValidator.validField(this.firstnameInputElt)
    }

    checkBirthdate() {
        const dateValidator = new DateValidator(this.birthdateInputElt, this.formValidator)
        const role = parseInt(this.roleInputElt.value)
        
        if ((!isNaN(role) && role != 3 && (dateValidator.getIntervalWithNow() / 365) < 16)) {
            return this.formValidator.invalidField(this.birthdateInputElt, 'Date invalide.')
        }

        if (dateValidator.isValid() === false || dateValidator.isNotAfterToday() === false) {
            return false
        }
        return this.formValidator.validField(this.birthdateInputElt)

    }

    checkEmail() {
        if (this.emailInputElt.value === '' || this.emailInputElt.value.match('^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}')) {
            return this.formValidator.validField(this.emailInputElt)
        } 
        return this.formValidator.invalidField(this.emailInputElt, 'L\'adresse email est incorrecte.')
    }


    checkNbPeople() {
        if (this.nbPeopleInputElt.value >= 1 && this.nbPeopleInputElt.value <= 19) {
            return this.formValidator.validField(this.nbPeopleInputElt)
        } 
        return this.formValidator.invalidField(this.nbPeopleInputElt, 'Le nombre de personnes est incorrect.')
    }
}