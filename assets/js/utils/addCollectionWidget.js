/**
 * Permet d'ajouter des éléments dans une collection.
 */
export default class AddCollectionWidget {

    constructor() {
        this.btnElts = document.querySelectorAll('.add-another-collection-widget')
        this.list = null
        this.counter = 0
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
     * Ajoute un élément prototypé dans la liste.
     * @param {HTMLButtonElement} btnElt 
     */
    addElt(btnElt) {
        this.list = document.querySelector(btnElt.dataset.listSelector)
        this.counter = parseInt(this.list.dataset.widgetCounter || this.list.children().length) // Try to find the counter of the list or use the length of the list
        // grab the prototype template
        let newWidget = this.list.dataset.prototype
        // replace the '__name__' used in the id and name of the prototype with a number that's unique to your emails
        newWidget = newWidget.replace(/__name__/g, this.counter)
        // Increase the counter and store it, the length cannot be used if deleting widgets is allowed
        this.list.dataset.widgetCounter = this.counter + 1
        
        // create a new list element
        const trElt = document.createElement('tr')
        trElt.innerHTML = newWidget

        // Add the delete link
        trElt.appendChild(this.getBtnElt(trElt))
        // Add item to the list
        this.list.appendChild(trElt)
    }

    /**
     * Ajoute un lien de suppression d'une catégorie.
     * @param {HTMLElement} elt 
     */
    getBtnElt(elt)
    {
        const tdElt = document.createElement('td')
        tdElt.className = 'align-middle'

        const btnElt = document.createElement('boutton')
        btnElt.className = 'btn btn-danger'
        btnElt.dataset.action = 'remove'
        btnElt.innerHTML = '<span class="fas fa-trash-alt"></span>'

        btnElt.addEventListener('click', e => {
            e.preventDefault()
            elt.remove()
        })

        tdElt.appendChild(btnElt)
        
        return tdElt
    }
}