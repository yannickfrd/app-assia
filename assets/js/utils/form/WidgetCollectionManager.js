/**
 * Permet d'ajouter des éléments dans une collection.
 */
export default class WidgetCollectionManager {

    constructor() {
        this.btnElts = document.querySelectorAll('button[data-add-widget]')
        this.list = null
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
     */
    addElt(btnElt) {
        this.listElt = document.querySelector(btnElt.dataset.listSelector)
        // Try to find the counter of the list or use the length of the list
        const counter = parseInt(this.listElt.dataset.widgetCounter || this.listElt.children.length)
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
                }, 200)
            })
        }
    }
}