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
            this.validationInput.invalid("lastname", this.lastnameLabelElt, this.lastnameInputElt, "Le nom est trop court (2 caractères min.).");
        } else if (this.lastnameInputElt.value.length >= 50) {
            this.validationInput.invalid("lastname", this.lastnameLabelElt, this.lastnameInputElt, "Le nom est trop long (50 caractères max.).");
        } else {
            this.validationInput.valid("lastname", this.lastnameLabelElt, this.lastnameInputElt);
        }
    }

    checkFirstname() {
        if (this.firstnameInputElt.value.length <= 1) {
            this.validationInput.invalid("firstname", this.firstnameLabelElt, this.firstnameInputElt, "Le prénom est trop court (2 caractères min.).");
        } else if (this.firstnameInputElt.value.length >= 50) {
            this.validationInput.invalid("firstname", this.firstnameLabelElt, this.firstnameInputElt, "Le prénom est trop long (50 caractères max.).");
        } else {
            this.validationInput.valid("firstname", this.firstnameLabelElt, this.firstnameInputElt);
        }
    }

    checkBirthdate() {
        let birthdate = new Date(this.birthdateInputElt.value);
        let now = new Date();
        let age = Math.round((now - birthdate) / (24 * 3600 * 1000 * 365.25));
        if (birthdate < now && age < 99) {
            this.validationInput.valid("birthdate", this.birthdateLabelElt, this.birthdateInputElt);
        } else {
            this.validationInput.invalid("birthdate", this.birthdateLabelElt, this.birthdateInputElt, "La date de naissance est incorrecte.");
        }
    }

    checkGender() {
        this.genderInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.genderValue = parseInt(option.value);
            }
        });
        if (this.genderValue >= 1 && this.genderValue <= 3) {
            this.validationInput.valid("gender", this.genderLabelElt, this.genderInputElt);
        } else {
            this.validationInput.invalid("gender", this.genderLabelElt, this.genderInputElt, "Le sexe doit être renseigné.");
        }
    }

    checkEmail() {
        let regex = this.emailInputElt.value.match("^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}");
        if (regex || this.emailInputElt.value === "") {
            this.valid("email", this.emailInputElt);
            this.validationInput.valid("email", this.emailLabelElt, this.emailInputElt);
        } else {
            this.validationInput.invalid("email", this.emailLabelElt, this.emailInputElt, "L'adresse email est incorrecte.");
        }
    }

    checkRole() {
        this.roleInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.roleValue = parseInt(option.value);
            }
        });
        if (this.roleValue >= 1 && this.roleValue <= 9) {
            this.validationInput.valid("role", this.roleLabelElt, this.roleInputElt);
        } else {
            this.validationInput.invalid("role", this.roleLabelElt, this.roleInputElt, "Le rôle  doit être renseigné.");
        }
    }

    checkTypo() {
        this.typoInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.typoValue = parseInt(option.value);
            }
        });
        if (this.typoValue >= 1 && this.typoValue <= 9) {
            this.validationInput.valid("typo", this.typoLabelElt, this.typoInputElt);
        } else {
            this.validationInput.invalid("typo", this.typoLabelElt, this.typoInputElt, "La typologie  doit être renseignée.");
        }
    }

    checkNbPeople() {
        if (this.nbPeopleInputElt.value >= 1 && this.nbPeopleInputElt.value <= 19) {
            this.valid("nbPeople", this.nbPeopleInputElt);
        } else {
            this.invalid("nbPeople", this.nbPeopleLabelElt, this.nbPeopleInputElt, "Le nombre de personnes est incorrect.");
        }
    }

    // Met le champ en valide 
    valid(field, input) {
        if (input.classList.contains("is-invalid")) {
            input.classList.replace("is-invalid", "is-valid");
            // document.querySelector(".invalid-" + field).remove();
            document.querySelector(".js-invalid-" + field).remove();
        } else {
            input.classList.add("is-valid");
        }
    }

    // Met le champ en invalide et met un message d'erreur
    invalid(field, label, input, msg) {
        if (document.querySelector("label.invalid-" + field)) {
            document.querySelector(".invalid-" + field).remove();
            if (document.querySelector("label>span.js-invalid-" + field)) {
                document.querySelector("span.js-invalid-" + field).remove();
            }
            if (!input.classList.contains("is-invalid")) {
                input.classList.add("is-invalid");
            }
            let invalidFeedbackElt = document.createElement("span");
            invalidFeedbackElt.className = "invalid-feedback d-block js-invalid js-invalid-" + field;
            invalidFeedbackElt.innerHTML = `
                <span class="form-error-icon badge badge-danger text-uppercase">Erreur</span> 
                <span class="form-error-message">${msg}</span>
                `
            label.appendChild(invalidFeedbackElt);
            this.validationInput.valid("nbPeople", this.nbPeopleLabelElt, this.nbPeopleInputElt);
        } else {
            this.validationInput.invalid("nbPeople", this.nbPeopleLabelElt, this.nbPeopleInputElt, "Le nombre de personnes est incorrect.");
        }
    }

    // Renvoie le nombre de champs invalides
    getNbErrors() {
        let nbErrors = document.querySelectorAll(".js-invalid").length + document.querySelectorAll("select.is-invalid").length;
        return nbErrors;
    }
}