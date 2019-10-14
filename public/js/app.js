// Requête Ajax
let ajaxRequest = new AjaxRequest();

// Recherche instannée d'une personne via Ajax
let searchPerson = new SearchPerson(3, 500); // lengthSearch, time

// Masque le loader lorsque le DOM est chargé
// window.onload = function () {
//     let loaderElt = document.getElementById("loader");
//     loaderElt.style.display = "none";
// };

// Active Toolips Bootstrap
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

// Active Popover Bootstrap
$(function () {
    $('[data-toggle="popover"]').popover()
})