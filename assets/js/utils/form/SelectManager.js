import TomSelect from 'tom-select'

/**
 * Manage selects with tom-select library (https://tom-select.js.org).
 */
export default class SelectManager {
    /**
     * @param {string} selector
     * @param {Object} settings
     */
    constructor(selector, settings = {}) {
        this.selectElt = document.querySelector(selector)
        this.settings = settings

        if (this.selectElt === null) {
            return
        }

        this.tomSelect = new TomSelect(this.selectElt, this.#getSettings())

        this.listSelectOptions = this.selectElt.options
    }

    /**
     * @returns {HTMLOptionElement}
     */
    getOption() {
        return this.tomSelect.getOption(this.selectElt.value)
    }

    /**
     * Add a selected item to the list.
     * 
     * @param {string} value 
     */
     addItem(value) {
        this.tomSelect.addItem(value)
    }

    /**
     * Clear all selected items and add new selected items.
     */
    updateItems(values) {
        this.tomSelect.clear()
        this.tomSelect.addItems(values)
    }

    /**
     * Clear all selected items.
     */
     clearItems() {
        this.tomSelect.clear()
    }

    /**
     * Cleart all options based on the list of ids.
     * 
     * @param {[{id: number}]} elts
     */
    clearOptionsList(elts) {
        elts.forEach(element => {
            Array.from(this.listSelectOptions).forEach(optionElt => {
                if (parseInt(optionElt.value) === parseInt(element.id)) {
                    optionElt.remove()
                    this.tomSelect.removeOption(optionElt.value)
                }
            })
        })
    }

    /**
     * Add a new option into the select.
     *
     * @param {string|number} value
     * @param {string} text
     */
    addOption(value, text) {
        const optionElt = this.#createOption(value, text)

        this.selectElt.add(optionElt, null)
        this.tomSelect.addOption(optionElt)
    }

    /**
     * Get settings (merge default and custom settings).
     */
    #getSettings() {
        const defaultSettings = {
            // create: true,
            // hideSelected: true,
            allowEmptyOption: true,
            plugins: this.selectElt.multiple ? {
                // dropdown_input: true,
                clear_button: {
                    title: 'Tout effacer',
                },
                remove_button: {
                    title:'Retirer cette option',
                },
            } : null,
            render: {
                option_create: function(data, escape) {
                    return '<div class="create">Créér <strong>' + escape(data.input) + '</strong>&hellip;</div>';
                },
                no_results:function(data,escape){
                    return '<div class="no-results text-secondary small">Pas de résultat pour "'+escape(data.input)+'"</div>';
                },
            }
        }

        return {
            ...defaultSettings,
            ...this.settings,
        }
    }

    /**
     * Create a new option element.
     *
     * @param {string|number} value
     * @param {string} text
     *
     * @returns {HTMLOptionElement}
     */
    #createOption(value, text) {
        const optionElt = document.createElement('option')
        optionElt.value = value.toString()
        optionElt.text = text

        return optionElt
    }
}