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
        this.init()
    }

    init() {      
        if (null === this.parentFieldElt) {
            return console.error('No parent field with the name :' + this.elt.dataset.parentField)
        }

        switch (this.parentFieldElt.type) {
            case 'select-one':
                this.checkSelect();
                this.parentFieldElt.addEventListener('click', () => this.checkSelect())
                this.parentFieldElt.addEventListener('change', () => this.checkSelect())
                break
            case 'date':
                this.checkInput()
                this.parentFieldElt.addEventListener('change', () => this.checkInput())
                break
            case 'text':
                this.checkInput()
                this.parentFieldElt.addEventListener('input', () => this.checkInput())
                break
            case 'checkbox':
                this.checkbox()
                this.parentFieldElt.addEventListener('change', () => this.checkbox())
                break
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
                if (option === this.parentFieldElt.value) {
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