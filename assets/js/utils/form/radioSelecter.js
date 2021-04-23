/**
 * Sélection d'un bouton radio
 */
export default class RadioSelecter {
    constructor(containerElt) {
        this.checkboxElts = document.getElementById(containerElt).querySelectorAll('.checkbox.form-check-input')
        this.init()
    }

    init() {
        this.checkboxElts.forEach(checkboxElt => {
            checkboxElt.addEventListener('click', this.check.bind(this, checkboxElt))
        })
    }

    check(checkboxSelected) {
        this.checkboxElts.forEach(checkboxElt => {
            checkboxElt.checked = false
        })
        checkboxSelected.checked = true
    }
}