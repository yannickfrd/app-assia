import ValidationSupport from "./validationSupport";
import ValidationAvdl from "./validationAvdl";
import SelectRadioJS from "../utils/selectRadio";
import RemoveSupportPerson from "./removeSupportPerson";
import SearchLocation from "../utils/searchLocation";
import CheckChange from "../utils/checkChange";

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("support")) {
        new SelectRadioJS("table-support-people");
        new RemoveSupportPerson();
    }
    if (document.getElementById("avdl_support")) {
        new ValidationAvdl();
    } else {
        new ValidationSupport();
    }
    new SearchLocation("support_location");
    new CheckChange("support"); // form name

});