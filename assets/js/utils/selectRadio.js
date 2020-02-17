// Sélection d'un bouton Radio
export default class SelectRadioJS {
    constructor(containerElt) {
        this.containerElt = document.getElementById(containerElt);
        this.checkboxElts = this.containerElt.querySelectorAll(".checkbox.form-check-input");
        this.init();
    }

    init() {
        this.checkboxElts.forEach(checkbox => {
            checkbox.addEventListener("click", this.check.bind(this, checkbox));
        });
    }

    check(checkboxSelected) {
        this.checkboxElts.forEach(checkbox => {
            checkbox.checked = false;
        });
        checkboxSelected.checked = true;
    }
}