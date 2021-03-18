
export default class CheckboxSelector {
    constructor() {
        this.checkboxAllInputElt = document.querySelector('input[data-checkbox-all="true"]')
        this.checkboxAllLabelElt = document.querySelector('label[data-checkbox-all="true"]')
        this.init()
    }

    init() {
        this.checkboxAllLabelElt.addEventListener('click', () => this.onSelectAll())
    }

    onSelectAll() {
        let checked = true;
        setTimeout(() => {
            if (this.checkboxAllInputElt.checked != true) {
                checked = false
            }
            this.updateCheckboxes(checked)
        }, 50)
    }

    updateCheckboxes(checked) {
        document.querySelectorAll('[data-checkbox]').forEach(checkboxElt => {
            if (checked) {
                checkboxElt.checked = true
                return
            } 
            checkboxElt.checked = false
        })
    }

    getSelectedCheckboxes() {
        const array = []
        document.querySelectorAll('input[data-checkbox]:checked').forEach(checkboxElt => {
            array.push(checkboxElt.getAttribute('data-checkbox'))
        })
        return array
    }
}