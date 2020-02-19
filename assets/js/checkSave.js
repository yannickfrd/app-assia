// Vérifie que l'utilisateur à sauvegarder ces modifications avant de changer de page

export default class CheckSave {

    constructor() {
        this.containerElt = document.querySelector("main");
        this.aElts = document.querySelectorAll("a");
        this.inputElts = this.containerElt.querySelectorAll("input");
        this.selectElts = this.containerElt.querySelectorAll("select");
        this.textareaElts = this.containerElt.querySelectorAll("textarea");
        this.inputSubmitElts = this.containerElt.querySelectorAll("input[type=submit]");
        this.btnSubmitElts = this.containerElt.querySelectorAll("button[type=submit]");
        this.count = 0;
        this.init();
    }

    init() {
        // console.log(this.aElts.length);
        // console.log(this.inputSubmitElts);
        // console.log(this.btnSubmitElts);
        if (this.inputElts.length) {
            this.inputElts.forEach(inputElt => {
                inputElt.addEventListener("keydown", this.countUp.bind(this))
            });
        }
        if (this.selectElts.length) {
            this.selectElts.forEach(selectElt => {
                selectElt.addEventListener("click", this.countUp.bind(this))
            });
        }
        if (this.textareaElts.length) {
            this.textareaElts.forEach(textareaElt => {
                textareaElt.addEventListener("keydown", this.countUp.bind(this))
            });
        }
        if (this.inputSubmitElts.length) {
            this.inputSubmitElts.forEach(inputSubmitElt => {
                inputSubmitElt.addEventListener("click", this.clearCount.bind(this))
            });
        }
        if (this.btnSubmitElts.length) {
            this.btnSubmitElts.forEach(btnSubmitElt => {
                btnSubmitElt.addEventListener("click", this.clearCount.bind(this))
            });
        }
        this.aElts.forEach(aElt => {
            aElt.addEventListener("click", function (e) {
                this.checkCount(e);
            }.bind(this))
        });
    }

    countUp() {
        this.count++;
    }

    clearCount() {
        this.count = 0;
    }

    checkCount(e) {
        if (this.count > 1) {
            if (!window.confirm("Attention, vous n'avez pas sauvegarder vos modifications.")) {
                e.preventDefault();
            }
        }
    }
}