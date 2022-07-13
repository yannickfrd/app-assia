import MessageFlash from '../messageFlash'

/**
 * Contrôle de la validité des champs d'un formualaire
 * @return {Number}
 */
export default class FormValidator {

    constructor(containerElt = document) {
        this.containerElt = containerElt
    }

    /**
     * Vérifie le formulaire avant envoie.
     * @return {Number}
     */
    checkForm() {
        this.cleanHidedFields()
        this.checkRequiredFields()

        // Récupère toutes les catégories de champs à vérifier
        let categories = []
        document.querySelectorAll('div[data-check-valid]').forEach(elt  => {
            const category = elt.dataset.checkValid
            if (!categories.includes(category)) {
                categories.push(category)
            }
        })
        // Vérifie si les champs à contrôler sont valides
        categories.forEach(category => {
            this.checkFields(document.querySelectorAll(`div[data-check-valid=${category}]`))
        })

        const nbErrors = this.getNbErrors()
        if (nbErrors > 0) {
            this.scrollToFirstInvalidElt()
            new MessageFlash('danger', 'Veuillez corriger les erreurs indiquées avant d\'enregistrer.')
        }
        return nbErrors
    }

    /**
     * Réinitialisation du formulaire.
     */
    reinit() {
        this.containerElt.classList.remove('was-validated')
        this.containerElt.querySelectorAll('input, select, textarea').forEach(elt => {
            this.removeInvalidFeedbackElt(this.getlabel(elt))
            elt.classList.remove('is-valid', 'is-invalid')
        })
    }

    /**
     * Défilement vers le premier élément invalide
     */
    scrollToFirstInvalidElt() {
        var rectElt = this.containerElt.querySelector('.is-invalid').getBoundingClientRect()
        window.scrollTo(0, window.scrollY + rectElt.top - 90)
    }

    /**
     * Vide tous les champs masqués dnas le formulaires.
     */
    cleanHidedFields() {
        this.containerElt.querySelectorAll('div[data-parent-field].d-none').forEach(hideElt => {
            const fieldElt = hideElt.querySelector('input, select, textarea')
            if (fieldElt) {
                return fieldElt.value = ''
            }
        })
    }

    /**
     * Vérifie les champs obligatoires à la saisie.
     */
    checkRequiredFields() {
        this.containerElt.querySelectorAll('input[required], select[required]').forEach(fieldElt => {
            if (this.isFilledField(fieldElt)) {
                return this.validField(fieldElt)
            }
            if (fieldElt.type === 'select-one' && fieldElt.querySelectorAll('option').length <= 1) {
                return fieldElt.removeAttribute('required')
            }
            this.invalidField(fieldElt, 'Saisie obligatoire.')
            fieldElt.addEventListener('change', () => this.validField(fieldElt))
        })
    }

    /**
     * Vérifie les champs à compléter.
     * @param {NodeList} elts
     */
    checkFields(elts) {
        elts.forEach(elt => {
            const fieldElt = elt.querySelector('input, select')
            if (!fieldElt.classList.contains('is-invalid')) {
                if (this.isFilledField(fieldElt) || this.oneFieldOfCategoryIsFilled(elts) === false) {
                    this.validField(fieldElt)
                } else {
                    this.invalidField(fieldElt, 'Saisie obligatoire.')
                    fieldElt.addEventListener('change', () => this.validField(fieldElt))
                }
            }
        })
    }

    /**
     * Vérifie si au moins un des champs de la catégorie est complété.
     * @param {NodeList} elts
     * @return {Boolean}
     */
    oneFieldOfCategoryIsFilled(elts) {
        let value = false
        elts.forEach(elt => {
            if (this.isFilledField(elt.querySelector('input, select'))) {
                value = true
            }
        })
        return value
    }

    /**
     * Vérfifie si le champ est complété.
     * @param {HTMLElement} fieldElt 
     * @return {Boolean}
     */
    isFilledField(fieldElt) {
        if ((['text', 'number', 'date', 'select-one'].includes(fieldElt.type) && '' != fieldElt.value)
            || ('checkbox' === fieldElt.type && true === fieldElt.checked)) {
            return true
        }
        return false
    }

