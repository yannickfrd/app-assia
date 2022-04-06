/**
 * Tool to multi-select checboxes.
 */
export default class CheckboxSelector {
    constructor() {
        this.checkboxAllInputElt = document.querySelector('input[data-checkbox-all="true"]')
        this.init()
    }

    init() {
        if (this.checkboxAllInputElt) {
            this.checkboxAllInputElt.addEventListener('click', () => this.toggle());
        }
    }

    /**
     * Toggle to select all items or nothing.
     */
    toggle() {
        let checked = true;
        setTimeout(() => {
            if (this.checkboxAllInputElt.checked != true) {
                checked = false
            }
            this.updateItems(checked)
        }, 50)
    }

    /**
     * Update all items.
     */
    updateItems(checked) {
        document.querySelectorAll('input[data-checkbox]').forEach(checkboxElt => {
            if (checked) {
                checkboxElt.checked = true
                return
            } 
            checkboxElt.checked = false
        })
    }

    /**
     * Return all checked items.
     * @returns {Array}
     */
    getItems() {
        const items = []
        document.querySelectorAll('input[data-checkbox]:checked').forEach(checkboxElt => {
            items.push(checkboxElt.dataset.checkbox)
        })
        return items
    }
}