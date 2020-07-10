import Search from "./utils/search";
import "./utils/maskPhone";
import "select2";

// import MaskInput from "./utils/maskInput";
// new MaskInput(".js-phone");

let select2Array = {
    "typology": "Typologie familiale",
    "status": "Statut",
    "referent": "Référent",
    "service": "Service",
    "device": "Dispositif",
    "contribution-type": "Type",
}

for (let i in select2Array) {
    $("select.multi-select.js-" + i).select2({
        // theme: 'bootstrap4',
        placeholder: "  -- " + select2Array[i] + " --",
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