    /**
     * Met le champ en valide.
     * @param {HTMLElement} fieldElt 
     * @return {Boolean} 
     */
    validField(fieldElt, addClassIsValid = true) {
        this.removeInvalidFeedbackElt(this.getlabel(fieldElt))
        fieldElt.classList.remove('is-valid', 'is-invalid')
        if (fieldElt.value && addClassIsValid === true) {
            fieldElt.classList.add('is-valid')
        }
        return true
    }

    /**
     * Met le champ en invalide et indique un message d'erreur.
     * @param {HTMLElement} fieldElt 
     * @param {string} msg 
     * @return {Boolean} 
     */
    invalidField(fieldElt, msg = 'Saisie incorrecte.') {
        const labelElt = this.getlabel(fieldElt)

        fieldElt.classList.remove('is-valid')
        fieldElt.classList.add('is-invalid')

        this.removeInvalidFeedbackElt(labelElt)

        labelElt.appendChild(this.createInvalidFeedbackElt(msg))

        return false
    }

    /**
     * Crée l'élément avec l'information de l'erreur.
     * @param {string} msg 
     * @return {HTMLDivElement}
     */
    createInvalidFeedbackElt(msg) {
        const elt = document.createElement('div')
        elt.className = 'invalid-feedback d-block'
        elt.dataset.invalid = 'true'
        elt.textContent = msg

        return elt
    }

    /**
     * Donne le label du champ.
     * @param {HTMLElement} fieldElt 
     */
    getlabel(fieldElt) {
        const labelElt = fieldElt.parentNode.parentNode.querySelector('label')
        if (labelElt) {
            return labelElt
        }
        return fieldElt.parentNode
    }

    /**
     * Supprime l'élément d'invalidité du champ.
     * @param {HTMLElement} labelElt 
     */
    removeInvalidFeedbackElt(labelElt) {
        const invalidFeedbackElt = labelElt.querySelector('div[data-invalid]')
        if (invalidFeedbackElt) {
            invalidFeedbackElt.remove()
        }
    }

    /**
     * Renvoie le nombre de champs invalides.
     * @return {Number}
     */
    getNbErrors() {
        const invalidFields = this.containerElt.querySelectorAll('div[data-invalid]')
        const nbErrors = invalidFields.length

        if (nbErrors > 0) {
            console.error(nbErrors + ' error' + (nbErrors > 1 ? 's' : '') + ' :')
            invalidFields.forEach(field => {
                console.error(field.parentElement.textContent)
            })
        }

        return nbErrors
    }

    checkIntervalBeetweenDates(startDateElt, endDateElt, msg = 'Date de fin antérieure à la date de début.') {
        const intervalWithStart = (new Date(endDateElt.value) - new Date(startDateElt.value)) / (24 * 3600 * 1000)

        if (intervalWithStart < 0) {
            return this.invalidField(endDateElt, msg)
        }
    }

    /**
     * Check if a field is valid or not.
     * @param {HTMLElement} fieldElt 
     * @param {String} msg 
     */
    checkField(fieldElt, msg = 'Saisie obligatoire') {
        if (!this.isFilledField(fieldElt)) {
            return this.invalidField(fieldElt, msg)
        }
        
        return this.validField(fieldElt)
    }

    /**
     * Check is a date input is valid.
     * @param {HTMLInputElement} inputElt 
     * @return {Boolean} 
     */
    checkDate(inputElt, min = -(365 * 99), max = (365 * 99), msg = 'Date invalide.', addClassIsValid = true) {
        const interval = Math.round((new Date(inputElt.value) - new Date()) / (24 * 3600 * 1000))

        if ((inputElt.value && !Number.isInteger(interval))
            || interval < min || interval > max) {
            return this.invalidField(inputElt, msg)
        }

        return this.validField(inputElt, addClassIsValid)
    }

    /**
     * Check is a amount input is valid.
     * @param {HTMLInputElement} inputElt 
     * @return {Boolean} 
     */
    checkAmount(inputElt, min = 0, max = 99999, resetValue = false, msg = 'Montant invalide.') {
        const value = parseFloat(inputElt.value.replaceAll(' ', '').replace(',', '.'))

        if (!isNaN(value) && true === resetValue) {
            inputElt.value = value
        }

        if ((inputElt.value && isNaN(value)) || value < min || value > max) {
            return this.invalidField(inputElt, msg)
        }

        return this.validField(inputElt)
    }
}