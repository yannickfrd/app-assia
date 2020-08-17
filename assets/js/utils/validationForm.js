import SelectType from './selectType'
import MessageFlash from '../utils/messageFlash'

/**
 * Contrôle de la validité des champs d'un formualaire
 * @return {Number}
 */
export default class ValidationForm {

    constructor(containerElt = document) {
        this.containerElt = containerElt
        this.selectType = new SelectType()
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
        document.querySelectorAll('div[data-check-valid]').forEach(elt => {
            let category = elt.getAttribute('data-check-valid')
            if (categories.indexOf(category) === -1) {
                categories.push(category)
            }
        })
        // Vérifie si les champs à contrôler sont valides
        categories.forEach(category => {
            this.checkFields(document.querySelectorAll(`div[data-check-valid=${category}]`))
        })

        let nbErrors = this.getNbErrors()
        if (nbErrors > 0) {
            this.scrollToFirstInvalidElt()
            new MessageFlash('danger', 'Veuillez corriger les erreurs indiquées avant d\'enregistrer.')
        }
        return nbErrors
    }

    /**
     * Défilement vers le premier élément invalide
     */
    scrollToFirstInvalidElt() {
        var rectElt = this.containerElt.querySelector('.is-invalid').getBoundingClientRect()
        window.scrollTo(0, window.scrollY + rectElt.top - 70)
    }

    /**
     * Vide tous les champs masqués dnas le formulaires.
     */
    cleanHidedFields() {
        this.containerElt.querySelectorAll('div[data-parent-field].d-none').forEach(hideElt => {
            let fieldElt = hideElt.querySelector('input, select, textarea')
            if (fieldElt.type === 'select-one') {
                this.selectType.setOption(fieldElt, null)
            } else {
                fieldElt.value = null
            }
        })
    }

    /**
     * Vérifie les champs obligatoires à la saisie.
     */
    checkRequiredFields() {
        this.containerElt.querySelectorAll('input[required], select[required]').forEach(fieldElt => {
            if (this.isFilledField(fieldElt)) {
                this.validField(fieldElt)
            } else {
                this.invalidField(fieldElt, 'Saisie obligatoire.')
                fieldElt.addEventListener('change', () => this.validField(fieldElt))
            }
        })
    }

    /**
     * Vérifie les champs à compléter.
     * @param {NodeList} elts 
     * @param {Boolean} requiredField 
     */
    checkFields(elts, requiredField = null) {
        let hasDatas = requiredField ? null : this.oneFieldIsFilled(elts)
        elts.forEach(elt => {
            let fieldElt = elt.querySelector('input, select')
            if (!fieldElt.classList.contains('is-invalid')) {
                if (this.isFilledField(fieldElt) || hasDatas === false) {
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
    oneFieldIsFilled(elts) {
        let value = false
        elts.forEach(elt => {
            if (this.isFilledField(elt.querySelector('input, select'))) {
                value = true
            }
        })
        return value
    }

    /**
     * Vérfifie si le champ est complété
     * @param {HTMLElement} fieldElt 
     * @return {Boolean}
     */
    isFilledField(fieldElt) {
        let value = this.selectType.getOption(fieldElt)
        if ((fieldElt.nodeName === 'INPUT' && fieldElt.value != '') ||
            (fieldElt.type === 'checkbox' && fieldElt.checked === true) ||
            (fieldElt.type === 'select-one' && value != '' && !isNaN(value))) {
            return true
        }
        return false
    }

    /**
     * Met le champ en valide 
     * @param {HTMLElement} fieldElt 
     */
    validField(fieldElt) {
        this.removeInvalidFeedbackElt(this.getlabel(fieldElt))
        fieldElt.classList.remove('is-valid', 'is-invalid')
        if (fieldElt.value) {
            fieldElt.classList.add('is-valid')
        }
    }

    /**
     * Met le champ en invalide et indique un message d 'erreur.
     * @param {HTMLElement} fieldElt 
     * @param {string} msg 
     */
    invalidField(fieldElt, msg = 'Saisie incorrecte.') {
        const labelElt = this.getlabel(fieldElt)

        fieldElt.classList.remove('is-valid')
        fieldElt.classList.add('is-invalid')

        this.removeInvalidFeedbackElt(labelElt)

        labelElt.appendChild(this.createInvalidFeedbackElt(msg))
    }

    /**
     * Crée l 'élément avec l'information de l 'erreur.
     * @param {string} msg 
     * @return {HTMLDivElement}
     */
    createInvalidFeedbackElt(msg) {
        let elt = document.createElement('div')
        elt.className = 'invalid-feedback d-block js-invalid'
        elt.innerHTML = `
                <span class='form-error-icon badge badge-danger text-uppercase'>Erreur</span> 
                <span class='form-error-message'>${msg}</span>`

        return elt
    }

    /**
     * Donne le label du champ
     * @param {HTMLElement} fieldElt 
     */
    getlabel(fieldElt) {
        let labelElt = fieldElt.parentNode.parentNode.querySelector('label')
        if (labelElt) {
            return labelElt
        }
        return fieldElt.parentNode
    }

    /**
     * Supprime l 'élement d'invalidité du champ
     * @param {HTMLElement} labelElt 
     */
    removeInvalidFeedbackElt(labelElt) {
        let invalidFeedbackElt = labelElt.querySelector('div.js-invalid')
        if (invalidFeedbackElt) {
            invalidFeedbackElt.remove()
        }
    }

    /**
     * Renvoie le nombre de champs invalides.
     * @return {Number}
     */
    getNbErrors() {
        let nbErrors = this.containerElt.querySelectorAll('.js-invalid').length

        if (nbErrors > 0) {
            console.error(nbErrors + ' error(s)')
        }

        return nbErrors
    }
}