//
class SelectRadioJS {
    constructor() {
        this.checkboxElts = document.querySelectorAll("table .checkbox.form-check-input");
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

let selectRadioJS = new SelectRadioJS();