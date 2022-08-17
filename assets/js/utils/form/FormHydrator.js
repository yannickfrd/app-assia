import SelectManager from "./SelectManager"

export default class FormHydrator
{
    /**
     * @param {HTMLFormElement} formElt 
     * @param {SelectManager[]} selectManagers
     */
    constructor(formElt, selectManagers) {
        this.formElt = formElt
        this.selectManagers = selectManagers
    }

    /**
     * @param {Object} object // entity
     */
     hydrate(object) {
        this.formElt.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(fieldElt => {
            const key = fieldElt.id.split('_').pop()
            const value = object[key]

            if (value === undefined) {
                return
            }

            if (fieldElt.type === 'checkbox') {
                return fieldElt.checked = value
            }

            if (fieldElt.type === 'datetime') {
                return fieldElt.value = value ? value.substring(0, 15) : ''
            }

            if (fieldElt.type === 'date') {
                return fieldElt.value = value ? value.substring(0, 10) : ''
            }
            
            if (fieldElt.type === 'time') {
                return fieldElt.value = value ? value.substring(11, 5) : ''
            }

            if (value instanceof Array) {
                const ids = []
                value.forEach(item => ids.push(item.id))
                return this.selectManagers[key].updateItems(ids)
            }

            if (value instanceof Object) {
                return this.setObjectField(fieldElt, value)
            } else if (value === null) {
                fieldElt.disabled = false
            }

            fieldElt.value = value ?? ''
        })
    }

    /**
     * @param {HTMLSelectElement} selectElt
     * @param {Object} object // entity
     */
    setObjectField(selectElt, object) {
        selectElt.value = object.id

        if (selectElt.value === '') {
            this.addOptionElt(selectElt, object)
        }

        selectElt.disabled = selectElt.value && selectElt.dataset.disabledIfValue
    }

    /**
     * @param {HTMLSelectElement} selectElt
     * @param {Object} object // entity
     */
     addOptionElt(selectElt, object) {
        const optionElt = document.createElement('option')
        const choiceLabel =  selectElt.dataset.choiceLabel
        const keys = choiceLabel.split('_')
        let value = object

        if (!choiceLabel) {
            return
        }

        for (let i = 1; i < keys.length; i++) {
            value = value[keys[i]]
        }        

        optionElt.value = object.id
        optionElt.textContent = value

        selectElt.appendChild(optionElt)
        selectElt.value = object.id
    }
}