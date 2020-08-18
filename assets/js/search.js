import Search from "./utils/search";
import "./utils/maskPhone";
import "select2";

// import MaskInput from "./utils/maskInput";
// new MaskInput(".js-phone");

let select2Array = {
    "typology": "Typologie familiale",
    "status": "Statut",
    "referents": "Référents",
    "services": "Services",
    "devices": "Dispositifs",
    "contribution-type": "Type",
    "support-type": "Type d'acc.",
}

for (let i in select2Array) {
    $(`select[data-select2-id="${i}"]`).select2({
        placeholder: "  -- " + select2Array[i] + " --",
        // theme: 'bootstrap4',
    });
}

new Search("form-search");

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