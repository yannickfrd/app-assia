import SelectType from './selectType'

/**
 * Masque ou rend visible les champs Input dépendants d'un input parent
 */
export default class DisplayFields {

    /**
     * @param {String} prefix 
     * @param {String} fieldId 
     * @param {Array} optionValues 
     */
    constructor(prefix, fieldId, optionValues = []) {
        this.selectType = new SelectType()
        this.fieldId = fieldId
        this.fieldElt = document.getElementById(prefix + fieldId)
        this.childrenElts = document.querySelectorAll(`div[data-parent-field='${fieldId}'], td[data-parent-field='${fieldId}']`)
        this.optionValues = optionValues
        this.init()
    }

    init() {
        if (null === this.fieldElt) {
            return null
        }

        switch (this.fieldElt.type) {
            case 'select-one':
                this.checkSelect();
                ['change', 'click'].forEach(eventType => {
                    this.fieldElt.addEventListener(eventType, this.checkSelect.bind(this)) // au click sur ordinateur ou au changement sur mobile
                })
                break
            case 'date':
                this.checkInput()
                this.fieldElt.addEventListener('change', this.checkInput.bind(this)) // au changement sur mobile
                break
            case 'text':
                this.checkInput()
                this.fieldElt.addEventListener('input', this.checkInput.bind(this)) // au changement sur mobile
                break
            case 'checkbox':
                this.checkbox()
                this.fieldElt.addEventListener('change', this.checkbox.bind(this)) // au changement sur mobile
                break
        }
    }

    /**
     * Vérifie le champ de type input.
     */
    checkInput() {
        if (this.fieldElt.value) {
            this.editChildrenElts(true)
        } else {
            this.editChildrenElts(false)
        }
    }

    /**
     * Vérifie le champ de type Select.
     */
    checkSelect() {
        const selectedOption = this.selectType.getOption(this.fieldElt)
        let isVisible = false

        if (this.optionValues.length > 0) {
            this.optionValues.forEach(optionValue => {
                if (optionValue === selectedOption) {
                    isVisible = true
                }
            })
        } else if (this.optionValues.length === 0 && selectedOption > 0) {
            isVisible = true
        }

        this.editChildrenElts(isVisible, selectedOption)
    }

    /**
     * Masque ou rend visible les éléments enfants de l'input parent.
     * @param {Boolean} isVisible 
     */
    editChildrenElts(isVisible, selectedOption = null) {
        this.childrenElts.forEach(elt => {
            let visibility = isVisible
            const options = elt.getAttribute('data-parent-field-options')
            if (options) {
                visibility = false
                options.split(', ').forEach(option => {
                    if (parseInt(option) === selectedOption) {
                        visibility = true
                    }
                })
            }
            this.visibleElt(elt, visibility)
        })
    }

    /**
     * Rend visible ou non un élément HTML
     * @param {HTMLElement} elt 
     * @param {Boolean} isVisible 
     */
    visibleElt(elt, visibility) {
        if (visibility === true) {
            elt.classList.remove('d-none')
            setTimeout(() => {
                elt.classList.add('fade-in')
                elt.classList.remove('fade-out')
            }, 10)
        } else {
            elt.classList.add('d-none', 'fade-out')
            elt.classList.remove('fade-in')
        }
    }

    /**
     * Vérifie le champ de type Checkbox.
     */
    checkbox() {
        let isVisible = false
        if (this.fieldElt.checked === true) {
            isVisible = true
        }
        this.editChildrenElts(isVisible)
    }
}