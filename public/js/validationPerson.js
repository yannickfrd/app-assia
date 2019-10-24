// Validation des données de la fiche personne
class ValidationPerson {
    constructor(lastname, firstname, birthdate, gender, email, role, typo, nbPeople) {
        this.lastname = lastname;
        this.lastnameInputElt = document.getElementById(this.lastname);
        this.lastnameLabelElt = document.querySelector("label[for=" + this.lastname + "]");

        this.firstname = firstname;
        this.firstnameInputElt = document.getElementById(this.firstname);
        this.firstnameLabelElt = document.querySelector("label[for=" + this.firstname + "]");

        this.birthdate = birthdate;
        this.birthdateInputElt = document.getElementById(this.birthdate);
        this.birthdateLabelElt = document.querySelector("label[for=" + this.birthdate + "]");

        this.gender = gender;
        this.genderInputElt = document.getElementById(this.gender);
        this.genderLabelElt = document.querySelector("label[for=" + this.gender + "]");
        this.genderValue = null;

        this.email = email;
        this.emailInputElt = document.getElementById(this.email);
        this.emailLabelElt = document.querySelector("label[for=" + this.email + "]");

        this.role = role;
        this.roleInputElt = document.getElementById(this.role);
        this.roleLabelElt = document.querySelector("label[for=" + this.role + "]");
        this.roleValue = null;

        this.typo = typo;
        this.typoInputElt = document.getElementById(this.typo);
        this.typoLabelElt = document.querySelector("label[for=" + this.typo + "]");
        this.typoValue = null;

        this.nbPeople = nbPeople;
        this.nbPeopleInputElt = document.getElementById(this.nbPeople);
        this.nbPeopleLabelElt = document.querySelector("label[for=" + this.nbPeople + "]");

        this.init();
    }

    init() {
        this.lastnameInputElt.addEventListener("focusout", this.checkLastname.bind(this));
        this.firstnameInputElt.addEventListener("focusout", this.checkFirstname.bind(this));
        this.birthdateInputElt.addEventListener("focusout", this.checkBirthdate.bind(this));
        this.genderInputElt.addEventListener("input", this.checkGender.bind(this));
        this.emailInputElt.addEventListener("focusout", this.checkEmail.bind(this));
        if (this.role) {
            this.roleInputElt.addEventListener("input", this.checkRole.bind(this));
        }
        if (this.typo) {
            this.typoInputElt.addEventListener("input", this.checkTypo.bind(this));
            this.nbPeopleInputElt.addEventListener("input", this.checkNbPeople.bind(this));
        }
    }

    checkLastname() {
        if (this.lastnameInputElt.value.length <= 2) {
            this.invalid("lastname", this.lastnameLabelElt, this.lastnameInputElt, "Le nom est trop court (2 caractères min.).");
        } else if (this.lastnameInputElt.value.length >= 50) {
            this.invalid("lastname", this.lastnameLabelElt, this.lastnameInputElt, "Le nom est trop long (50 caractères max.).");
        } else {
            this.valid("lastname", this.lastnameInputElt);
        }
    }

    checkFirstname() {
        if (this.firstnameInputElt.value.length <= 2) {
            this.invalid("firstname", this.firstnameLabelElt, this.firstnameInputElt, "Le prénom est trop court (2 caractères min.).");
        } else if (this.firstnameInputElt.value.length >= 50) {
            this.invalid("firstname", this.firstnameLabelElt, this.firstnameInputElt, "Le prénom est trop long (50 caractères max.).");
        } else {
            this.valid("firstname", this.firstnameInputElt);
        }
    }

    checkBirthdate() {
        let birthdate = new Date(this.birthdateInputElt.value);
        let now = new Date();
        let age = Math.round((now - birthdate) / (24 * 3600 * 1000 * 365.25));
        if (birthdate < now && age < 99) {
            this.valid("birthdate", this.birthdateInputElt);
        } else {
            this.invalid("birthdate", this.birthdateLabelElt, this.birthdateInputElt, "La date de naissance est incorrecte.");
        }
    }

    checkGender() {
        this.genderInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.genderValue = parseInt(option.value);
            }
        });
        if (this.genderValue >= 1 && this.genderValue <= 3) {
            this.valid("gender", this.genderInputElt);
        } else {
            this.invalid("gender", this.genderLabelElt, this.genderInputElt, "Le sexe doit être renseigné.");
        }
    }

    checkEmail() {
        let regex = this.emailInputElt.value.match("^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}");
        if (regex || this.emailInputElt.value === "") {
            this.valid("email", this.emailInputElt);
        } else {
            this.invalid("email", this.emailLabelElt, this.emailInputElt, "L'adresse email est incorrecte.");
        }
    }

    checkRole() {
        this.roleInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.roleValue = parseInt(option.value);
            }
        });
        if (this.roleValue >= 1 && this.roleValue <= 9) {
            this.valid("role", this.roleInputElt);
        } else {
            this.invalid("role", this.roleLabelElt, this.roleInputElt, "Le rôle  doit être renseigné.");
        }
    }

    checkTypo() {
        this.typoInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.typoValue = parseInt(option.value);
            }
        });
        if (this.typoValue >= 1 && this.typoValue <= 9) {
            this.valid("typo", this.typoInputElt);
        } else {
            this.invalid("typo", this.typoLabelElt, this.typoInputElt, "La typologie  doit être renseignée.");
        }
    }

    checkNbPeople() {
        if (this.nbPeopleInputElt.value >= 1 && this.nbPeopleInputElt.value <= 9) {
            this.valid("nbPeople", this.nbPeopleInputElt);
        } else {
            this.invalid("nbPeople", this.nbPeopleLabelElt, this.nbPeopleInputElt, "Le nombre de personnes est incorrect.");
        }
    }

    // Met le champ en valide 
    valid(field, input) {
        if (input.classList.contains("is-invalid")) {
            input.classList.replace("is-invalid", "is-valid");
            document.querySelector(".js-invalid-" + field).remove();
        } else {
            input.classList.add("is-valid");
        }
    }

    // Met le champ en invalide et met un message d'erreur
    invalid(field, label, input, msg) {
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
    }

    // Renvoie le nombre de champs invalides
    getNbErrors() {
        let nbErrors = document.querySelectorAll(".js-invalid").length;
        return nbErrors;
    }
}