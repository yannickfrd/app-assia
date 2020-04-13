// Class pour démasquer le mot de passe
export default class SeePassword {
    constructor() {
        this.passwordGroupElts = document.querySelectorAll(".js-password-group");
        this.init();
    }

    init() {
        this.passwordGroupElts.forEach(passwordGroupElt => {
            let passwordElt = passwordGroupElt.querySelector(".js-password");
            let showPasswordElt = passwordGroupElt.querySelector(".js-show-password");
            showPasswordElt.addEventListener("mousedown", this.see.bind(this, passwordElt)); // Affiche du mot de passe au clic sur l'oeil
            document.addEventListener("mouseup", this.hide.bind(this, passwordElt)); // Masque le mot de passe au relachement de la souris
            showPasswordElt.addEventListener("touchstart", this.see.bind(this, passwordElt)); // Affiche du mot de passe au touché tactile sur l'oeil
            document.addEventListener("touchend", this.hide.bind(this, passwordElt)); // Masque le mot de passe au relachement du touché tactile
        });
    }
    // Affiche du mot de passe
    see(passwordElt) {
        passwordElt.type = "text";
    }
    // Masque le mot de passe
    hide(passwordElt) {
        passwordElt.type = "password";
    }
}