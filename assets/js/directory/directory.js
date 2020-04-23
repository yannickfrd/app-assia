import "select2";
import "../utils/maskZipcode";
import "../utils/maskPhone";

$("select.multi-select.js-service").select2({
    // theme: "bootstrap4",
    placeholder: "  -- Service --",
});

$("select.multi-select.js-device").select2({
    // theme: "bootstrap4",
    placeholder: "  -- Dispositif --",
});