/**
 * Sélection d'un bouton radio
 */
export default class RadioSelecter {
    constructor(containerElt) {
        this.radioElts = document.getElementById(containerElt).querySelectorAll('input[type="radio"]')
        this.init()
    }

    init() {
        this.radioElts.forEach(radioElt => {
            radioElt.addEventListener('click', this.check.bind(this, radioElt))
        })
    }

    check(radioSelected) {
        this.radioElts.forEach(radioElt => {
            if (radioElt !== radioSelected) {
                radioElt.checked = false
            }
        })
    }
}