import ValidationSupport from "./validationSupport";
import SitSocial from "./sitSocial";
import SitFamily from "./sitFamily";
import SitProf from "./sitProf";

import "select2";
import "../utils/maskZipCode";
import "../utils/maskPhone";

let validationSupport = new ValidationSupport();

let sitSocial = new SitSocial();
let sitFam = new SitFamily();
let sitProf = new SitProf();

$("select.multi-select").select2({
    // theme: "bootstrap4",
    placeholder: "-- Services --",
});