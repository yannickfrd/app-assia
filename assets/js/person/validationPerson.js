import ValidationForm from '../utils/validationForm'
import ValidationDate from '../utils/validationDate'

/**
 * Validation des données de la fiche personne.
 */
export default class ValidationPerson {
    constructor(lastname, firstname, birthdate, gender, email, role, typo, nbPeople) {
        this.validationForm = new ValidationForm(document.getElementById('person'))

        this.lastnameInputElt = document.getElementById(lastname)
        this.lastnameLabelElt = document.querySelector('label[for=' + lastname + ']')

        this.firstnameInputElt = document.getElementById(firstname)
        this.firstnameLabelElt = document.querySelector('label[for=' + firstname + ']')

        this.birthdateInputElt = document.getElementById(birthdate)
        this.birthdateLabelElt = document.querySelector('label[for=' + birthdate + ']')

        this.genderInputElt = document.getElementById(gender)
        this.genderLabelElt = document.querySelector('label[for=' + gender + ']')
        this.genderValue = null

        this.emailInputElt = document.getElementById(email)
        this.emailLabelElt = document.querySelector('label[for=' + email + ']')

        this.roleInputElt = document.getElementById(role)
        this.roleLabelElt = document.querySelector('label[for=' + role + ']')
        this.roleValue = null

        this.typoInputElt = document.getElementById(typo)
        this.typoLabelElt = document.querySelector('label[for=' + typo + ']')
        this.typoValue = null

        this.nbPeopleInputElt = document.getElementById(nbPeople)
        this.nbPeopleLabelElt = document.querySelector('label[for=' + nbPeople + ']')

        this.init()
    }

    init() {
        this.lastnameInputElt.addEventListener('focusout', this.checkLastname.bind(this))
        this.firstnameInputElt.addEventListener('focusout', this.checkFirstname.bind(this))
        this.birthdateInputElt.addEventListener('focusout', this.checkBirthdate.bind(this))
        this.emailInputElt.addEventListener('focusout', this.checkEmail.bind(this))
        if (this.typoInputElt) {
            this.nbPeopleInputElt.addEventListener('change', this.checkNbPeople.bind(this))
        }
    }

    getNbErrors() {
        return this.validationForm.checkForm()
    }

    checkLastname() {
        if (this.lastnameInputElt.value.length <= 1) {
            this.validationForm.invalidField(this.lastnameInputElt, 'Le nom est trop court (2 caractères min.).')
        } else if (this.lastnameInputElt.value.length >= 50) {
            this.validationForm.invalidField(this.lastnameInputElt, 'Le nom est trop long (50 caractères max.).')
        } else {
            this.validationForm.validField(this.lastnameInputElt)
        }
    }

    checkFirstname() {
        if (this.firstnameInputElt.value.length <= 1) {
            this.validationForm.invalidField(this.firstnameInputElt, 'Le prénom est trop court (2 caractères min.).')
        } else if (this.firstnameInputElt.value.length >= 50) {
            this.validationForm.invalidField(this.firstnameInputElt, 'Le prénom est trop long (50 caractères max.).')
        } else {
            this.validationForm.validField(this.firstnameInputElt)
        }
    }

    checkBirthdate() {
        let validationDate = new ValidationDate(this.birthdateInputElt, this.validationForm)

        if (validationDate.isValid() === false || validationDate.isNotAfterToday() === false) {
            return false
        }
        this.validationForm.validField(this.birthdateInputElt)
    }

    checkEmail() {
        if (this.emailInputElt.value === '' || this.emailInputElt.value.match('^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}')) {
            return this.validationForm.validField(this.emailInputElt)
        } else {
            return this.validationForm.invalidField(this.emailInputElt, 'L\'adresse email est incorrecte.')
        }
    }


    checkNbPeople() {
        if (this.nbPeopleInputElt.value >= 1 && this.nbPeopleInputElt.value <= 19) {
            this.validationForm.validField(this.nbPeopleInputElt)
        } else {
            this.validationForm.invalidField(this.nbPeopleInputElt, 'Le nombre de personnes est incorrect.')
        }
    }
}