require("../css/app.scss");
require("../css/table.scss");
require("../css/calendar.scss");
import "select2/dist/css/select2.min.css";
import "select2-bootstrap4-theme/dist/select2-bootstrap4.min.css";

const $ = require("jquery");

require("bootstrap");
require("bootstrap-datepicker");

// import MessageFlash from "./utils/messageFlash";
import AjaxRequest from "./utils/ajaxRequest";
import SearchPerson from "./searchPerson";

// Masque le loader lorsque le DOM est chargé
window.onload = function () {
    // Stop spinner loader 
    document.getElementById("loader").classList.add("d-none");
    // Smooth Scroll animation
    document.getElementById("scroll-top").addEventListener("click", function (e) {
        e.preventDefault();
        let target = this.getAttribute("href");
        $("html, body").stop().animate({
            scrollTop: $(target).height()
        }, 1000);
    });
};

$(function () {
    $("[data-toggle='tooltip']").tooltip();
});

$(function () {
    $("[data-toggle='popover']").popover();
});

// Requête Ajax
let ajaxRequest = new AjaxRequest();

// Recherche instannée d'une personne via Ajax
new SearchPerson(ajaxRequest, 3, 500); // lengthSearch, time

// ! function (a) {
//     a.fn.datepicker.dates.fr = {
//         days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
//         daysShort: ["Dim.", "Lun.", "Mar.", "Mer.", "Jeu.", "Ven.", "Sam."],
//         daysMin: ["Dm", "Lu", "Ma", "Me", "Ju", "Ve", "Sa"],
//         months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
//         monthsShort: ["Janv.", "Févr.", "Mars", "Avril", "Mai", "Juin", "Juil.", "Août", "Sept.", "Oct.", "Nov.", "Déc."],
//         today: "Aujourd'hui",
//         monthsTitle: "Mois",
//         clear: "Effacer",
//     }
// }(jQuery);

// $(document).ready(function () {
//     $(".datepicker").datepicker({
//         format: "dd/mm/yyyy",
//         weekStart: 1,
//         language: "fr",
//         todayHighlight: true,
//         autoclose: true,
//         assumeNearbyYear: true,
//     });
// });