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