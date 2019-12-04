import ValidationSupport from "./validationSupport";
import Evaluation from "./evaluation";
import RemoveSupportPerson from "./removeSupportPerson";

import "select2";
import "../utils/maskDeptCode";
import "../utils/maskPhone";

let validationSupport = new ValidationSupport();

if (document.getElementById("support_group_endDate")) {
    let evaluation = new Evaluation();
}

let removeSupportPerson = new RemoveSupportPerson();

$("select.multi-select").select2({
    // theme: "bootstrap4",
    placeholder: "-- Services --",
});