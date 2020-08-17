import SelectType from './selectType'

/**
 * Masque ou rend visible les champs Input dépendants d'un input parent
 */
export default class DisplayFields {

    /**
     * @param {String} prefix 
     * @param {String} inputId 
     * @param {Array} optionValues 
     */
    constructor(prefix, inputId, optionValues = []) {
        this.selectType = new SelectType()
        this.inputElt = document.getElementById(prefix + inputId)
        this.childrenElts = document.querySelectorAll(`div[data-parent-field='${inputId}'`)
        this.optionValues = optionValues
        this.init()
    }

    init() {
        if (this.inputElt === null) {
            return null
        }

        switch (this.inputElt.type) {
            case 'select-one':
                this.checkSelect()
                this.inputElt.addEventListener('change', this.checkSelect.bind(this)) // au changement sur mobile
                this.inputElt.addEventListener('click', this.checkSelect.bind(this)) // au click sur ordinateur 
                break
            case 'date':
                this.checkInput()
                this.inputElt.addEventListener('change', this.checkInput.bind(this)) // au changement sur mobile
                break
            case 'checkbox':
                this.checkbox()
                this.inputElt.addEventListener('change', this.checkbox.bind(this)) // au changement sur mobile
                break
        }
    }

    /**
     * Vérifie le champ de type input.
     */
    checkInput() {
        if (this.inputElt.value) {
            this.editChildrenElts(true)
        } else {
            this.editChildrenElts(false)
        }
    }

    /**
     * Vérifie le champ de type Select.
     */
    checkSelect() {
        let selectedOption = this.selectType.getOption(this.inputElt)
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

        this.editChildrenElts(isVisible)
    }

    /**
     * Masque ou rend visible les éléments enfants de l'input parent.
     * @param {Boolean} isVisible 
     */
    editChildrenElts(isVisible) {
        this.childrenElts.forEach(elt => {
            if (isVisible) {
                elt.classList.remove('d-none')
                setTimeout(() => {
                    elt.classList.add('fade-in')
                    elt.classList.remove('fade-out')
                }, 10)
            } else {
                elt.classList.add('d-none', 'fade-out')
                elt.classList.remove('fade-in')
            }
        })
    }

    /**
     * Vérifie le champ de type Checkbox.
     */
    checkbox() {
        let isVisible = false
        if (this.inputElt.checked === true) {
            isVisible = true
        }
        this.editChildrenElts(isVisible)
    }
}