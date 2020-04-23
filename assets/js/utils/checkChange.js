// Vérifie que l'utilisateur à sauvegarder ses modifications avant de changer de page
export default class CheckChange {

    constructor(formName) {
        this.formElt = document.querySelector("form[name=" + formName + "]");
        this.aElts = document.querySelectorAll("a[href]");
        this.inputElts = this.formElt.querySelectorAll("input");
        this.selectElts = this.formElt.querySelectorAll("select");
        this.textareaElts = this.formElt.querySelectorAll("textarea");
        this.btnSubmitElts = this.formElt.querySelectorAll("button[type=submit]");
        this.change = false;
        this.init();
    }

    init() {
        this.inputElts.forEach(inputElt => {
            inputElt.addEventListener("change", this.onChange.bind(this))
        });
        this.selectElts.forEach(selectElt => {
            selectElt.addEventListener("change", this.onChange.bind(this))
        });
        this.textareaElts.forEach(textareaElt => {
            textareaElt.addEventListener("change", this.onChange.bind(this))
        });
        this.btnSubmitElts.forEach(btnSubmitElt => {
            btnSubmitElt.addEventListener("click", this.clearChange.bind(this))
        });
        this.aElts.forEach(aElt => {
            aElt.addEventListener("click", e => {
                this.checkChange(e);
            })
        });
    }

    onChange() {
        this.change = true;
    }

    clearChange() {
        this.change = false;
    }

    checkChange(e) {
        if (this.change) {
            if (!window.confirm("Attention, vous n'avez pas sauvegarder vos modifications. Continuer sans sauvegarder ?")) {
                e.preventDefault();
            }
        }
    }
}