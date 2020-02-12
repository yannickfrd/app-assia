$(document).ready(function () {
    $('.add-another-collection-widget').click(function (e) {
        e.preventDefault();
        var list = $(jQuery(this).attr('data-list-selector'));
        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter') || list.children().length;
        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);
        // create a new list element and add it to the list
        var newElem = $(list.attr('data-widget-tags')).html(newWidget);

        addDeleteLink(newElem);

        newElem.appendTo(list);
    });
});

// La fonction qui ajoute un lien de suppression d'une catégorie
function addDeleteLink($prototype) {
    // Création du lien
    var $deleteLink = $('<div class="form-group col-sm-1 my-2"><button class="btn btn-danger"><span class="fas fa-trash-alt"></class></button>');
    // Ajout du lien
    $prototype.append($deleteLink);
    // Ajout du listener sur le clic du lien pour effectivement supprimer la catégorie
    $deleteLink.click(function (e) {
        $prototype.remove();
        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
        return false;
    });
}