import AjaxRequest from "../utils/ajaxRequest";
import ValidationInput from "../utils/validationInput";

// Création automatique du login de l'utilisateur
export default class Username {
    constructor(formName) {
        this.firstnameInputElt = document.getElementById(formName + "_firstname");
        this.lastnameInputElt = document.getElementById(formName + "_lastname");

        this.usernameLabelElt = document.querySelector("label[for=" + formName + "_username]");
        this.usernameInputElt = document.getElementById(formName + "_username");

        this.emailLabelElt = document.querySelector("label[for=" + formName + "_email]");
        this.emailInputElt = document.getElementById(formName + "_email");

        this.regexPassword = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\\W).{6,20}";
        this.passwordLabelElt = document.querySelector("label[for=" + formName + "_password]");
        this.passwordInputElt = document.getElementById(formName + "_password");
        this.confirmPasswordLabelElt = document.querySelector("label[for=" + formName + "_confirmPassword]");
        this.confirmPasswordInputElt = document.getElementById(formName + "_confirmPassword");

        this.ajaxRequest = new AjaxRequest();
        this.validationInput = new ValidationInput();
        this.init();
    }

    init() {
        if (this.lastnameInputElt) {
            this.firstnameInputElt.addEventListener("change", this.updateUsername.bind(this));
            this.lastnameInputElt.addEventListener("change", this.updateUsername.bind(this));
            this.usernameInputElt.addEventListener("keyup", this.timer.bind(this));
        }
        this.emailInputElt.addEventListener("change", this.checkEmail.bind(this));
        if (this.passwordInputElt) {
            this.passwordInputElt.addEventListener("keyup", this.checkPassword.bind(this));
            this.passwordInputElt.addEventListener("change", this.checkoutPassword.bind(this));
            this.confirmPasswordInputElt.addEventListener("change", this.checkConfirmPassword.bind(this));
        }
    }


    // Timer avant de lancer la requête Ajax
    timer() {
        clearInterval(this.countdownID);
        this.countdownID = setTimeout(this.checkUsername.bind(this), 1000);
    }

    checkUsername() {
        if (this.usernameInputElt.value.length > 6) {
            return this.sendAjaxRequest();
        }
        return this.validationInput.invalid(this.usernameInputElt, "Le login est invalide.");
    }

    updateUsername() {
        if (this.firstnameInputElt.value.length > 2 && this.lastnameInputElt.value.length > 2) {
            this.usernameInputElt.value = this.firstnameInputElt.value.toLowerCase().charAt(0) + "." + this.lastnameInputElt.value.toLowerCase();;
            this.sendAjaxRequest();
        }
    }

    sendAjaxRequest() {
        this.ajaxRequest.init("GET", "/user/username_exists?value=" + this.usernameInputElt.value, this.response.bind(this), true);
    }

    response(data) {
        if (JSON.parse(data).response === true) {
            return this.validationInput.invalid(this.usernameInputElt, "Ce login est déjà pris !");
        }
        return this.validationInput.valid(this.usernameInputElt);
    }

    checkEmail() {
        if (this.emailInputElt.value.match("^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}") || this.emailInputElt.value === "") {
            return this.validationInput.valid(this.emailInputElt);
        }
        return this.validationInput.invalid(this.emailInputElt, "L'adresse email est incorrecte.");
    }

    checkPassword() {
        if (this.passwordInputElt.value.match(this.regexPassword)) {
            return this.validationInput.valid(this.passwordInputElt);
        }
    }

    checkoutPassword() {
        if (!this.passwordInputElt.value.match(this.regexPassword)) {
            this.validationInput.invalid(this.passwordInputElt, "Le mot de passe est invalide.");
        }
        this.checkConfirmPassword();
    }

    checkConfirmPassword() {
        if (this.confirmPasswordInputElt.value === this.passwordInputElt.value) {
            return this.validationInput.valid(this.confirmPasswordInputElt);
        }
        if (this.confirmPasswordInputElt.value) {
            return this.validationInput.invalid(this.confirmPasswordInputElt, "La confirmation est différente du mot de passe.");
        }
    }
}