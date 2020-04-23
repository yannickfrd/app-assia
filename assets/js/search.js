import Search from "./utils/search";
import "./utils/maskPhone";
import "select2";

$("select.multi-select.js-referent").select2({
    // theme: "bootstrap4",
    placeholder: "  -- Référent --",
});

$("select.multi-select.js-service").select2({
    // theme: "bootstrap4",
    placeholder: "  -- Service --",
});

$("select.multi-select.js-device").select2({
    // theme: "bootstrap4",
    placeholder: "  -- Dispositif --",
});

$("select.multi-select.js-status").select2({
    placeholder: "  -- Statut --",
});

let search = new Search("form-search");

let headingSearchElt = document.getElementById("headingSearch");
if (headingSearchElt) {
    let spanFaElt = headingSearchElt.querySelector("span.fa");
    headingSearchElt.addEventListener("click", function () {
        if (headingSearchElt.classList.contains("collapsed")) {
            spanFaElt.classList.replace("fa-chevron-down", "fa-chevron-right");
        } else {
            spanFaElt.classList.replace("fa-chevron-right", "fa-chevron-down");
        }
    });
}