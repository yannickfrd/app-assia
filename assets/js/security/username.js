import AjaxRequest from "../utils/ajaxRequest";
import ValidationInput from "../utils/validationInput";

// Création automatique du login de l'utilisateur
export default class Username {
    constructor() {
        this.firstnameInputElt = document.getElementById("registration_firstname");
        this.lastnameInputElt = document.getElementById("registration_lastname");

        this.usernameLabelElt = document.querySelector("label[for=registration_username]");
        this.usernameInputElt = document.getElementById("registration_username");

        this.emailLabelElt = document.querySelector("label[for=registration_email]");
        this.emailInputElt = document.getElementById("registration_email");

        this.regexPassword = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\\W).{6,20}";
        this.passwordLabelElt = document.querySelector("label[for=registration_password]");
        this.passwordInputElt = document.getElementById("registration_password");
        this.confirmPasswordLabelElt = document.querySelector("label[for=registration_confirmPassword]");
        this.confirmPasswordInputElt = document.getElementById("registration_confirmPassword");

        this.ajaxRequest = new AjaxRequest();
        this.validationInput = new ValidationInput();
        this.init();
    }

    init() {
        this.firstnameInputElt.addEventListener("focusout", this.updateUsername.bind(this));
        this.lastnameInputElt.addEventListener("focusout", this.updateUsername.bind(this));
        this.usernameInputElt.addEventListener("keyup", this.timer.bind(this));
        this.emailInputElt.addEventListener("focusout", this.checkEmail.bind(this));
        this.passwordInputElt.addEventListener("keyup", this.checkPassword.bind(this));
        this.passwordInputElt.addEventListener("focusout", this.checkoutPassword.bind(this));
        this.confirmPasswordInputElt.addEventListener("focusout", this.checkConfirmPassword.bind(this));
    }


    // Timer avant de lancer la requête Ajax
    timer() {
        clearInterval(this.countdownID);
        this.countdownID = setTimeout(this.checkUsername.bind(this), 1000);
    }

    checkUsername() {
        if (this.usernameInputElt.value.length > 6) {
            this.sendAjaxRequest();
        } else {
            this.validationInput.invalid("username", this.usernameLabelElt, this.usernameInputElt, "Le login est invalide.");
        }
    }

    updateUsername() {
        if (this.firstnameInputElt.value.length > 2 && this.lastnameInputElt.value.length > 2) {
            let autoUsername = this.firstnameInputElt.value.toLowerCase().charAt(0) + "." + this.lastnameInputElt.value.toLowerCase();
            if (this.usernameInputElt.value === "") {
                this.usernameInputElt.value = autoUsername;
                this.sendAjaxRequest();
            }
        }
    }

    sendAjaxRequest() {
        let url = "/user/check_username?value=" + this.usernameInputElt.value;
        this.ajaxRequest.init("GET", url, this.response.bind(this), true);
    }

    response(data) {
        let dataJSON = JSON.parse(data);
        console.log(dataJSON);
        if (dataJSON.response === true) {
            this.validationInput.invalid("username", this.usernameLabelElt, this.usernameInputElt, "Ce login est déjà pris !");
        } else {
            this.validationInput.valid("username", this.usernameLabelElt, this.usernameInputElt);
        }
    }

    checkEmail() {
        let regex = this.emailInputElt.value.match("^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}");
        if (regex || this.emailInputElt.value === "") {
            this.validationInput.valid("email", this.usernameLabelElt, this.emailInputElt);
        } else {
            this.validationInput.invalid("email", this.emailLabelElt, this.emailInputElt, "L'adresse email est incorrecte.");
        }
    }

    checkPassword() {
        if (this.passwordInputElt.value.match(this.regexPassword)) {
            this.validationInput.valid("password", this.passwordLabelElt, this.passwordInputElt);
        }
    }

    checkoutPassword() {
        if (!this.passwordInputElt.value.match(this.regexPassword)) {
            this.validationInput.invalid("password", this.passwordLabelElt, this.passwordInputElt, "Le mot de passe est invalide.");
        }
    }

    checkConfirmPassword() {
        if (this.confirmPasswordInputElt.value === this.passwordInputElt.value) {
            this.validationInput.valid("email", this.confirmPasswordLabelElt, this.confirmPasswordInputElt);
        } else {
            this.validationInput.invalid("email", this.confirmPasswordLabelElt, this.confirmPasswordInputElt, "Le mot de passe et la confirmation sont différents.");
        }
    }
}