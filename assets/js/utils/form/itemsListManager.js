/**
 * Manage the items list.
 */
export default class ItemsListManager {

    /**
     * @param {String} eltId
     * @param {CallableFunction} callback
     */
    constructor(eltId, callback = null) {
        this.eltId = eltId
        this.selectElt = document.getElementById(eltId)
        this.trElts = document.querySelectorAll(`tr[data-parent-select="${eltId}"]`)
        this.callback = callback
        this.init()
    }

    init() {
        if (!this.selectElt) {
            return null
        }

        this.selectElt.addEventListener('change', () => this.addOption())

        this.trElts.forEach(trElt => {
            this.displayTr(trElt)
            trElt.querySelector('button[data-action="remove"')
                .addEventListener('click', e => {
                    e.preventDefault()
                    this.removeTr(trElt)
                    if (this.callback) {
                        this.callback()
                    }
                }) 
        })
    }

    /**
     * Ajoute l'option sélectionnée de la liste déroulante.
     */
    addOption() {
        this.selectElt.querySelectorAll('option').forEach(option => {
            if (option.selected) {
                this.trElt = document.querySelector(`tr[data-parent-select="${this.eltId}"][data-value="${option.value}"]`)
                this.displayTr(this.trElt, 1)
                if (this.selectElt.dataset.type === 'resources') {
                    const type = this.trElt.dataset.value
                    const spId = this.trElt.dataset.spId
                    document.querySelectorAll(`tr[data-value="${type}"][data-sp-id="${spId}"]`).forEach(trElt => {
                        this.displayTr(trElt, 1)
                    })
                }
            }
        })

        this.initTrElts()
        this.reinitSelect()
    }

    /**
     * Affiche ou masque l'affichage d'une ligne du tableau.
     * @param {HTMLTableRowElement} trElt 
     */
    displayTr(trElt, newValue = null) {
        const inputElt = trElt.querySelector('input')
        
        if (newValue && (!inputElt.value || this.trElt === trElt)) {
            inputElt.value = newValue
        }

        if (inputElt.value != 1) {
            return trElt.classList.replace('d-table-row', 'd-none')
        }
        trElt.classList.replace('d-none', 'd-table-row')
    }

    /**
     * Met tous les autres inputs du tableau à 0 si vide
     */
    initTrElts() {
        this.trElts.forEach(trElt => {
            const inputNumberElt = trElt.querySelector('input[type="number"]')
            const inputTextElt = trElt.querySelector('input[type="text"]')
            if (!inputNumberElt.value) {
                inputNumberElt.value = 0
            }
            if (inputTextElt && !inputTextElt.dataset.edit) {
                inputTextElt.dataset.edit = 1
            }    
        })
    }

    /**
     * Remplace le select sur l'option par défaut.
     */
    reinitSelect() {
        window.setTimeout(() => {
            this.selectElt.querySelector('option').selected = 'selected'
            const inputTextElt = this.trElt.querySelector('input[type="text"]')
            if (inputTextElt) {
                inputTextElt.focus()
            }
        }, 200)
    }

    /**
     * Retire la ligne correspondante dans le tableau.
     * @param {HTMLTableRowElement} trElt 
     */
    removeTr(trElt) {
        trElt.querySelectorAll('input').forEach(inputElt => {
            inputElt.getAttribute('type') === 'number' ? inputElt.value = 0 : inputElt.value = null
        })
        trElt.classList.replace('d-table-row', 'd-none')
    }
}