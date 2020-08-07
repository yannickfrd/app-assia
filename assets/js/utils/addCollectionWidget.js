// Permet d'ajouter des éléments dans une collection
export default class AddCollectionWidget {

    constructor() {
        this.btnElts = document.querySelectorAll(".add-another-collection-widget");
        this.list = null;
        this.counter = null;
        this.init();
    }

    init() {
        this.btnElts.forEach(btnElt => {
            btnElt.addEventListener("click", e => {
                e.preventDefault();
                this.addElt(btnElt);
            });
        });
    }

    // Ajoute un élément prototypé dans la liste
    addElt(btnElt) {
        this.list = document.querySelector(btnElt.getAttribute("data-list-selector"));
        this.counter = this.list.getAttribute("data-widget-counter") || this.list.children().length; // Try to find the counter of the list or use the length of the list
        // grab the prototype template
        let newWidget = this.list.getAttribute("data-prototype");
        // replace the "__name__" used in the id and name of the prototype with a number that's unique to your emails
        newWidget = newWidget.replace(/__name__/g, this.counter);
        // Increase the counter and store it, the length cannot be used if deleting widgets is allowed
        this.list.setAttribute("data-widget-counter", this.counter + 1);
        // create a new list element and add it to the list
        let newElt = $(this.list.getAttribute("data-widget-tags")).html(newWidget);
        // Add the delete link
        this.addDeleteBtn(newElt);
        // Add the element
        newElt.appendTo(this.list);
    }

    // Ajoute un lien de suppression d'une catégorie
    addDeleteBtn(newElt) {
        let deleteBtnElt = $('<td class="align-middle"><button class="btn btn-danger js-remove"><span class="fas fa-trash-alt"></span></button></td>');
        newElt.append(deleteBtnElt);
        deleteBtnElt.click(e => {
            e.preventDefault();
            newElt.remove();
        });
    }
}