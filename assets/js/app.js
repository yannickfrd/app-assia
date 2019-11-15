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

// import MessageFlash from "./utils/messageFlash";
import AjaxRequest from "./utils/ajaxRequest";
import SearchPerson from "./searchPerson";

$(function () {
    $("[data-toggle='tooltip']").tooltip()
});

$(function () {
    $("[data-toggle='popover']").popover()
});

// Requête Ajax
let ajaxRequest = new AjaxRequest();

// Recherche instannée d'une personne via Ajax
let searchPerson = new SearchPerson(ajaxRequest, 3, 500); // lengthSearch, time

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
// console.log("Hello Webpack Encore!");