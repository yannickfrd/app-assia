class NameClass {
    constructor() {

        this.init();
    }
    // Initialise 
    init() {

    };

    Method() {

    };
};

let nameClass = new NameClass();

// Masque le loader lorsque le DOM est chargé
window.onload = function () {
    let loaderElt = document.getElementById("loader");
    loaderElt.style.display = "none";
};

// Active Toolips Bootstrap
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

// Active Popover Bootstrap
$(function () {
    $('[data-toggle="popover"]').popover()
})