// Sélection d'un bouton radio
export default class SelectRadioJS {
    constructor(containerElt) {
        this.checkboxElts = document.getElementById(containerElt).querySelectorAll(".checkbox.form-check-input");
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