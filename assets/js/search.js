import Search from "./utils/search";
import "./utils/maskPhone";
import "select2";

$("select.multi-select.js-service").select2({
    // theme: "bootstrap4",
    placeholder: "  -- Services --",
});

$("select.multi-select.js-status").select2({
    placeholder: "  -- Statut --",
});

let search = new Search("form-search");

let headingSearchElt = document.getElementById("headingSearch");
let spanFaElt = headingSearchElt.querySelector("span.fa");
headingSearchElt.addEventListener("click", function () {
    if (headingSearchElt.classList.contains("collapsed")) {
        spanFaElt.classList.replace("fa-chevron-right", "fa-chevron-down");
    } else {
        spanFaElt.classList.replace("fa-chevron-down", "fa-chevron-right");
    }
});