/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');
// import '../css/app.scss';
// require("select2-bootstrap4.css");

const $ = require('jquery');

require("select2");

$("select.multi-select").select2({
    theme: "bootstrap4",
    placeholder: "-- Services --",
});

console.log("test");

$(function () {
    $("[data-toggle='tooltip']").tooltip()
});

$(function () {
    $("[data-toggle='popover']").popover()
});


// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
console.log('Hello Webpack Encore! Edit me in assets/js/app.js');