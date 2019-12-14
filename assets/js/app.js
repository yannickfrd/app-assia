/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)

require("../css/app.scss");
require("../css/table.css");
import "select2/dist/css/select2.min.css";
import "select2-bootstrap4-theme/dist/select2-bootstrap4.min.css";

const $ = require("jquery");

require("bootstrap");
require("bootstrap-datepicker");


// import MessageFlash from "./utils/messageFlash";
import AjaxRequest from "./utils/ajaxRequest";
import SearchPerson from "./searchPerson";

$(function () {
    $("[data-toggle='tooltip']").tooltip()
});

$(function () {
    $("[data-toggle='popover']").popover()
});

// Smooth Scroll animation
$(document).ready(function ($) {
    $("a[href^='#']").on("click", function (e) {
        e.preventDefault();
        let target = $(this).attr("href");
        $("html, body").stop().animate({
            scrollTop: $(target).height()
        }, 1000);
    });
});

// Requête Ajax
let ajaxRequest = new AjaxRequest();

// Recherche instannée d'une personne via Ajax
let searchPerson = new SearchPerson(ajaxRequest, 3, 500); // lengthSearch, time

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