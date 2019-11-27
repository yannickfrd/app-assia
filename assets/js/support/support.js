import ValidationSupport from "./validationSupport";
import Evaluation from "./evaluation";

import "select2";
import "../utils/maskDeptCode";
import "../utils/maskPhone";

let validationSupport = new ValidationSupport();
let evaluation = new Evaluation();

$("select.multi-select").select2({
    // theme: "bootstrap4",
    placeholder: "-- Services --",
});