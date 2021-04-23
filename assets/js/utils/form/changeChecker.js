/**
 * Vérifie que l'utilisateur à sauvegarder ses modifications avant de changer de page.
 */
export default class changeChecker {

    constructor(formName) {
        this.formElt = document.querySelector(`form[name=${formName}`)
        this.aElts = document.querySelectorAll('a[href]')
        this.typeElts = this.formElt.querySelectorAll('input, select, textarea')
        this.btnSubmitElts = this.formElt.querySelectorAll('button[type=submit]')
        this.ischanged = false
        this.init()
    }

    init() {
        this.typeElts.forEach(typeElt => {
            typeElt.addEventListener('change', this.onChange.bind(this))
        })
        this.btnSubmitElts.forEach(btnSubmitElt => {
            btnSubmitElt.addEventListener('click', this.clearChange.bind(this))
        })
        this.aElts.forEach(aElt => {
            aElt.addEventListener('click', e => {
                this.confirm(e)
            })
        })
    }

    onChange() {
        this.ischanged = true
    }

    clearChange() {
        this.ischanged = false
    }

    /**
     * Demmande de confirmer le choix
     */
    confirm(e) {
        if (this.ischanged) {
            if (!window.confirm('Attention, vous n\'avez pas sauvegarder vos modifications. \nContinuer sans sauvegarder ? ')) {
                e.preventDefault()
            }
        }
    }
}