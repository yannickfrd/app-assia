/**
 * Masque ou rend visible un élement en fonction d'un champ parent (select, input, checkbox).
 */
export default class FieldDisplayer {
    /**
     * @param {HTMLElement} elt 
     */
    constructor(elt) {
        this.elt = elt
        this.parentFieldElt = document.getElementById(elt.dataset.parentField)
        this.optionSelected = null
        this.init()
    }

    init() {      
        if (null === this.parentFieldElt) {
            return console.error('No parent field with the name :' + this.elt.dataset.parentField)
        }

        switch (this.parentFieldElt.type) {
            case 'select-one':
                return this.initSelect()
            case 'date':
                this.parentFieldElt.addEventListener('change', () => this.checkInput())
                return this.checkInput()
            case 'text':
                this.parentFieldElt.addEventListener('input', () => this.checkInput())
                return this.checkInput()
            case 'checkbox':
                this.parentFieldElt.addEventListener('change', () => this.checkbox())
                return this.checkbox()
        }
    }

    check() {
        switch (this.parentFieldElt.type) {
            case 'select-one':
                this.checkSelect();
                break
            case 'checkbox':
                this.checkbox()
                break
            default:
                this.checkInput()
        }
    }
    
    initSelect() {
        this.checkSelect()
        this.parentFieldElt.addEventListener('click', () => this.checkSelect())
        this.parentFieldElt.addEventListener('change', () => this.checkSelect())
    }

    /**
     * Vérifie le champ de type Select.
     */
    checkSelect() {
        const options = this.elt.dataset.options
        let isVisible = false

        if (!options) {
            return console.error('No option defined for the select field : ' + this.elt.dataset.parentField)
        }

        if ('*' === options && this.parentFieldElt.value) {
            isVisible = true
        } else {
            options.split('|').forEach(option => {
                if (option === this.parentFieldElt.value || option === this.optionSelected) {
                    isVisible = true
                }
            })
        }

        this.visibleElt(isVisible)
    }

    /**
     * Vérifie le champ de type input.
     */
    checkInput() {
        this.visibleElt(this.parentFieldElt.value ? true : false)
    }

    /**
     * Vérifie le champ de type Checkbox.
     */
    checkbox() {
        this.visibleElt(true === this.parentFieldElt.checked)
    }

    /**
     * Rend visible ou non un élément HTML
     * @param {Boolean} isVisible 
     */
    visibleElt(isVisible) {
        if (true === isVisible) {
            this.elt.classList.remove('d-none')
            setTimeout(() => {
                this.elt.classList.add('fade-in')
                this.elt.classList.remove('fade-out')
            }, 10)
        } else {
            this.elt.classList.add('d-none', 'fade-out')
            this.elt.classList.remove('fade-in')
        }
    }
}