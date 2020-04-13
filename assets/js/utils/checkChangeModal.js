// Vérifie que l'utilisateur à sauvegarder ses modifications avant de changer de page

export default class CheckChange {

    constructor(formName) {
        this.formElt = document.querySelector("form[name=" + formName + "]");
        this.aElts = document.querySelectorAll("a[href]");
        this.inputElts = this.formElt.querySelectorAll("input");
        this.selectElts = this.formElt.querySelectorAll("select");
        this.modalNoteElt = document.getElementById("modal-note");
        this.modalOutElt;
        this.btnCancelElt = document.getElementById("js-btn-cancel");
        this.btnSaveElt = document.getElementById("js-btn-save");
        this.change = false;
        this.init();
    }

    init() {

        $("#modal-note").modal({
            backdrop: "static",
            show: false
        });

        this.inputElts.forEach(inputElt => {
            inputElt.addEventListener("change", this.onChange.bind(this))
        });
        this.selectElts.forEach(selectElt => {
            selectElt.addEventListener("change", this.onChange.bind(this))
        });
        if (this.textareaElts) {
            this.textareaElts.forEach(textareaElt => {
                textareaElt.addEventListener("change", this.onChange.bind(this))
            });
        }

        document.getElementById("editor").addEventListener("input", this.onChange.bind(this));

        // this.modalNoteElt.addEventListener("click", this.checkChange.bind(this));

        this.btnSaveElt.addEventListener("click", this.clearChange.bind(this));


        this.aElts.forEach(aElt => {
            aElt.addEventListener("click", e => {
                this.checkChange(e);
            })
        });
    }




    onChange() {
        this.change = true;
        console.log(this.change);
    }

    clearChange() {
        this.change = false;
        console.log(this.change);
    }

    test() {
        this.modalNoteElt.querySelector(".modal-content").addEventListener("click", e => {
            this.change = false;
            return false;
        });

    }


    checkChange(e) {
        if (this.change) {
            if (!window.confirm("Attention, vous n'avez pas sauvegarder vos modifications. Continuer sans sauvegarder ?")) {
                e.preventDefault();
            }
        }
    }
}