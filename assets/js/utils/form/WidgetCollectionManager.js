/**
 * Permet d'ajouter des éléments dans une collection.
 */
export default class WidgetCollectionManager {

    /**
     * @param {CallableFunction} actionAfterAdding
     * @param {CallableFunction} actionAfterRemoving
     * @param {number} limit 
     * @param {number} delay 
     */
    constructor(actionAfterAdding = null, actionAfterRemoving = null, limit = null, delay = 200) {
        this.btnElts = document.querySelectorAll('button[data-add-widget]')
        this.listElt = null
        this.actionAfterAdding = actionAfterAdding
        this.actionAfterRemoving = actionAfterRemoving
        this.limit = limit
        this.delay = delay
        this.init()
    }

    init() {
        this.btnElts.forEach(btnElt => {
            btnElt.addEventListener('click', e => {
                e.preventDefault()
                this.addElt(btnElt)
            })
        })
    }

    /**
     * Add a element from a prototype in a container.
     * @param {HTMLButtonElement} btnElt 
     * @return {HTMLElement}
     */
    addElt(btnElt) {
        this.listElt = document.querySelector(btnElt.dataset.listSelector)
        // Try to find the counter of the list or use the length of the list
        const counter = parseInt(this.listElt.dataset.widgetCounter || this.listElt.children.length)
        //Check the limit
        if ((counter + 1) === this.limit) {
            btnElt.classList.add('d-none')
        }
        if (this.limit && counter >= this.limit) {
            return false
        }
        // Grab the prototype template
        let prototypeString = this.listElt.dataset.prototype
        const containerTag = this.listElt.dataset.containerTag
        // Replace the '__name__' used in the id and name of the prototype with a number that's unique to your emails
        prototypeString = prototypeString.replace(/__name__/g, counter)
        // Increase the counter and store it, the length cannot be used if deleting widgets is allowed
        this.listElt.dataset.widgetCounter = counter + 1
        // create a new list element
        const newElt = document.createElement(containerTag)
        newElt.innerHTML = prototypeString
        // Add listener to button to remove the widget element
        this.addEventRemove(newElt)
        // Add item to the list
        this.listElt.appendChild(newElt)

        if (this.actionAfterAdding) this.actionAfterAdding()

        return newElt
    }

    /**
     * @param {HTMLElement} elt 
     */
    addEventRemove(elt) {
        const btnElt = elt.querySelector('button[data-action="remove"]')
        if (btnElt) {
            btnElt.addEventListener('click', e => {
                e.preventDefault()
                elt.classList.add('fade-out')

                setTimeout(() => {
                    elt.remove()
                    this.listElt.dataset.widgetCounter -= 1
                    this.btnElts.forEach(btnElt => btnElt.classList.remove('d-none'))
                    if (this.actionAfterRemoving) this.actionAfterRemoving()
                }, this.delay)
            })
        }
    }
}