// import ValidationForm from './validationForm'

import ValidationForm from '../validationForm'

/**
 * Permet de vérfiier la validité d 'une date.
 */
export default class ValidationDate {

    constructor(inputElt, validationForm = new ValidationForm()) {
        this.inputElt = inputElt
        this.validationForm = validationForm
        this.now = new Date()
        this.date = inputElt.value ? new Date(inputElt.value) : null
        this.intervalWithNow = (this.now - this.date) / (24 * 3600 * 1000)
    }

    /**
     * Donne l'interval entre la date et maintenant.
     * @return {Number}
     */
    getIntervalWithNow() {
        return this.intervalWithNow
    }

    /**
     * Retourne vrai ou faux si la date est valide.
     * @return {Boolean}
     */
    isValid() {
        if (this.date === null) {
            this.inputElt.value = ''
            return
        }

        if (isNaN(this.intervalWithNow)) {
            return this.invalid('Date invalide.')
        }

        if (this.intervalWithNow > (365 * 99) || this.intervalWithNow < -(365 * 20)) {
            return this.invalid('Date invalide.')
        }

        return true
    }

    /**
     * Retourne vrai ou faux si la date est après aujourd'hui.
     * @return {Boolean}
     */
    isNotAfterToday() {
        if (this.intervalWithNow < 0) {
            return this.invalid('Ne peut pas être postérieure à la date du jour.')
        }
        return true
    }

    /**
     * Retourne vrai ou faux si la date est supérieure à un an.
     * @return {Boolean}
     */
    isNotAfterOneYear() {
        if (this.intervalWithNow < -365) {
            return this.invalid('Ne peut pas être supérieur à plus d\'un an.')
        }
        return true
    }

    /**
     * Retourne vrai ou faux si la date est dans l'interval.
     * @return {Boolean}
     */
    isValidInterval(maxInterval) {
        if (this.intervalWithNow > maxInterval || this.intervalWithNow < -maxInterval) {
            return invalid('Date incorrecte.')
        }

        return true
    }

    getIntervaltoYears() {
        return this.intervalWithNow / 365
    }

    /**
     * Date invalide.
     */
    invalid(msg) {
        console.error('Invalid date !')
        if (this.validationForm != null) {
            this.validationForm.invalidField(this.inputElt, msg)
        }
        return false
    }
}