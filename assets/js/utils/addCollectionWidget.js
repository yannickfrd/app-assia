// Classe d'ajout d'éléments dans une collection
export default class AddCollectionWidget {

    constructor() {
        this.btnElt = document.querySelector(".add-another-collection-widget");
        this.list = document.querySelector(this.btnElt.getAttribute("data-list-selector"));
        this.counter = this.list.getAttribute("data-widget-counter") || this.list.children().length; // Try to find the counter of the list or use the length of the list
        this.init();
    }

    init() {
        this.btnElt.addEventListener("click", e => {
            e.preventDefault();
            this.addElt();
        });
    }

    // Ajoute un élément prototypé dans la liste
    addElt() {
        // grab the prototype template
        let newWidget = this.list.getAttribute("data-prototype");
        // replace the "__name__" used in the id and name of the prototype with a number that's unique to your emails
        newWidget = newWidget.replace(/__name__/g, this.counter);
        // Increase the counter
        this.counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        this.list.setAttribute("data-widget-counter", this.counter);
        // create a new list element and add it to the list
        let newElt = $(this.list.getAttribute("data-widget-tags")).html(newWidget);
        // Add the delete link
        this.addDeleteLink(newElt);
        // Add the element
        newElt.appendTo(this.list);
    }

    // Ajoute un lien de suppression d'une catégorie
    addDeleteLink(newElt) {
        // Création du lien
        let deleteLink = $('<div class="form-group col-sm-1 my-2"><button class="btn btn-danger"><span class="fas fa-trash-alt"></span></button>');
        // Ajout du lien
        newElt.append(deleteLink);
        // Ajout du listener sur le clic du lien pour effectivement supprimer la catégorie
        deleteLink.click(e => {
            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            newElt.remove();
            return false;
        });
    }
}