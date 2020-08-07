import ValidationInput from "../utils/validationInput";

// Validation des données de la fiche personne
export default class ValidationPerson {
    constructor(lastname, firstname, birthdate, gender, email, role, typo, nbPeople) {
        this.lastnameInputElt = document.getElementById(lastname);
        this.lastnameLabelElt = document.querySelector("label[for=" + lastname + "]");

        this.firstnameInputElt = document.getElementById(firstname);
        this.firstnameLabelElt = document.querySelector("label[for=" + firstname + "]");

        this.birthdateInputElt = document.getElementById(birthdate);
        this.birthdateLabelElt = document.querySelector("label[for=" + birthdate + "]");

        this.genderInputElt = document.getElementById(gender);
        this.genderLabelElt = document.querySelector("label[for=" + gender + "]");
        this.genderValue = null;

        this.emailInputElt = document.getElementById(email);
        this.emailLabelElt = document.querySelector("label[for=" + email + "]");

        this.roleInputElt = document.getElementById(role);
        this.roleLabelElt = document.querySelector("label[for=" + role + "]");
        this.roleValue = null;

        this.typoInputElt = document.getElementById(typo);
        this.typoLabelElt = document.querySelector("label[for=" + typo + "]");
        this.typoValue = null;

        this.nbPeopleInputElt = document.getElementById(nbPeople);
        this.nbPeopleLabelElt = document.querySelector("label[for=" + nbPeople + "]");

        this.validationInput = new ValidationInput();
        this.init();
    }

    init() {
        this.lastnameInputElt.addEventListener("focusout", this.checkLastname.bind(this));
        this.firstnameInputElt.addEventListener("focusout", this.checkFirstname.bind(this));
        this.birthdateInputElt.addEventListener("focusout", this.checkBirthdate.bind(this));
        this.genderInputElt.addEventListener("change", this.checkGender.bind(this));
        this.emailInputElt.addEventListener("focusout", this.checkEmail.bind(this));
        if (this.roleInputElt) {
            this.roleInputElt.addEventListener("change", this.checkRole.bind(this));
        }
        if (this.typoInputElt) {
            this.typoInputElt.addEventListener("change", this.checkTypo.bind(this));
            this.nbPeopleInputElt.addEventListener("change", this.checkNbPeople.bind(this));
        }
    }

    checkLastname() {
        if (this.lastnameInputElt.value.length <= 1) {
            this.validationInput.invalid(this.lastnameInputElt, "Le nom est trop court (2 caractères min.).");
        } else if (this.lastnameInputElt.value.length >= 50) {
            this.validationInput.invalid(this.lastnameInputElt, "Le nom est trop long (50 caractères max.).");
        } else {
            this.validationInput.valid(this.lastnameInputElt);
        }
    }

    checkFirstname() {
        if (this.firstnameInputElt.value.length <= 1) {
            this.validationInput.invalid(this.firstnameInputElt, "Le prénom est trop court (2 caractères min.).");
        } else if (this.firstnameInputElt.value.length >= 50) {
            this.validationInput.invalid(this.firstnameInputElt, "Le prénom est trop long (50 caractères max.).");
        } else {
            this.validationInput.valid(this.firstnameInputElt);
        }
    }

    checkBirthdate() {
        let birthdate = new Date(this.birthdateInputElt.value);
        let now = new Date();
        let age = Math.round((now - birthdate) / (24 * 3600 * 1000 * 365.25));
        if (birthdate < now && age < 99) {
            this.validationInput.valid(this.birthdateInputElt);
        } else {
            this.validationInput.invalid(this.birthdateInputElt, "La date de naissance est incorrecte.");
        }
    }

    checkGender() {
        this.genderInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.genderValue = parseInt(option.value);
            }
        });
        if (this.genderValue >= 1 && this.genderValue <= 3) {
            this.validationInput.valid(this.genderInputElt);
        } else {
            this.validationInput.invalid(this.genderInputElt, "Le sexe doit être renseigné.");
        }
    }

    checkEmail() {
        if (this.emailInputElt.value === "" || this.emailInputElt.value.match("^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}")) {
            return this.validationInput.valid(this.emailInputElt);
        } else {
            return this.validationInput.invalid(this.emailInputElt, "L'adresse email est incorrecte.");
        }
    }

    checkRole() {
        this.roleInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.roleValue = parseInt(option.value);
            }
        });
        if (this.roleValue >= 1 && this.roleValue <= 9) {
            this.validationInput.valid(this.roleInputElt);
        } else {
            this.validationInput.invalid(this.roleInputElt, "Le rôle  doit être renseigné.");
        }
    }

    checkTypo() {
        this.typoInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.typoValue = parseInt(option.value);
            }
        });
        if (this.typoValue >= 1 && this.typoValue <= 9) {
            this.validationInput.valid(this.typoInputElt);
        } else {
            this.validationInput.invalid(this.typoInputElt, "La typologie  doit être renseignée.");
        }
    }

    checkNbPeople() {
        if (this.nbPeopleInputElt.value >= 1 && this.nbPeopleInputElt.value <= 19) {
            this.validationInput.valid(this.nbPeopleInputElt);
        } else {
            this.validationInput.invalid(this.nbPeopleInputElt, "Le nombre de personnes est incorrect.");
        }
    }
}