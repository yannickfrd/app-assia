import ValidationSupport from "./validationSupport";
import Evaluation from "./evaluation";
import RemoveSupportPers from "./removeSupportPers";

import "select2";
import "../utils/maskDeptCode";
import "../utils/maskPhone";

let validationSupport = new ValidationSupport();

if (document.getElementById("support_grp_endDate")) {
    let evaluation = new Evaluation();
}

let removeSupportPers = new RemoveSupportPers();

$("select.multi-select").select2({
    // theme: "bootstrap4",
    placeholder: "-- Services --",
});