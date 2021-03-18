
export default class CheckboxSelector {
    constructor() {
        this.checkboxAllFilesInputElt = document.querySelector('input[data-checkbox-all="true"]')
        this.checkboxAllFilesLabelElt = document.querySelector('label[data-checkbox-all="true"]')
        this.init()
    }

    init() {
        this.checkboxAllFilesLabelElt.addEventListener('click', () => this.onSelectAll())
    }

    onSelectAll() {
        let checked = true;
        setTimeout(() => {
            if (this.checkboxAllFilesInputElt.checked != true) {
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

    getSelectedChecboxes() {
        const array = []
        document.querySelectorAll('[data-checkbox]').forEach(checkboxElt => {
            if (checkboxElt.checked === true) {
                array.push(checkboxElt.getAttribute('data-checkbox'))
            }
        })
        return array
    }
}