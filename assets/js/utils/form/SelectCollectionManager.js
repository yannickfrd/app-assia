/**
 * Permet d'ajouter un item d'une liste déroulante (select) dans une collection.
 */
export default class SelectCollectionManager {

    /**
     * @param {string} selectId
     * @param {CallableFunction} callbackAfterRemove
     * @param {CallableFunction} callbackAfterAdd
     * @param {number} delay
     */
    constructor(selectId, callbackAfterAdd = null, callbackAfterRemove = null, delay = 200) {
        this.selectElt = document.getElementById(selectId)
        this.trElts = document.querySelectorAll(`tr[data-parent-select="${selectId}"]`)
        this.callbackAfterAdd = callbackAfterAdd
        this.callbackAfterRemove = callbackAfterRemove
        this.delay = delay
        this.other = 1000
    }

    init() {
        if (!this.selectElt) {
            return null
        }

        this.selectElt.addEventListener('change', () => this.addElt())
        this.trElts.forEach(trElt => this.addEventRemove(trElt))
    }

    /**
     * Ajoute un élément prototypé dans la liste.
     */
    addElt(focusFirst = true, callbackNewElt = false) {
        this.listElt = document.getElementById(this.selectElt.id + '_list')
        // Try to find the counter of the list or use the length of the list
        const counter = parseInt(this.listElt.dataset.itemsCounter || this.listElt.children().length) 
        // grab the prototype template
        let prototypeString = this.listElt.dataset.prototype
        const containerTag = this.listElt.dataset.containerTag
        // replace the '__name__' used in the id and name of the prototype with a number that's unique to your emails
        prototypeString = prototypeString.replace(/__name__/g, counter)
        // Increase the counter and store it, the length cannot be used if deleting widgets is allowed
        this.listElt.dataset.itemsCounter = counter + 1
        
        // create a new list element
        const newElt = document.createElement(containerTag)
        newElt.innerHTML = prototypeString
            
        newElt.querySelector('input[type="hidden"]').value = this.selectElt.value
        if (parseInt(this.selectElt.value) !== this.other) {
            newElt.querySelector('td[data-name]').textContent = this.selectElt.querySelector('option:checked').textContent
        }
        this.addEventRemove(newElt)
              
        // Add item to the list
        this.listElt.appendChild(newElt)

        if (focusFirst) this.focusFirstInput(newElt)

        if (this.callbackAfterAdd) this.callbackAfterAdd(callbackNewElt ? newElt : null)
        
        window.setTimeout(() => {
            this.reinitSelect()
        }, this.delay)
    }

    /**
     * Met le focus sur le premier input.
     * @param {HTMLTableRowElement} trElt 
     */
    focusFirstInput(trElt) {
        const inputTextElt = trElt.querySelector('input[type="text"]')
        if (inputTextElt) inputTextElt.focus()
    }

    /**
     * Remplace le select sur l'option par défaut.
     */
    reinitSelect() {
        this.selectElt.querySelector('option').selected = 'selected'
    }

    /**
     * Retire la ligne correspondante dans le tableau.
     * @param {HTMLTableRowElement} trElt 
     */
    addEventRemove(trElt) {
        trElt.querySelector('button[data-action="remove"]').addEventListener('click', e => {
            e.preventDefault()
            trElt.classList.add('fade-out')
            setTimeout(() => {
                trElt.remove()
                if (this.callbackAfterRemove) this.callbackAfterRemove()
            }, this.delay)
        }) 
    }
}