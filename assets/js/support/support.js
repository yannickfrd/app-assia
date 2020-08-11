import ValidationSupport from "./validationSupport";
import SelectRadioJS from "../utils/selectRadio";
import RemoveSupportPerson from "./removeSupportPerson";
import SearchLocation from "../utils/searchLocation";
import CheckChange from "../utils/checkChange";

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("support")) {
        new SelectRadioJS("table-support-people");
        new RemoveSupportPerson();
    }
    new SearchLocation("support_location");
    new ValidationSupport();
    new CheckChange("support"); // form name

});